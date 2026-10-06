## 上下文 (Context)

目前的 AI 工具是獨立頁面，與產品管理流程斷裂。此外，缺乏全域開關來控制 AI 功能，導致無法針對低預算或隱私敏感專案關閉 AI。

## 目標 / 非目標 (Goals / Non-Goals)

**目標:**
- 在專案層級實作 `is_ai_enabled` 開關。
- 整合 AI 翻譯至產品編輯流程，支援語系選擇與防呆機制。
- 實作 DM 解析後的無縫導向至產品建立。

**非目標:**
- 本次不涉及 AI 模型本身的微調 (Fine-tuning)。
- 不改變現有的計費邏輯，僅控制功能存取。

## 技術決策 (Decisions)

### 1. 後端防護機制
- **決策**: 建立 `EnsureAiEnabled` Middleware 並套用至所有 `api/ai/*` 路由。
- **原因**: 集中管理權限檢查，避免在每個 Controller 重複撰寫邏輯，確保安全性。

### 2. 前端狀態管理
- **決策**: 在 `Settings` 頁面更新 `is_ai_enabled` 後，即時更新 Pinia Store 或重整頁面以觸發全域 UI 狀態變更（隱藏/顯示選單）。
- **原因**: 提供即時回饋，提升使用者體驗。

### 3. 解析導向邏輯
- **決策**: 使用 `vue-router` 的 `query` 參數傳遞解析後的 JSON 資料（Base64 編碼或使用 Pinia 暫存）。
- **實作**: 考量 URL 長度限制，將採用 Pinia Store (`useProductStore`) 暫存解析結果，跳轉後從 Store 讀取並填入表單。

## 風險與權衡 (Risks / Trade-offs)

- **[風險] 翻譯覆蓋資料** → **[對策]** UI 必須明確標示哪些欄位已有內容，並預設**不勾選**，需使用者手動確認才覆蓋。
- **[風險] 解析資料過大** → **[對策]** 使用 Pinia Store 傳遞資料而非 URL 參數，避免長度限制問題。

## 遷移計畫 (Migration Plan)

1.  執行 Migration 新增 `is_ai_enabled` 欄位（預設 true）。
2.  部署後端 Middleware 與 Controller 更新。
3.  部署前端 UI 更新。
