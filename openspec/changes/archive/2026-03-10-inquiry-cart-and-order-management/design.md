## Context

本專案為 Laravel 12 + Blade 前台 / Vue 3 SPA 後台的多租戶 CMS。前台**嚴禁**使用 Vue/React，必須以 Alpine.js 處理客戶端互動；後台使用 Vue 3 Composition API + Element Plus + Pinia。

目前產品詳情頁僅有靜態「立即詢價」連結導向聯絡頁，訪客無法一次收集多件商品送出詢價。後台也沒有任何訂單管理介面。

## Goals / Non-Goals

**Goals:**

- 以 Alpine.js `$persist` 實作前台跨頁購物車（LocalStorage 持久化）
- 前台 `/inquiry` 結帳頁：購物車列表 + 聯絡表單，標準 HTML POST 送出
- 後端接收詢價、寫入 `orders` / `order_items`、發送 Email 通知給管理員
- 後台 API + Vue 3 介面管理訂單（列表分頁篩選、詳情、狀態更新）
- 全站 i18n 覆蓋（前台 `frontend.php`、後台 `admin.php` + JSON）

**Non-Goals:**

- 金流 / 付款整合
- 訪客帳號系統或登入驗證
- 訂單 PDF 匯出
- 即時推播通知（WebSocket）
- 狀態更新時通知客戶
- 後台建立/刪除訂單（僅查詢與狀態更新）

## Decisions

### 1. Alpine.js Persist 插件載入方式

**決定**：在 `layouts/app.blade.php` 以 CDN 依序載入 `@alpinejs/persist`（先）再載入 Alpine core（後），符合 Alpine 插件初始化規範。

**替代方案考慮**：透過 npm 安裝並以 Vite bundle — 但前台 `app.js` 目前僅有少量樣式邏輯，引入 npm Alpine 會增加建構複雜度；CDN 方式符合現有架構。

### 2. 購物車 Store 宣告位置

**決定**：在 `layouts/app.blade.php` 的 `<script>` 區塊以 `document.addEventListener('alpine:init', ...)` 宣告 `Alpine.store('cart', { ... })`，使用 `$persist` 綁定 `items` 陣列。

**替代方案考慮**：各頁面 `@push('scripts')` 中宣告 — 會導致 store 在不同頁面重複初始化，不一致。

### 3. 詢價表單送出機制

**決定**：標準 HTML `<form method="POST">` 搭配隱藏欄位 `<input type="hidden" name="cart_items" :value="JSON.stringify($store.cart.items)">`，伺服器端以 `InquiryController@store` 接收並驗證。

**替代方案考慮**：Fetch/Axios AJAX 送出 — 需額外處理 CSRF header 與回應跳轉，且標準 POST 對 SEO 爬蟲與無 JS 環境更友善。

### 4. 訂單編號格式

**決定**：`INQ-YYYYMMDD-XXXX`（4 位流水號），在 `Order::creating` 事件中自動產生，使用當日已存在訂單數 +1，並以 `DB::transaction + lockForUpdate` 避免 race condition。

### 5. 後台 API 路由群組

**決定**：新增 `Route::prefix('admin/orders')` 群組，置於現有 `auth:sanctum` middleware 內。Controller 放在 `App\Http\Controllers\Api\Admin\` 命名空間，與其他後台 API 結構一致。

### 6. 後台 Vue 路由結構

**決定**：新增 `/admin/orders`（列表）與 `/admin/orders/:id`（詳情）兩個 children 路由，掛載於現有 Layout，使用 `order.view` permission meta，走現有 permission guard。

### 7. Email 通知

**決定**：
- 詢價送出後，以 `Mail` facade 寄出 Markdown Mailable（`InquirySubmittedMail`）給 **`Project::first()->contact_email`**（固定讀取現有 Project 設定）
- 狀態更新時**不發信**給客戶
- 若 SMTP 未設定，以 `try/catch` 吞掉錯誤、仍完成寫庫，確保詢價不遺失

### 8. 後台商品價格快照

**決定**：`order_items` 儲存下單當下的 `product_name` 與 `price`（從資料庫重新讀取，忽略前端傳來的價格），防止前端偽造金額。

## Risks / Trade-offs

- **LocalStorage 購物車被清除** → 使用者清除瀏覽器資料後購物車消失，屬預期行為，B2B 場景可接受
- **訂單編號 race condition** → 以 `DB::transaction + lockForUpdate` 緩解；B2B 詢價量低，極小機率衝突可接受
- **Alpine CDN 版本浮動** → 現有 `alpinejs@3.x.x` 為浮動版本，`@alpinejs/persist` 同樣使用 `@3.x.x`；未來應考慮固定版號
- **Email 靜默失敗** → try/catch 保護確保寫庫成功，但管理員可能錯過通知；建議日後補上 queue 重試機制

## Migration Plan

1. 執行 `./vendor/bin/sail artisan migrate`（新增 `orders`、`order_items`）
2. 部署前台 Blade 修改（無需 build，Blade 即時生效）
3. 執行 `./vendor/bin/sail npm run build`（後台 Vue bundle）
4. 確認 `.env` 中 SMTP 設定正確，否則 Email 靜默失敗但不影響功能
5. 無需 rollback migration（新增資料表，不影響現有資料）
