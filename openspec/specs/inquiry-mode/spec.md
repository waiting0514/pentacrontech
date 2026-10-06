## ADDED Requirements

### Requirement: 設定僅供詢價模式
系統 SHALL 允許設定產品是否為「僅供詢價」。

#### Scenario: 產品為僅供詢價
- **WHEN** 產品的 `is_inquiry_only` 設定為 true
- **THEN** 產品價格 SHALL NOT 顯示在 API 回應或介面中
- **THEN** 「加入購物車」按鈕 SHALL 被詢價按鈕 (CTA) 取代

### Requirement: 預設詢價模式
系統 SHALL 允許設定專案層級的預設詢價模式。

#### Scenario: 建立新產品
- **WHEN** 在介面中初始化新產品時
- **THEN** `is_inquiry_only` 開關 SHALL 預設為專案設定中的 `product_default_inquiry_mode` 數值

### Requirement: 批次詢價設定
系統 SHALL 允許批次更新多項產品的詢價狀態。

#### Scenario: 批次設定為僅供詢價
- **WHEN** 發送 `action: set_inquiry_mode` 且 `value: true` 的批次更新請求
- **THEN** 所有指定的產品 SHALL 將 `is_inquiry_only` 設定為 true