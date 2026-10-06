## MODIFIED Requirements

### Requirement: Budget & Usage 欄位
`Project` model 的預算與用量欄位 SHALL 使用年度 TWD 計算。

- `yearly_budget_twd`: Decimal (10, 2)。年度 AI 預算上限，單位 TWD，預設 1000。
- `yearly_usage_twd`: Decimal (10, 2)。當年度已使用金額（累計固定單價），單位 TWD，預設 0。

#### Scenario: 查詢剩餘預算
- **WHEN** 系統或前端查詢剩餘預算
- **THEN** SHALL 計算 `yearly_budget_twd - yearly_usage_twd`，單位為 NT$

#### Scenario: 查詢使用率
- **WHEN** 系統計算使用百分比
- **THEN** SHALL 計算 `(yearly_usage_twd / yearly_budget_twd) × 100`

#### Scenario: 年度歸零
- **WHEN** 排程 `usage:reset-yearly` 於每年 1/1 執行
- **THEN** SHALL 將所有 projects 的 `yearly_usage_twd` 歸零為 0
