## MODIFIED Requirements

### Requirement: 產品分類 CRUD 管理
系統 SHALL 提供 API 接口以建立、讀取、更新和刪除產品分類。每個分類 SHALL 具有多語系名稱（包含 zh/en 鍵值的 JSON）、代稱 (slug)、排序 (sort_order)、啟用狀態 (is_active) 和系統保護標記 (is_system) 欄位。分類 SHALL 透過 `project_id` 歸屬於特定專案。

#### Scenario: 列出所有產品分類
- **WHEN** 向 `/api/product-categories` 發送 GET 請求時
- **THEN** 系統回傳所有依據 `sort_order` 及 `id` 排序的產品分類，每筆記錄 SHALL 包含 `is_system` 欄位

#### Scenario: 建立產品分類
- **WHEN** 向 `/api/product-categories` 發送帶有有效名稱的 POST 請求時
- **THEN** 系統建立該分類（若未提供 slug 則自動生成）並回傳 HTTP 201 狀態碼

#### Scenario: 更新產品分類
- **WHEN** 向 `/api/product-categories/{id}` 發送帶有更新欄位的 PUT 請求時
- **THEN** 系統更新該分類（若更新名稱且未提供 slug 則重新生成）並回傳更新後的紀錄

#### Scenario: 刪除產品分類
- **WHEN** 向 `/api/product-categories/{id}` 發送 DELETE 請求時
- **THEN** 若為系統分類（`is_system=true`）則回傳 HTTP 403；否則系統 SHALL 將該分類下的產品重新指派至預設分類後刪除該分類
