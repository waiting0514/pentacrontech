## ADDED Requirements

### Requirement: 使用者列表 API
GET /api/users SHALL 回傳使用者列表，需 `user.manage` 權限。

#### Scenario: Manager 查看使用者
- **WHEN** manager 呼叫 GET /api/users
- **THEN** 系統 SHALL 回傳該系統下的所有使用者（不含 super-admin）

#### Scenario: Super Admin 查看使用者
- **WHEN** super-admin 呼叫 GET /api/users
- **THEN** 系統 SHALL 回傳所有使用者

#### Scenario: Editor 無權限
- **WHEN** editor 呼叫 GET /api/users
- **THEN** 系統 SHALL 回傳 403

### Requirement: 建立使用者 API
POST /api/users SHALL 建立新使用者，需 `user.manage` 權限。

#### Scenario: Manager 建立 editor
- **WHEN** manager 發送 POST /api/users 並指定 role 為 editor
- **THEN** 系統 SHALL 建立使用者並指派 editor 角色

#### Scenario: Manager 建立 manager
- **WHEN** manager 發送 POST /api/users 並指定 role 為 manager
- **THEN** 系統 SHALL 建立使用者並指派 manager 角色

#### Scenario: 不可建立 super-admin
- **WHEN** 任何使用者嘗試指定 role 為 super-admin
- **THEN** 系統 SHALL 拒絕並回傳 422

### Requirement: 切換使用者狀態 API
PUT /api/users/{id}/toggle SHALL 切換使用者 is_active 狀態。

#### Scenario: 停用使用者
- **WHEN** manager 對 active 使用者呼叫 toggle
- **THEN** 系統 SHALL 將 is_active 設為 false

#### Scenario: 不可停用自己
- **WHEN** 使用者嘗試停用自己的帳號
- **THEN** 系統 SHALL 回傳 422 錯誤

### Requirement: 刪除使用者 API
DELETE /api/users/{id} SHALL 僅限 super-admin 執行。

#### Scenario: Super Admin 刪除使用者
- **WHEN** super-admin 呼叫 DELETE /api/users/{id}
- **THEN** 系統 SHALL 刪除該使用者

#### Scenario: Manager 嘗試刪除
- **WHEN** manager 呼叫 DELETE /api/users/{id}
- **THEN** 系統 SHALL 回傳 403
