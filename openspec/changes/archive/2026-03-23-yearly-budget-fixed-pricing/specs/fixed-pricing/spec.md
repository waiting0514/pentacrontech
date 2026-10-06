## ADDED Requirements

### Requirement: 固定單價定價表
系統 SHALL 在 `config/ai_pricing.php` 定義每個 AI 功能的固定 TWD 單價。

#### Scenario: 讀取功能單價
- **WHEN** 系統執行 AI 功能（如 `vision_parse`）
- **THEN** SHALL 從 `config('ai_pricing.<feature>')` 取得對應的 TWD 單價

#### Scenario: 未定義功能的預設價格
- **WHEN** 功能名稱不在定價表中
- **THEN** SHALL 使用預設值 NT$10

### Requirement: 計費快照寫入
每次 AI 呼叫完成後，系統 SHALL 將固定單價寫入 `ai_usage_logs.display_cost_twd` 作為快照。

#### Scenario: 記錄成功的 AI 呼叫
- **WHEN** AI 呼叫成功
- **THEN** `ai_usage_logs` SHALL 同時記錄 `cost_usd`（原始 API 成本）與 `display_cost_twd`（固定單價快照）

#### Scenario: 記錄失敗的 AI 呼叫
- **WHEN** AI 呼叫失敗
- **THEN** `cost_usd` SHALL 為 0，`display_cost_twd` SHALL 為 0

### Requirement: 預算扣減
系統 SHALL 以 `display_cost_twd` 累加至 `projects.yearly_usage_twd`。

#### Scenario: 呼叫後累計用量
- **WHEN** AI 呼叫成功且記錄完成
- **THEN** `projects.yearly_usage_twd` SHALL 增加該筆 `display_cost_twd` 的金額

### Requirement: 敏感欄位權限控制
用量監控頁的 tokens（input_tokens / output_tokens）與原始成本（cost_usd）欄位 SHALL 僅超級管理員可見。

#### Scenario: 超級管理員檢視用量
- **WHEN** 超級管理員進入用量監控頁
- **THEN** SHALL 顯示 tokens 欄、cost_usd 欄、display_cost_twd 欄

#### Scenario: 一般管理員檢視用量
- **WHEN** 一般管理員進入用量監控頁
- **THEN** SHALL 僅顯示 display_cost_twd 欄，tokens 欄與 cost_usd 欄 SHALL 隱藏
