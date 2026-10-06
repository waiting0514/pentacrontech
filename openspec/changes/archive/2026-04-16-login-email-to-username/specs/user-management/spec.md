## ADDED Requirements

### Requirement: users 資料表新增 username 欄位
系統 SHALL 透過 migration 在 `users` 資料表新增 `username` 欄位：`string(50)`, unique, not null。

既有使用者 SHALL 以 email 的 `@` 前綴自動回填 username，重複時加數字後綴（`_2`, `_3`...）。

#### Scenario: 執行 migration 新增欄位
- **WHEN** 執行 `artisan migrate`
- **THEN** `users` 資料表 SHALL 存在 `username` 欄位且有 UNIQUE constraint

#### Scenario: 既有使用者自動回填 username
- **WHEN** migration 執行時 users 表已有資料
- **THEN** 每筆使用者的 `username` SHALL 被填入 email `@` 前綴值
- **THEN** 若前綴重複，SHALL 加數字後綴確保 unique

### Requirement: User model 支援 username
`User` model 的 `$fillable` SHALL 包含 `username`。

#### Scenario: 可透過 mass assignment 寫入 username
- **WHEN** 呼叫 `User::create(['username' => 'john', ...])` 
- **THEN** 系統 SHALL 成功建立使用者且 `username` 為 `john`

## MODIFIED Requirements

### Requirement: 建立使用者 API
POST /api/users SHALL 建立新使用者，需 `user.manage` 權限。

驗證規則新增：
- `username`: required, string, max:50, unique:users, regex:/^[a-zA-Z0-9_]+$/

#### Scenario: Manager 建立 editor
- **WHEN** manager 發送 POST /api/users 並指定 username、name、email、role 為 editor
- **THEN** 系統 SHALL 建立使用者（含 username）並指派 editor 角色

#### Scenario: Manager 建立 manager
- **WHEN** manager 發送 POST /api/users 並指定 username、name、email、role 為 manager
- **THEN** 系統 SHALL 建立使用者（含 username）並指派 manager 角色

#### Scenario: 不可建立 super-admin
- **WHEN** 任何使用者嘗試指定 role 為 super-admin
- **THEN** 系統 SHALL 拒絕並回傳 422

#### Scenario: username 重複
- **WHEN** POST /api/users 的 username 已存在
- **THEN** 系統 SHALL 回傳 validation error
