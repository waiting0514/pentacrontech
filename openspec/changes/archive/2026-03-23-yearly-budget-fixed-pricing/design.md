## Context

現行系統以月為單位、USD 為幣別管理 AI 預算。`projects.monthly_budget_usd` (預設 100) 與 `projects.monthly_usage_usd` 控制預算，`AiCostControlService` 在每次 AI 呼叫後以原始 API 成本累加。前端以 USD 顯示所有金額。有一個 `usage:reset-monthly` command 但尚未排程。

## Goals / Non-Goals

**Goals:**
- 預算週期從月改為年，幣別從 USD 改為 TWD
- AI 功能以固定 TWD 單價計費（config 定義），取代原始成本加成
- 每筆 log 快照 `display_cost_twd`，不受未來調價影響
- tokens 與原始成本欄位僅超級管理員可見
- 歸零排程改為每年 1/1

**Non-Goals:**
- 不做即時匯率 API 串接
- 不做 DB 定價表與後台定價 UI（目前用 config 檔）
- 不處理既有資料遷移（開發階段）

## Decisions

### 1. 定價來源：config 檔
用 `config/ai_pricing.php` 定義每個 feature 的固定 TWD 單價。

```php
return [
    'vision_parse'       => 10,
    'vision_parse_multi' => 15,
    'remove_bg'          => 5,
    'scene_gen'          => 8,
    'translation'        => 3,
    'chat'               => 3,
];
```

**Why**: 開發階段最簡單，改價只需改檔案＋部署。日後可升級為 DB 表。

### 2. 預算與累計皆存 TWD
`projects` 表改為 `yearly_budget_twd`（預設 1000）和 `yearly_usage_twd`（累計固定單價）。預算檢查直接比較兩個 TWD 值，不需換算。

**Why**: 同幣別比較最直覺，前端直接顯示不需再計算。

### 3. ai_usage_logs 雙欄位
保留 `cost_usd`（原始 API 成本），新增 `display_cost_twd`（固定單價快照）。

**Why**: `cost_usd` 供超級管理員分析實際成本與利潤；`display_cost_twd` 是計費依據，寫入即定，不受後續調價影響。

### 4. 權限控制：tokens 與原始成本
`usage/index.vue` 中 tokens 欄與 cost_usd 欄用 `is('super-admin')` 判斷顯示。一般 admin 只看到 `display_cost_twd`。

**Why**: 原始成本是敏感的商業資訊，不應讓一般管理員看到。

### 5. AiCostControlService 流程

```
AI 呼叫完成
    │
    ├─ 1. calculateOpenAICost() → cost_usd（不變）
    │
    ├─ 2. getFeaturePrice(feature) → display_cost_twd（從 config 讀取）
    │
    ├─ 3. recordUsage() → 寫入 cost_usd + display_cost_twd
    │
    ├─ 4. incrementUsage() → project.increment('yearly_usage_twd', display_cost_twd)
    │
    └─ 5. checkBudget() → yearly_usage_twd vs yearly_budget_twd
```

## Risks / Trade-offs

- **Config 調價需部署** → 可接受，開發階段由開發者管理。日後可升級 DB。
- **固定單價可能虧損** → 超級管理員可透過 cost_usd 欄位監控實際成本，適時調整定價。
- **歸零排程依賴 cron** → 上線時需確認 `schedule:work` 或 cron 已設定。
