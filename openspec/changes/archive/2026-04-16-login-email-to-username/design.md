## Context

目前系統的後台（Admin SPA, Sanctum token）與前台（Customer Web, Session）登入皆以 `email` 作為帳號識別。需改為 `username` 登入，同時保留 `email` 欄位供通知與密碼重設使用。

涉及的現有程式碼：
- `AuthController::createToken()` — API 登入，`User::where('email', ...)`
- `Frontend\Auth\LoginController::store()` — 前台登入，`Auth::attempt(['email' => ...])`
- `Frontend\Auth\RegisterController::store()` — 前台註冊
- `Api\UserController::store()` / `update()` — 後台使用者管理
- `resources/js/admin/views/login/index.vue` — 後台 Vue 登入頁
- `resources/views/auth/login.blade.php` — 前台 Blade 登入頁
- `User` model `$fillable` 無 `username`
- `users` 資料表無 `username` 欄位

## Goals / Non-Goals

**Goals:**
- 後台與前台登入從 email 改為 username
- `users` 表新增 `username` 欄位（unique, required）
- 註冊流程新增 username 輸入
- 後台使用者管理支援 username CRUD
- 既有使用者透過 migration 自動補填 username

**Non-Goals:**
- 不移除 email 欄位（保留供通知、密碼重設）
- 不修改密碼重設流程（仍用 email）
- 不支援「email 或 username 皆可登入」的雙重模式
- 不變更 Sanctum / Session guard 架構

## Decisions

### 1. username 欄位規格

`username`: `string(50)`, unique, not null。

驗證規則：`required|string|max:50|unique:users,username|regex:/^[a-zA-Z0-9_]+$/`

只允許英數字與底線，避免特殊字元造成的安全或顯示問題。

**替代方案**：允許中文或更多字元 — 放棄，因為帳號欄位應保持簡潔可預測。

### 2. 既有使用者的 username 填充

在 migration 中用 `email` 的 `@` 前綴作為預設 username，若重複則加數字後綴。這樣既有環境執行 migrate 後不會因 NOT NULL + UNIQUE 約束失敗。

**替代方案**：先 nullable 再手動填 — 放棄，因為會留下資料不一致的窗口。

### 3. Auth::attempt 的 credential key

前台 `Auth::attempt()` 直接改傳 `['username' => ..., 'password' => ...]`，Laravel 會自動用 `username` 欄位查詢。後台 API 登入維持手動 `User::where('username', ...)` 查詢 + Hash::check 的模式。

### 4. 前端登入表單 icon

後台 Vue 登入頁的 icon 從 `Message`（信封）改為 `User`（人像），更符合帳號輸入的語意。

### 5. i18n key 調整

新增 `admin.auth.username_required`、`frontend.auth.username` 等 i18n key。既有 email 相關 key 保留不刪（註冊頁仍用）。

## Risks / Trade-offs

**[既有使用者 username 衝突]** → migration 中處理重複：email 前綴相同時加 `_2`, `_3` 後綴。

**[BREAKING API 變更]** → `POST /api/login` request body 從 `{email, password}` 改為 `{username, password}`。若有外部 API 呼叫者需同步更新。此為內部系統，風險可控。

**[前台登入 error field 變更]** → 錯誤訊息的 field name 從 `email` 改為 `username`，前端表單需對應調整。

## Migration Plan

1. 建立 migration：`users` 表加 `username` 欄位，回填既有資料
2. 更新 `User` model `$fillable`
3. 修改後端 controller（AuthController, LoginController, RegisterController, UserController）
4. 修改前端頁面（Vue 登入頁、Blade 登入頁、Blade 註冊頁）
5. 新增 i18n key
6. 更新 Seeder 的測試帳號資料
7. 執行 `sail artisan migrate` 與 `sail artisan test` 驗證

**Rollback**：revert migration（drop username 欄位）+ revert 程式碼即可。
