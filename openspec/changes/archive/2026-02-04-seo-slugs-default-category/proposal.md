## Why

目前產品與分類的 `slug` 欄位雖存在但未被自動生成，且刪除分類時會將產品的 `category_id` 設為 null，導致孤兒資料問題。本變更將實現 SEO 友好的 URL 結構並建立穩健的分類刪除策略，提升搜尋引擎排名與資料完整性。

## What Changes

- 新增分類的 `is_system` 欄位，用於標記系統保護的預設分類
- 建立「未分類」預設分類 (id=1, slug=uncategorized)，作為刪除分類時的產品歸屬目標
- 實作 Model Observer 自動生成 SEO-friendly slugs：
  - 優先使用 AI 翻譯中文名稱為英文 slug
  - 降級方案：使用 `Str::slug()` 處理
  - 自動處理重複 slug (加入數字後綴)
- **BREAKING**: 刪除分類時，產品將被重新指派至「未分類」而非設為 null
- 禁止刪除系統分類 (is_system=true)

## Capabilities

### New Capabilities
- `seo-slugs`: 產品與分類的 SEO 友好 URL slug 自動生成機制，包含 AI 翻譯與唯一性保證
- `default-category`: 預設分類保護機制，確保刪除分類時產品不會成為孤兒資料

### Modified Capabilities
- `product-categories`: 分類刪除邏輯變更 - 從設定 null 改為重新指派至預設分類；新增 is_system 欄位保護

## Impact

**資料庫**:
- `product_categories` 表新增 `is_system` 欄位
- 新增 `DefaultCategorySeeder` 確保預設分類存在

**後端**:
- 新增 `ProductObserver` 與 `ProductCategoryObserver` 處理 slug 生成
- 修改 `ProductCategoryController@destroy` 實作重新指派邏輯
- 可能需要擴展 `AiTextService` 加入 slug 翻譯方法

**路由**:
- 支援 Route Model Binding 使用 slug: `/products/{product:slug}`, `/category/{category:slug}`

**API**:
- 分類刪除 API 行為變更（BREAKING）
- 分類 API 回應新增 `is_system` 欄位
- 產品 API store/update 新增 `slug` 參數（選填）

**前端**:
- `ProductManager.vue` 新增 slug 編輯欄位（選填，留空自動產生）
- 產品列表顯示 slug 欄位
