# AI-Powered Agency Backend Proposal

## 概述

建立一個基於 Laravel 11 + Vue 3 的 AI 驅動內容管理系統,採用 **Agency Maintenance Model (B2B)** 商業模式。系統整合多個 AI 服務(OpenAI GPT-4o-mini、Photoroom、Clipdrop)用於內容生成、圖片處理和翻譯,並實作嚴格的「月度預算」成本控制機制。

## 目標

1. **成本控制優先**: 實作「隱形圍欄」(Invisible Fence)邏輯,在 Agency 層級控制 AI 使用成本
2. **多語言支援**: 使用 JSON 欄位策略,避免傳統翻譯表的複雜性
3. **AI 助手整合**: 提供情境感知聊天、圖片解析、背景移除、場景生成等功能
4. **完整稽核**: 記錄所有 AI API 呼叫,用於成本計算和問責

## 核心功能

### 1. 資料庫架構

#### Products Table (產品表)
- 使用 JSON 欄位儲存多語言內容 (`name`, `description`)
- 整合 Laravel Scout 搜尋功能
- 欄位: id, name (JSON), description (JSON), specs, price, image_url, timestamps

#### Users/Teams Table (使用者/團隊表)
- 實作成本控制欄位
- `monthly_usage_usd`: 當月 AI 使用成本
- `monthly_budget_usd`: 月度預算上限(預設 $30)
- `role`: 角色權限 (admin/client)

#### AI Usage Logs Table (AI 使用記錄表)
- 記錄每次 API 呼叫的詳細資訊
- 欄位: team_id, user_id, provider, feature, input_tokens, output_tokens, cost_usd, status

#### Activity Logs (活動記錄)
- 使用 `spatie/laravel-activitylog` 套件
- 追蹤所有 Product 的 CRUD 操作

### 2. 後端服務層

#### AiCostControlService (成本控管服務)
**職責**: 作為所有 AI 呼叫的閘道
- **前置檢查**: 驗證 `monthly_usage_usd` 是否超過 `monthly_budget_usd`
- **超額處理**: 記錄警告,可選擇性阻擋請求
- **後置更新**: 根據實際 API 成本增加使用量

#### AiTextService (文字 AI 服務)
**使用模型**: OpenAI GPT-4o-mini

功能:
1. **情境感知聊天** (`chat(context, userMessage)`)
   - `context='product'` → 系統提示: "You are a Product Manager..."
   - `context='news'` → 系統提示: "You are a Social Media Editor..."

2. **RAG (簡化版)** 
   - 使用 Laravel Scout (Database Driver) 搜尋產品
   - 將搜尋結果注入系統提示

3. **一鍵翻譯** (`translate(data, targetLangs)`)
   - 輸入: 產品名稱/描述
   - 輸出: JSON 格式的多語言內容
   - 強制使用 JSON mode

#### AiImageService (圖片 AI 服務)
**多模態功能**:

1. **Vision Parse** (`parseDmImage(image)`)
   - 使用 GPT-4o Vision
   - 輸入: DM/包裝照片
   - 輸出: JSON (name, specs, meta_title, meta_keywords, description)

2. **圖片編輯**
   - `removeBackground(image)`: 呼叫 Clipdrop/Remove.bg API
   - `generateScene(image, prompt)`: 呼叫 Photoroom API

### 3. 前端元件 (Vue 3)

#### AiAssistant.vue (AI 助手)
- 浮動或嵌入式聊天視窗
- Props: `context` (String), `initialData` (Object - RAG 情境)
- 功能: 串流文字回應,允許插入 AI 回應到表單欄位

#### CameraUploader.vue (相機上傳)
- HTML5 `capture="environment"` 支援行動裝置相機
- "掃描" 按鈕觸發 `AiImageService.parseDmImage`
- 顯示解析後的表單供手動編輯

#### ImageEditor.vue (圖片編輯器)
- 顯示產品圖片
- 按鈕: "移除背景"、"生成場景"(需輸入提示詞)
- 清楚顯示載入狀態

## 技術架構

### 後端
- **框架**: Laravel 11 (PHP 8.2+)
- **資料庫**: MySQL 8.0 (Docker)
- **搜尋**: Laravel Scout (Database Driver)
- **活動記錄**: spatie/laravel-activitylog

### 前端
- **框架**: Vue 3 (Composition API)
- **樣式**: Tailwind CSS

### 基礎設施
- **容器化**: Docker Compose (Shared Infrastructure Strategy)
- **環境**: Laravel Sail
- **排程器**: Laravel Scheduler (每月 1 日重置使用量)

## 安全性考量

1. **API 金鑰管理**: 所有 Master API Keys 儲存在 `.env`,不暴露給前端
2. **成本控制**: 強制所有 AI 呼叫通過 `AiCostControlService`
3. **稽核追蹤**: 完整記錄所有 AI 使用,包含成本和狀態

## 實作順序

1. **資料庫層**: Migrations → Models
2. **服務層**: AiCostControlService → AiTextService → AiImageService
3. **API 層**: Controllers
4. **前端層**: Vue Components
5. **基礎設施**: Docker、Scheduler、環境配置

## 非目標 (Non-Goals)

- ❌ 不實作使用者點數系統 (改用月度預算)
- ❌ 不建立獨立的翻譯表 (使用 JSON 欄位)
- ❌ 不支援即時 AI 串流到前端 (初版使用簡單的請求/回應)

## 風險與挑戰

1. **API 成本估算**: 需要準確計算 token 使用量
2. **第三方 API 穩定性**: Photoroom/Clipdrop 可能有延遲或失敗
3. **JSON 欄位查詢**: MySQL JSON 查詢效能需要監控
4. **圖片上傳大小**: 需確保 Nginx 設定 `client_max_body_size` ≥ 20MB

## 成功指標

- ✅ 成本控制機制正確運作(超額時阻擋/警告)
- ✅ AI 翻譯準確率 > 90%
- ✅ 圖片解析成功率 > 85%
- ✅ 所有 AI 呼叫都有完整的成本記錄
