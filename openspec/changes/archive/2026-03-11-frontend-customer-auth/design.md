## Context

目前系統有完整的後台管理員帳號體系（`users` 表 + Sanctum Bearer token + Spatie Permission），前台為純訪客瀏覽，詢價以無帳號訪客流程送出。

本次在不破壞現有後台架構的前提下，讓前台訪客可以註冊成為客戶帳號，登入後自動帶入個人資料輔助詢價，並可查閱歷史訂單。

## Goals / Non-Goals

**Goals:**
- 前台客戶可自行註冊、登入、登出
- 共用 `users` 表，以 Spatie `customer` role 識別身份
- `customer_profiles` 存放前台額外欄位（company_name、phone、address）
- 登入後詢價結帳頁自動帶入 profile 資料（可覆寫）
- 訂單寫入時關聯已登入客戶（`customer_id` nullable）
- 前台我的訂單：列表 + 詳情
- 後台完全不受影響（admin guard / Sanctum 路線不動）

**Non-Goals:**
- Email 驗證（可後續再加）
- 第三方 OAuth 登入
- 客戶端密碼重設（暫不實作）
- 後台管理客戶帳號列表（本次範圍外）

## Decisions

### 1. 共用 `users` 表，以 role 區分身份

**決策**：前台客戶使用現有 `users` 表，註冊時以 Spatie Permission 分配 `customer` role。

**理由**：
- 避免維護兩套 Authenticatable model
- Spatie Permission 已整合，可直接使用 `role:customer` middleware
- `users` 表欄位（name、email、password）對客戶完全適用

**替代方案**：另建 `customers` 表 → 需維護兩套 Auth guard，複雜度高，不採用。

---

### 2. `customer_profiles` 一對一擴充欄位

**決策**：建立 `customer_profiles` 表（`user_id` FK unique、`company_name`、`phone`、`address`），以 `hasOne` 關聯 User。

**理由**：
- 保持 `users` 表簡潔，不污染後台欄位
- Profile 可為 null（客戶未填寫時），不影響 users 表查詢
- 未來可擴充更多前台專屬欄位

---

### 3. 前台使用 `web` guard（Session），後台隔離

**決策**：前台登入沿用 Laravel 預設 `web` guard（Session-based）。後台 API 使用 `auth:sanctum`，另外加上 `role:super-admin|admin` middleware 防止客戶誤入後台。

**理由**：
- 前台 Blade 頁面適合 Session，不需 Bearer token
- Sanctum + role middleware 雙重保護後台，即使客戶已登入 session 也無法存取後台 API
- 實作最簡，無需新增 guard config

---

### 4. `orders.customer_id` nullable FK

**決策**：orders 表新增 `customer_id` nullable，關聯 `users.id`（on delete set null）。

**理由**：
- 訪客訂單 customer_id 為 null，保持向後相容
- 客戶刪除帳號時訂單不連帶刪除（set null）
- InquiryController::store() 透過 `auth()->id()` 取值，未登入自動為 null

---

### 5. 詢價頁帶入資料以 Blade server-side 輸出

**決策**：已登入時，Controller 將 customer profile 傳入 view，以 `value="{{ old('company_name', $customer?->company_name) }}"` 預填欄位。

**理由**：
- 避免前端 JS 額外 API call
- Alpine.js cart store 無需感知 auth 狀態
- `old()` 機制確保驗證失敗後仍保留使用者修改值

## Risks / Trade-offs

- **[Risk] 客戶以 `web` guard 登入後可否存取後台？** → Mitigation：後台路由（`/admin`）加上 `role:super-admin|admin` middleware，customer role 會被 403 拒絕
- **[Risk] users 表混用後查詢效能** → Mitigation：後台現有查詢均有 role 篩選，Spatie Permission 有 cache，影響微小
- **[Risk] customer_profiles 為 null 時詢價頁預填空白** → Mitigation：view 使用 `$customer?->profile?->company_name` optional chaining，null 安全

## Migration Plan

1. 新增 `customer_profiles` migration
2. 新增 `orders.customer_id` migration（add column）
3. Seeder 建立 `customer` role（Spatie Permission）
4. 新增前台 Auth 路由與 Controller
5. 修改 `InquiryController` 與 inquiry view
6. 新增會員中心、歷史訂單 Blade 頁面
7. 後台路由加 `role:super-admin|admin` middleware（確認隔離）
