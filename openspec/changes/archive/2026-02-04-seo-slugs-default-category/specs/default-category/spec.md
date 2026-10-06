## ADDED Requirements

### Requirement: 系統預設分類
每個專案 SHALL 擁有一個系統保護的「未分類」預設分類。該分類 SHALL 具有 `id=1`（專案內）、`slug=uncategorized`、`is_system=true`。系統分類 SHALL 禁止刪除。

#### Scenario: 專案初始化時建立預設分類
- **WHEN** 專案資料庫初始化（Seeder 執行）時
- **THEN** 系統 SHALL 建立預設分類，其 `name` 為 `{"zh": "未分類", "en": "Uncategorized"}`，`slug` 為 `uncategorized`，`is_system` 為 `true`

#### Scenario: 禁止刪除系統分類
- **WHEN** 嘗試刪除 `is_system=true` 的分類時
- **THEN** 系統 SHALL 返回 HTTP 403 Forbidden 並拒絕刪除

#### Scenario: 禁止修改系統分類的保護屬性
- **WHEN** 嘗試修改系統分類的 `is_system` 或 `slug` 欄位時
- **THEN** 系統 SHALL 忽略這些欄位的變更（僅允許修改 `name`、`sort_order`）

### Requirement: 分類刪除時產品重新指派
當刪除非系統分類時，系統 SHALL 將該分類下的所有產品重新指派至預設分類，而非設為 null。

#### Scenario: 刪除分類時重新指派產品
- **WHEN** 刪除一個包含產品的非系統分類時
- **THEN** 系統 SHALL 將該分類下的所有產品的 `category_id` 更新為預設分類的 ID

#### Scenario: 刪除空分類
- **WHEN** 刪除一個不包含任何產品的非系統分類時
- **THEN** 系統 SHALL 直接刪除該分類

#### Scenario: API 回應包含重新指派資訊
- **WHEN** 成功刪除分類且有產品被重新指派時
- **THEN** API 回應 SHALL 包含被重新指派的產品數量

### Requirement: 系統分類欄位
`product_categories` 表 SHALL 包含 `is_system` 布林欄位，用於標識系統保護的分類。

#### Scenario: 分類 API 回應包含 is_system
- **WHEN** 取得分類列表或單一分類時
- **THEN** API 回應 SHALL 包含 `is_system` 欄位

#### Scenario: 前端顯示系統分類標識
- **WHEN** 管理員查看分類列表時
- **THEN** 系統分類 SHALL 顯示標識且刪除按鈕 SHALL 被禁用
