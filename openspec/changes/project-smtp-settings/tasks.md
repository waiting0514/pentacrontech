## 1. Database & Model

- [x] 1.1 建立 migration，在 `projects` 表新增 7 個 SMTP 欄位（smtp_host, smtp_port, smtp_encryption, smtp_username, smtp_password, smtp_from_address, smtp_from_name），皆 nullable
- [x] 1.2 更新 `Project` model：fillable 新增 SMTP 欄位、casts 加入 `smtp_password => encrypted` 和 `smtp_port => integer`、hidden 加入 `smtp_password`
- [x] 1.3 更新 `ProjectResource`：排除 `smtp_password`，新增 `has_smtp_password` 布林欄位

## 2. Service Layer

- [x] 2.1 建立 `ProjectMailService`，實作 `getMailer(Project)` 方法：透過 runtime config + `Mail::mailer()` 動態建立 Mailer
- [x] 2.2 實作 fallback 邏輯：Project 未設定 SMTP 時回傳預設 mailer

## 3. API Controller

- [x] 3.1 更新 `ProjectController@update` 的驗證規則，加入 SMTP 欄位驗證（host 字串、port 整數、encryption in tls/ssl、from_address email 格式）
- [x] 3.2 實作密碼更新邏輯：空值時不覆蓋現有密碼
- [x] 3.3 新增 `POST /api/projects/{id}/test-smtp` 端點，使用 Project SMTP 設定發送測試信至 contact_email
- [x] 3.4 註冊 test-smtp 路由

## 4. 寄信邏輯改寫

- [x] 4.1 修改 `InquiryController`：改用 `ProjectMailService` 取得 Mailer 寄信
- [x] 4.2 修改 `ContactController`：啟用寄信功能，使用 `ProjectMailService`

## 5. 前端 UI

- [x] 5.1 在 `settings/index.vue` 新增「郵件設定」區塊，包含 SMTP 主機、連接埠（下拉 587/465）、加密方式、帳號、密碼、寄件人地址、寄件人名稱欄位
- [x] 5.2 密碼欄位處理：已設定時顯示 placeholder 提示，未填寫時不送出該欄位
- [x] 5.3 新增測試寄信按鈕，呼叫 test-smtp API 並顯示結果
- [x] 5.4 在 `projectService` 中新增 `testSmtp(projectId)` API 方法

## 6. 測試

- [x] 6.1 測試 SMTP 設定 CRUD（儲存、更新、密碼不覆蓋）
- [x] 6.2 測試 API 回傳不含 smtp_password 明文
- [x] 6.3 測試 ProjectMailService fallback 邏輯
