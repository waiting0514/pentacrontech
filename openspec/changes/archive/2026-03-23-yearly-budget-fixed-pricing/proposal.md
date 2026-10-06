## Why

目前 AI 用量採月計預算（$100 USD/月），成本以原始 USD 顯示，對台灣客戶不直覺。需改為年度預算制（NT$ 計），並以固定單價計費取代按實際成本加成，讓客戶能預測「預算還能用幾次」。

## What Changes

- **BREAKING** `projects` 表：`monthly_budget_usd` / `monthly_usage_usd` 改為 `yearly_budget_twd` / `yearly_usage_twd`
- 新增 `config/ai_pricing.php` 定價設定檔，每個 AI 功能定義固定 TWD 單價
- `ai_usage_logs` 表新增 `display_cost_twd` 欄位（寫入時快照固定單價）
- `AiCostControlService`：預算檢查改為比較 TWD，計費改為查詢固定單價
- `ResetMonthlyUsage` command 改為 `ResetYearlyUsage`，排程改為每年 1/1
- 前端所有金額顯示從 `$USD` 改為 `NT$`
- 用量監控頁 tokens 與原始成本（cost_usd）欄位僅超級管理員可見（統一用 `is('super-admin')` 控制）

## Capabilities

### New Capabilities
- `fixed-pricing`: AI 功能固定單價計費機制（config 定價表、快照寫入、預算扣減）

### Modified Capabilities
- `ai-governance`: 預算檢查從月/USD 改為年/TWD
- `project`: Budget & Usage 欄位從 monthly USD 改為 yearly TWD

## Impact

- DB migration：`projects` rename/新增欄位、`ai_usage_logs` 新增欄位
- Backend：`AiCostControlService`、`UsageController`、`ProjectController`、`ResetMonthlyUsage` command、`routes/console.php`
- Frontend：`usage/index.vue`、`dashboard/index.vue`、`settings/index.vue`
- i18n：`lang/zh-TW/admin.php`、`lang/en/admin.php`（月→年、USD→TWD 相關翻譯）
- Config：新增 `config/ai_pricing.php`
