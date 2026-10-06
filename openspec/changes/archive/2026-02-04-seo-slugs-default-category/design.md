## Context

目前系統狀態：
- `products` 和 `product_categories` 表都已有 `slug` 欄位（nullable）
- 分類刪除時將產品的 `category_id` 設為 null，造成孤兒資料
- 無自動 slug 生成機制，需手動填寫
- 已有 `AiTextService` 提供翻譯功能（`translate` 方法）

技術環境：Laravel 12 + MySQL 8.4 + Laravel Sail

## Goals / Non-Goals

**Goals:**
- 自動生成 SEO 友好的 slug（支援中英文）
- 確保 slug 唯一性（同表內）
- 建立系統保護的預設分類
- 分類刪除時自動重新指派產品
- AI 翻譯整合（可選，有降級方案）

**Non-Goals:**
- 跨專案（project）的 slug 唯一性（每個專案獨立命名空間）
- 歷史 URL 重定向（slug 變更時的 301 redirect）
- 批量更新現有資料的 slug（僅新建/更新時觸發）

## Decisions

### D1: Slug 生成策略 - 使用 Model Observer

**選擇**: 在 Model 的 `creating` 和 `updating` 事件中透過 Observer 處理

**替代方案**:
- Mutator（`setSlugAttribute`）：無法訪問完整模型狀態
- Controller 內處理：違反 DRY 原則，容易遺漏

**理由**: Observer 可統一處理所有來源的資料變更，包括 Seeder、Tinker、API

### D2: AI 翻譯整合 - 新增輕量級 SlugService

**選擇**: 建立獨立的 `SlugService`，內部呼叫 `AiTextService`

```php
class SlugService
{
    public function generateSlug(string $name, string $table, ?int $projectId = null): string
    {
        $baseSlug = $this->translateToSlug($name);
        return $this->ensureUnique($baseSlug, $table, $projectId);
    }

    protected function translateToSlug(string $name): string
    {
        // 1. 嘗試 AI 翻譯（中文 → 英文）
        // 2. 降級：Str::slug($name)
    }
}
```

**替代方案**:
- 直接在 Observer 內實作：過於複雜，難以測試
- 擴展 AiTextService：違反單一職責

**理由**: 獨立 Service 便於測試、可注入 mock、清晰的責任邊界

### D3: AI 呼叫時機 - 非同步降級（Sync-first with Fallback）

**選擇**: 同步呼叫 AI，失敗時立即降級為 `Str::slug()`

**替代方案**:
- Queue 處理：增加複雜度，slug 可能暫時為空
- 僅用本地處理：放棄 AI 翻譯優勢

**理由**:
- AI 呼叫通常 <500ms，可接受的同步延遲
- 降級確保即使 AI 失敗也能生成有效 slug
- 預算超限時自動降級，不阻擋業務流程

### D4: 預設分類 ID - 硬編碼 ID=1

**選擇**: 預設分類固定 ID=1，`is_system=true`

**替代方案**:
- 使用 UUID 標識：需額外查詢
- Config 配置：增加維護成本

**理由**:
- ID=1 簡單可靠，無需查詢
- Migration 確保該 ID 被保留
- `is_system` 防止誤刪

### D5: Slug 唯一性範圍 - 專案內唯一

**選擇**: 每個 `project_id` 內 slug 唯一

```sql
-- 唯一索引
ALTER TABLE products ADD UNIQUE INDEX idx_slug_project (project_id, slug);
ALTER TABLE product_categories ADD UNIQUE INDEX idx_slug_project (project_id, slug);
```

**理由**: 多租戶架構，各專案獨立，允許不同專案有相同 slug

### D6: 系統分類處理 - 每專案獨立

**選擇**: 每個專案都有自己的「未分類」（透過 Seeder 建立）

**替代方案**:
- 全域單一未分類（project_id=null）：打破多租戶隔離

**理由**: 保持專案完全隔離，簡化權限控制

## Risks / Trade-offs

| Risk | Mitigation |
|------|------------|
| AI 翻譯增加延遲 | 同步呼叫 + 即時降級，最差情況使用 Str::slug() |
| AI 預算超限 | checkBudget() 失敗時自動降級，不阻擋建立 |
| 現有資料 slug 為空 | 不批量更新，僅影響新建/更新的記錄 |
| 刪除分類時大量產品更新 | 單一 UPDATE 語句批量處理，非逐筆更新 |
| 中文 slug 不利於 SEO | AI 翻譯優先，確保英文 slug |

## Migration Plan

**部署順序**:
1. Migration: 新增 `is_system` 欄位
2. Seeder: 建立預設分類（需在 Migration 後執行）
3. Observer: 註冊並啟用
4. Controller: 更新刪除邏輯

**Rollback**:
- 若需回滾，先移除 Observer 註冊
- `is_system` 欄位可保留（向後相容）

## Open Questions

- [已決定] 中文 slug 支援：優先 AI 翻譯為英文，降級時使用 hash-based fallback (`item-{hash}`)
- [已實作] slug 編輯 UI：產品編輯頁面新增選填 slug 欄位，留空自動產生，手動輸入可覆寫
