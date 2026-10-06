## 1. Config 與 Migration

- [x] 1.1 建立 `config/ai_pricing.php` 定價設定檔（vision_parse: 10, vision_parse_multi: 15, remove_bg: 5, scene_gen: 8, translation: 3, chat: 3）
- [x] 1.2 建立 migration：`projects` 表移除 `monthly_budget_usd` / `monthly_usage_usd`，新增 `yearly_budget_twd`（預設 1000）/ `yearly_usage_twd`（預設 0）
- [x] 1.3 建立 migration：`ai_usage_logs` 表新增 `display_cost_twd` 欄位（decimal 10,2，nullable）

## 2. Model 層

- [x] 2.1 更新 `Project` model：$fillable、$casts 改為 yearly_budget_twd / yearly_usage_twd，更新 getRemainingBudgetAttribute、getUsagePercentageAttribute、isBudgetExceeded
- [x] 2.2 更新 `AiUsageLog` model：$fillable 加入 display_cost_twd

## 3. Service 層

- [x] 3.1 `AiCostControlService`：新增 `getFeaturePrice(string $feature): float` 方法，從 config('ai_pricing') 讀取固定單價
- [x] 3.2 `AiCostControlService`：修改 `checkBudget()` 改為比較 yearly_usage_twd vs yearly_budget_twd
- [x] 3.3 `AiCostControlService`：修改 `incrementUsage()` 改為 increment yearly_usage_twd，參數改為 display_cost_twd
- [x] 3.4 `AiCostControlService`：修改 `recordUsage()` 支援寫入 display_cost_twd
- [x] 3.5 更新所有 AI 呼叫端（`AiImageService`、`AiTextController` 等）：呼叫 getFeaturePrice 取得固定單價，傳入 recordUsage 與 incrementUsage

## 4. API 層

- [x] 4.1 `UsageController::current()`：回傳 yearly_budget_twd / yearly_usage_twd / remaining（TWD）
- [x] 4.2 `UsageController::statistics()`：total_usage 改為 yearly_usage_twd
- [x] 4.3 `UsageController::logs()`：回傳 display_cost_twd，cost_usd 僅 super-admin 可見
- [x] 4.4 `ProjectController`：settings 儲存/讀取改為 yearly_budget_twd

## 5. Console Command 與排程

- [x] 5.1 `ResetMonthlyUsage` 改名為 `ResetYearlyUsage`，signature 改為 `usage:reset-yearly`，reset 欄位改為 yearly_usage_twd
- [x] 5.2 `routes/console.php` 註冊排程：`Schedule::command('usage:reset-yearly')->yearlyOn(1, 1, '0:00')`

## 6. 前端：用量監控頁

- [x] 6.1 `usage/index.vue`：金額顯示從 `$USD` 改為 `NT$`，資料欄位對應 yearly_budget_twd / yearly_usage_twd
- [x] 6.2 `usage/index.vue`：tokens 欄加上 `is('super-admin')` 權限控制
- [x] 6.3 `usage/index.vue`：cost_usd 欄加上 `is('super-admin')` 權限控制，新增 display_cost_twd 欄（所有管理員可見）
- [x] 6.4 `usage/index.vue`：statistics 表格的 total_cost 改為顯示 NT$

## 7. 前端：Dashboard 與 Settings

- [x] 7.1 `dashboard/index.vue`：AI 用量卡片從 `$USD` 改為 `NT$`，資料欄位對應 yearly
- [x] 7.2 `settings/index.vue`：預算輸入欄位改為 yearly_budget_twd，標籤與提示文字更新

## 8. i18n 翻譯

- [x] 8.1 `lang/zh-TW/admin.php`：更新 usage / settings / dashboard 相關翻譯（月→年、USD→TWD）
- [x] 8.2 `lang/en/admin.php`：同步更新英文翻譯
