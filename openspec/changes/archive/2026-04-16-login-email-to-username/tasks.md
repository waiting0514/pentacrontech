## 1. 資料庫與 Model

- [x] 1.1 建立 migration：`users` 表新增 `username` 欄位（string(50), unique, not null），並回填既有使用者（email `@` 前綴，重複加數字後綴）
- [x] 1.2 `User` model `$fillable` 加入 `username`

## 2. 後端 API 登入與註冊

- [x] 2.1 `AuthController::createToken()` 驗證規則從 email 改為 username，查詢改 `User::where('username', ...)`，error field 改為 `username`
- [x] 2.2 `AuthController::register()` 新增 `username` 驗證規則（required, string, max:50, unique, regex）並寫入資料庫

## 3. 前台登入與註冊

- [x] 3.1 `Frontend\Auth\LoginController::store()` 驗證與 `Auth::attempt()` 從 email 改為 username，error field 與 `withInput` 改為 `username`
- [x] 3.2 `Frontend\Auth\RegisterController::store()` 新增 `username` 驗證規則並寫入 `User::create()`
- [x] 3.3 `resources/views/auth/login.blade.php` 表單 input 從 email 改為 username（name、type、label）
- [x] 3.4 `resources/views/auth/register.blade.php` 表單新增 username input 欄位

## 4. 後台 Vue 登入頁

- [x] 4.1 `resources/js/admin/views/login/index.vue` 的 `loginForm.email` 改為 `loginForm.username`，移除 email 格式驗證改為 required string，icon 從 Message 改為 User
- [x] 4.2 新增 i18n key：`admin.auth.username_required`、`admin.auth.username`（zh-TW 與 en）

## 5. 後台使用者管理

- [x] 5.1 `UserController::store()` 新增 `username` 驗證規則並寫入
- [x] 5.2 `UserController::update()` 新增 `username` 驗證規則（unique 排除自身）

## 6. 前台 i18n

- [x] 6.1 `lang/zh-TW/frontend.php` 與 `lang/en/frontend.php` 的 `auth` 區塊新增 `username` key

## 7. Seeder 與測試

- [x] 7.1 更新 DatabaseSeeder / UserSeeder 的測試帳號加入 username 欄位
- [x] 7.2 執行 `sail artisan migrate` 驗證 migration 成功
- [x] 7.3 執行 `sail artisan test` 確認既有測試通過，修正因 email→username 造成的測試失敗
