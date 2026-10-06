## 1. 資料庫：Migration 與 Model

- [x] 1.1 建立 `customer_profiles` migration（user_id UNIQUE FK → users cascade delete、company_name、phone、address nullable）
- [x] 1.2 建立 `orders` add column migration（新增 `customer_id` nullable FK → users on delete set null）
- [x] 1.3 執行 `./vendor/bin/sail artisan migrate` 確認無錯誤
- [x] 1.4 在 `App\Models\User` 新增 `hasOne CustomerProfile` 關聯
- [x] 1.5 建立 `App\Models\CustomerProfile`（`$fillable`: user_id、company_name、phone、address；`belongsTo User`）
- [x] 1.6 在 `App\Models\Order` 新增 `customer_id` 至 `$fillable`，並新增 `belongsTo User` 關聯（`customer()`）

## 2. Role 與權限設定

- [x] 2.1 在 `DatabaseSeeder`（或新增 RoleSeeder）使用 Spatie Permission 建立 `customer` role
- [x] 2.2 執行 `./vendor/bin/sail artisan db:seed` 確認 `customer` role 存在
- [x] 2.3 在後台 API 路由群組（`routes/api.php` 的 `auth:sanctum` middleware 群組）加上 `role:super-admin|admin` middleware，確保客戶無法存取後台 API

## 3. 前台 Auth：Controller 與路由

- [x] 3.1 建立 `app/Http/Controllers/Frontend/Auth/RegisterController.php`（`showForm()` 渲染註冊頁、`store()` 處理註冊）
- [x] 3.2 `store()` 實作：驗證欄位 → 建立 User → 分配 `customer` role → 自動登入（`Auth::login()`）→ redirect `/account`
- [x] 3.3 建立 `app/Http/Controllers/Frontend/Auth/LoginController.php`（`showForm()` 渲染登入頁、`store()` 處理登入、`destroy()` 登出）
- [x] 3.4 `store()` 實作：以 `Auth::attempt()` 驗證 → 確認 User 有 `customer` role → 登入成功 redirect `/account`，失敗回傳 error
- [x] 3.5 在 `routes/web.php` 新增 guest middleware 保護的路由：`GET /register`、`POST /register`、`GET /login`、`POST /login`
- [x] 3.6 在 `routes/web.php` 新增 `POST /logout`（auth middleware）

## 4. 前台 Auth：Blade 頁面

- [x] 4.1 建立 `resources/views/auth/register.blade.php`（繼承 layouts.app，含 name、email、password、password_confirmation 欄位）
- [x] 4.2 建立 `resources/views/auth/login.blade.php`（繼承 layouts.app，含 email、password 欄位）
- [x] 4.3 在前台 header（`partials/header.blade.php`）新增：未登入顯示「登入」連結；已登入顯示客戶姓名 + 「我的帳戶」+ 「登出」

## 5. 會員中心：AccountController 與路由

- [x] 5.1 建立 `app/Http/Controllers/Frontend/AccountController.php`
- [x] 5.2 實作 `index()`：讀取已登入 User 及其 CustomerProfile，傳入 view
- [x] 5.3 實作 `update()`：驗證 name、company_name、phone、address → 更新 `users.name` → `CustomerProfile::updateOrCreate` → redirect 回 `/account` 含成功提示
- [x] 5.4 在 `routes/web.php` 新增 auth middleware 保護的路由：`GET /account`、`POST /account`（命名：`account.show`、`account.update`）

## 6. 會員中心：Blade 頁面

- [x] 6.1 建立 `resources/views/account/index.blade.php`（繼承 layouts.app，含 name、company_name、phone、address 欄位，顯示成功提示）
- [x] 6.2 在會員中心頁面加入「我的訂單」導覽連結

## 7. 歷史訂單：Controller 與路由

- [x] 7.1 建立 `app/Http/Controllers/Frontend/OrderHistoryController.php`
- [x] 7.2 實作 `index()`：查詢 `orders` where `customer_id = auth()->id()`，分頁，傳入 view
- [x] 7.3 實作 `show()`：findOrFail → 驗證 `order->customer_id === auth()->id()`（不符合 abort 403）→ eager load `orderItems` → 計算總金額 → 傳入 view
- [x] 7.4 在 `routes/web.php` 新增 auth middleware 保護的路由：`GET /account/orders`、`GET /account/orders/{order}`（命名：`account.orders.index`、`account.orders.show`）

## 8. 歷史訂單：Blade 頁面

- [x] 8.1 建立 `resources/views/account/orders/index.blade.php`（訂單列表：order_number、status 標籤、日期、商品件數、詳情連結；空狀態提示）
- [x] 8.2 建立 `resources/views/account/orders/show.blade.php`（訂單詳情：客戶資訊區塊、商品列表含小計、訂單總金額、狀態）

## 9. 修改詢價流程

- [x] 9.1 修改 `InquiryController::index()`：已登入時取得 `auth()->user()->customerProfile`，以 `$profile` 傳入 view
- [x] 9.2 修改 `resources/views/inquiry/index.blade.php`：company_name、phone、address 欄位改用 `value="{{ old('company_name', $profile?->company_name) }}"` 預填（未登入時為空）
- [x] 9.3 修改 `InquiryController::store()`：寫入 Order 時加入 `customer_id => auth()->id()`（未登入時 `auth()->id()` 回傳 null，自動為 null）

## 10. i18n 翻譯檔更新

- [x] 10.1 在 `lang/zh-TW/frontend.php` 新增 `auth` 區塊（login、register、logout、email、password、name、password_confirmation 等）
- [x] 10.2 在 `lang/zh-TW/frontend.php` 新增 `account` 區塊（title、company_name、phone、address、save、saved、orders、no_orders）
- [x] 10.3 在 `lang/en/frontend.php` 新增對應英文翻譯

## 11. 驗收測試

- [x] 11.1 前台：完成客戶註冊 → 確認自動登入、`customer` role 正確分配、redirect 至 `/account`
- [x] 11.2 前台：登入 → 進入 `/inquiry` → 確認 company_name、phone、address 自動帶入 → 修改後送出 → 訂單 `customer_id` 正確關聯
- [x] 11.3 前台：訪客（未登入）進入 `/inquiry` → 確認欄位空白 → 送出後 `customer_id` 為 null
- [x] 11.4 前台：已登入客戶進入 `/account/orders` → 確認只顯示自己的訂單
- [x] 11.5 前台：嘗試查看他人訂單 `/account/orders/{otherId}` → 確認回傳 403
- [x] 11.6 前台：修改會員資料 → 確認 `users.name` 與 `customer_profiles` 欄位正確更新
- [x] 11.7 後台：以 `customer` role 帳號呼叫 `/api/admin/orders` → 確認回傳 403
- [x] 11.8 確認登出後 Session 清除，無法存取 `/account`（redirect 至 `/login`）
