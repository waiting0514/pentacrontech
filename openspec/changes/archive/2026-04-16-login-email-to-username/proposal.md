## Why

目前後台（Admin SPA）與前台（Customer Web）登入皆使用 email 作為帳號識別欄位。業務需求希望改用 username（帳號）登入，讓使用者體驗更直覺，同時保留 email 欄位供通知、密碼重設等功能使用。

## What Changes

- **BREAKING** 登入 API (`POST /api/login`) 與前台登入 (`POST /login`) 的 credential 欄位從 `email` 改為 `username`
- 新增 `username` 欄位至 `users` 資料表（unique, required）
- 後台 Vue 登入頁改為輸入 username + password
- 前台 Blade 登入頁改為輸入 username + password
- 前台與後台註冊流程新增 `username` 欄位（email 保留為必填）
- Seeder 中的測試帳號加入 username
- 後台使用者管理介面新增 username 欄位的顯示與編輯

## Capabilities

### New Capabilities

_無新增能力_

### Modified Capabilities

- `admin-auth`: 登入頁面與認證流程從 email 改為 username
- `frontend-customer-auth`: 前台登入與註冊從 email 改為 username
- `user-management`: 使用者 CRUD 需支援 username 欄位

## Impact

- **資料庫**: `users` 表新增 `username` 欄位（需 migration）
- **API**: `POST /api/login` request body 從 `{email, password}` 改為 `{username, password}`；`POST /api/users` 新增 `username` 必填欄位
- **前端路由**: 無變動
- **既有使用者**: 需在 migration 或 seeder 中為既有使用者補填 username（可用 email 前綴或 name 作為預設值）
- **密碼重設 / Email 通知**: 不受影響（繼續使用 email 欄位）
