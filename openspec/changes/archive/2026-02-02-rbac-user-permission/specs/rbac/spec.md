## ADDED Requirements

### Requirement: Permission 定義
系統 SHALL 定義以下細粒度權限：`product.view`, `product.create`, `product.update`, `product.delete`, `news.view`, `news.create`, `news.update`, `news.delete`, `banner.manage`, `ai.use`, `user.manage`, `report.view_cost`, `system.manage`。

#### Scenario: 權限建立
- **WHEN** 執行 RolesAndPermissionsSeeder
- **THEN** 系統 SHALL 建立所有上述權限記錄

### Requirement: 角色定義與權限指派
系統 SHALL 定義三種角色並指派對應權限。

#### Scenario: super-admin 角色
- **WHEN** 使用者擁有 super-admin 角色
- **THEN** Gate::before SHALL 回傳 true，繞過所有權限檢查

#### Scenario: manager 角色
- **WHEN** 執行 Seeder 建立 manager 角色
- **THEN** 該角色 SHALL 擁有 `product.*`, `news.*`, `banner.manage`, `ai.use`, `user.manage`, `report.view_cost`

#### Scenario: editor 角色
- **WHEN** 執行 Seeder 建立 editor 角色
- **THEN** 該角色 SHALL 擁有 `product.view`, `product.create`, `product.update`, `news.*`, `banner.manage`, `ai.use`
- **THEN** 該角色 SHALL NOT 擁有 `product.delete`, `user.manage`, `report.view_cost`, `system.manage`

### Requirement: Controller 權限檢查
所有 Controller 的操作 SHALL 使用 `$this->authorize('permission.name')` 檢查權限，不得直接檢查角色。

#### Scenario: 產品刪除權限檢查
- **WHEN** 使用者呼叫 DELETE /api/products/{id}
- **THEN** 系統 SHALL 檢查 `product.delete` 權限
- **THEN** 無此權限的使用者 SHALL 收到 403 回應

### Requirement: 使用者帳號停用
users 表 SHALL 有 `is_active` boolean 欄位，預設為 true。

#### Scenario: 停用帳號存取阻擋
- **WHEN** is_active 為 false 的使用者發送 API 請求
- **THEN** EnsureUserIsActive middleware SHALL 回傳 403 回應

### Requirement: 預設使用者 Seed
Seeder SHALL 建立一個 super-admin 使用者和一個 manager 使用者。

#### Scenario: 預設使用者建立
- **WHEN** 執行 Seeder
- **THEN** 系統 SHALL 建立 super-admin 使用者（開發者）和 manager 使用者（客戶）
