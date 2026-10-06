## ADDED Requirements

### Requirement: SEO Slug 自動生成
系統 SHALL 在產品或分類建立/更新時自動生成 SEO 友好的 slug。Slug SHALL 使用小寫英文字母、數字和連字號組成。若名稱為中文，系統 SHALL 優先嘗試 AI 翻譯為英文後生成 slug；若 AI 不可用，SHALL 使用 `Str::slug()` 作為降級方案。

#### Scenario: 建立產品時自動生成 slug
- **WHEN** 建立新產品且未提供 slug 時
- **THEN** 系統 SHALL 根據產品名稱（優先使用 `name.zh`，次選 `name.en`）自動生成 slug

#### Scenario: 建立分類時自動生成 slug
- **WHEN** 建立新分類且未提供 slug 時
- **THEN** 系統 SHALL 根據分類名稱自動生成 slug

#### Scenario: 使用者提供 slug
- **WHEN** 使用者在建立或更新時提供 slug 時
- **THEN** 系統 SHALL 使用該 slug（經過 `Str::slug()` 標準化處理）

#### Scenario: AI 翻譯生成 slug
- **WHEN** 產品/分類名稱為中文且 AI 服務可用時
- **THEN** 系統 SHALL 呼叫 AI 將中文名稱翻譯為英文後生成 slug

#### Scenario: AI 服務不可用降級處理
- **WHEN** AI 服務不可用（預算超限、API 錯誤等）時
- **THEN** 系統 SHALL 使用 `Str::slug()` 直接處理名稱，不阻擋建立流程

### Requirement: Slug 唯一性保證
系統 SHALL 確保同一專案內的 slug 唯一。若產生重複 slug，系統 SHALL 自動附加數字後綴。

#### Scenario: Slug 衝突自動解決
- **WHEN** 生成的 slug 與同專案內現有記錄重複時
- **THEN** 系統 SHALL 附加數字後綴（如 `product-name-1`, `product-name-2`）直到唯一

#### Scenario: 跨專案允許相同 slug
- **WHEN** 不同專案建立相同名稱的產品/分類時
- **THEN** 系統 SHALL 允許使用相同的 slug（各專案獨立命名空間）

### Requirement: Route Model Binding 支援
系統 SHALL 支援使用 slug 進行路由綁定，提供 SEO 友好的 URL。

#### Scenario: 透過 slug 取得產品
- **WHEN** 請求 `/products/{product:slug}` 時
- **THEN** 系統 SHALL 根據 slug 查詢並返回對應產品

#### Scenario: 透過 slug 取得分類
- **WHEN** 請求 `/category/{category:slug}` 時
- **THEN** 系統 SHALL 根據 slug 查詢並返回對應分類
