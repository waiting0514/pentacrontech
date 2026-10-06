## MODIFIED Requirements

### Requirement: 登入頁面
系統 SHALL 提供使用 Element Plus 表單元件的登入頁面。

#### Scenario: 登入成功
- **WHEN** 使用者輸入正確的 username 和密碼並提交
- **THEN** 系統 SHALL 儲存 token 至 localStorage 並透過 vue-router 導航至首頁

#### Scenario: 登入失敗
- **WHEN** 使用者輸入錯誤的 username 或密碼
- **THEN** 系統 SHALL 使用 Element Plus Message 顯示錯誤訊息

#### Scenario: username 欄位驗證
- **WHEN** 使用者未填入 username 即提交表單
- **THEN** 系統 SHALL 顯示「請輸入帳號」提示，不送出請求

## ADDED Requirements

### Requirement: API 登入以 username 認證
`POST /api/login` SHALL 接受 `{username, password}` 作為 request body，以 `username` 查詢使用者。

驗證規則：
- `username`: required, string
- `password`: required, string

#### Scenario: 以 username 登入成功
- **WHEN** POST `/api/login` 帶有正確的 username 與 password
- **THEN** 系統 SHALL 回傳 Sanctum token 與使用者資訊

#### Scenario: username 不存在
- **WHEN** POST `/api/login` 帶有不存在的 username
- **THEN** 系統 SHALL 回傳 validation error，field 為 `username`

#### Scenario: 帳號已停用
- **WHEN** POST `/api/login` 的 username 對應使用者 `is_active` 為 false
- **THEN** 系統 SHALL 回傳 validation error，提示帳號已停用

### Requirement: API 註冊新增 username
`POST /api/register` SHALL 額外接受 `username` 欄位。

驗證規則：
- `username`: required, string, max:50, unique:users, regex:/^[a-zA-Z0-9_]+$/

#### Scenario: 以 username 註冊成功
- **WHEN** POST `/api/register` 帶有合法 username、name、email、password
- **THEN** 系統 SHALL 建立使用者（含 username）並回傳 token

#### Scenario: username 已被使用
- **WHEN** POST `/api/register` 的 username 已在 users 表存在
- **THEN** 系統 SHALL 回傳 validation error

#### Scenario: username 含非法字元
- **WHEN** POST `/api/register` 的 username 包含空格或特殊字元
- **THEN** 系統 SHALL 回傳 validation error
