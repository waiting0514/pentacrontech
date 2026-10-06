## ADDED Requirements

### Requirement: customer_profiles 資料表
系統 SHALL 建立 `customer_profiles` 資料表，欄位如下：

| 欄位 | 類型 | 說明 |
|------|------|------|
| `id` | bigint PK | |
| `user_id` | FK → users (unique) | cascade delete，一對一 |
| `company_name` | string nullable | 公司名稱 |
| `phone` | string nullable | 電話 |
| `address` | string nullable | 地址 |
| `created_at` / `updated_at` | timestamp | |

#### Scenario: 建立 customer_profiles 資料表
- **WHEN** 執行 `artisan migrate`
- **THEN** `customer_profiles` 資料表 SHALL 存在且 `user_id` 有 UNIQUE constraint

---

### Requirement: customer role 建立
系統 SHALL 在 Seeder 或 migration 中建立 `customer` role（透過 Spatie Permission），前台客戶註冊後 SHALL 自動分配此 role。

#### Scenario: 客戶註冊後取得 customer role
- **WHEN** 客戶完成前台註冊
- **THEN** 該 User SHALL 擁有 `customer` role
- **THEN** 該 User SHALL 不具備 `admin` 或 `super-admin` role

---

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

---

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

---

### Requirement: 前台客戶登出
系統 SHALL 提供 `POST /logout` 路由，清除 `web` guard Session 並 redirect 至 `/`。

#### Scenario: 成功登出
- **WHEN** 已登入客戶送出 POST `/logout`
- **THEN** Session SHALL 清除
- **THEN** 使用者 SHALL 被 redirect 至首頁

---

### Requirement: 會員資料修改頁
系統 SHALL 提供 `GET /account`（顯示表單）與 `POST /account`（儲存）路由，需 `auth` middleware（`web` guard）。

可修改欄位：
- `name`（users 表）
- `company_name`、`phone`、`address`（customer_profiles 表，updateOrCreate）

#### Scenario: 已登入客戶查看會員中心
- **WHEN** 已登入客戶訪問 `GET /account`
- **THEN** 頁面 SHALL 顯示目前的 name、company_name、phone、address

#### Scenario: 成功修改資料
- **WHEN** 已登入客戶 POST `/account` 帶有合法欄位
- **THEN** `users.name` SHALL 更新
- **THEN** `customer_profiles` 對應欄位 SHALL 更新（updateOrCreate）
- **THEN** 頁面 SHALL 顯示成功提示

#### Scenario: 未登入存取會員中心
- **WHEN** 未登入使用者訪問 `GET /account`
- **THEN** 系統 SHALL redirect 至 `/login`

---

### Requirement: 後台路由隔離
後台路由 SHALL 加上 `role:super-admin|admin` middleware，確保擁有 `customer` role 的前台客戶無法存取後台功能。

#### Scenario: 已登入客戶存取後台 API
- **WHEN** 擁有 `customer` role 的已登入 User 呼叫後台 API（`/api/admin/*`）
- **THEN** 系統 SHALL 回傳 403 Forbidden

#### Scenario: 已登入 admin 存取後台 API
- **WHEN** 擁有 `admin` role 的 User 呼叫後台 API
- **THEN** 系統 SHALL 正常回應

---

### Requirement: i18n 翻譯（auth / account）
系統 SHALL 在 `lang/zh-TW/frontend.php` 與 `lang/en/frontend.php` 新增 `auth` 與 `account` 翻譯區塊。

所需 key：`auth.login`、`auth.register`、`auth.logout`、`auth.username`、`auth.username_hint`、`auth.email`、`auth.password`、`auth.name`、`auth.password_confirmation`、`account.title`、`account.company_name`、`account.phone`、`account.address`、`account.save`、`account.saved`

#### Scenario: 前台顯示翻譯字串
- **WHEN** 語系切換為 zh-TW 或 en
- **THEN** 登入、註冊、會員中心頁面 SHALL 顯示對應語系文字
