## MODIFIED Requirements

### Requirement: 專案 AI 功能開關
系統 SHALL 允許管理者在專案層級啟用或停用 AI 功能 (`is_ai_enabled`)。此設定必須在所有 API 請求中強制執行。預算檢查 SHALL 比較 `yearly_usage_twd` 與 `yearly_budget_twd`。

#### Scenario: 停用 AI 功能
- **WHEN** 管理者在設定頁面關閉 AI 功能
- **THEN** 所有 AI 相關 API (`/api/ai/*`) 請求 SHALL 被拒絕，並回傳 403 Forbidden 錯誤，前端 AI 選單與按鈕 SHALL 自動隱藏

#### Scenario: 啟用 AI 功能
- **WHEN** 管理者開啟 AI 功能
- **THEN** 系統恢復 AI API 存取權限，前端顯示 AI 工具選單與相關操作按鈕

#### Scenario: 年度預算不足
- **WHEN** `yearly_usage_twd + 本次固定單價 > yearly_budget_twd`
- **THEN** 系統 SHALL 記錄警告 log（目前策略為允許繼續，可改為阻擋）
