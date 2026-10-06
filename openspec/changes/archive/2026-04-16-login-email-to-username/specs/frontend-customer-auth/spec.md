## MODIFIED Requirements

### Requirement: 前台客戶註冊
系統 SHALL 提供 `GET /register`（顯示表單）與 `POST /register`（處理註冊）路由。

POST 驗證規則：
- `username`: required, string, max:50, unique:users, regex:/^[a-zA-Z0-9_]+$/
- `name`: required, string, max:255
- `email`: required, email, unique:users, max:255
- `password`: required, string, min:8, confirmed

成功後 SHALL 自動登入並 redirect 至 `/account`。

#### Scenario: 成功註冊
- **WHEN** POST `/register` 帶有合法欄位（含 username）
- **THEN** 系統 SHALL 建立 User（含 username）並分配 `customer` role
- **THEN** 系統 SHALL 自動登入並 redirect 至 `/account`

#### Scenario: Username 已存在
- **WHEN** POST `/register` 的 username 已在 `users` 表存在
- **THEN** 系統 SHALL 回傳 validation error，不建立帳號

#### Scenario: Email 已存在
- **WHEN** POST `/register` 的 email 已在 `users` 表存在
- **THEN** 系統 SHALL 回傳 validation error，不建立帳號

#### Scenario: 密碼確認不符
- **WHEN** POST `/register` 的 `password` 與 `password_confirmation` 不同
- **THEN** 系統 SHALL 回傳 validation error

### Requirement: 前台客戶登入
系統 SHALL 提供 `GET /login`（顯示表單）與 `POST /login`（處理登入）路由，使用 `web` guard（Session）。

#### Scenario: 成功登入
- **WHEN** POST `/login` 帶有正確 username / password 且 User 有 `customer` role
- **THEN** 系統 SHALL 建立 Session 並 redirect 至 `/account`

#### Scenario: 帳密錯誤
- **WHEN** POST `/login` 帶有錯誤 username 或 password
- **THEN** 系統 SHALL 回傳 authentication error，不建立 Session

#### Scenario: admin 帳號無法從前台登入
- **WHEN** POST `/login` 的 User 只有 `admin` 或 `super-admin` role（無 `customer` role）
- **THEN** 系統 SHALL 拒絕登入並回傳 error

### Requirement: i18n 翻譯（auth / account）
系統 SHALL 在 `lang/zh-TW/frontend.php` 與 `lang/en/frontend.php` 的 `auth` 區塊新增 `username` 翻譯 key。

所需新增 key：`auth.username`

#### Scenario: 前台顯示 username 翻譯字串
- **WHEN** 語系切換為 zh-TW 或 en
- **THEN** 登入與註冊頁面的帳號欄位 SHALL 顯示對應語系文字（「帳號」/「Username」）
