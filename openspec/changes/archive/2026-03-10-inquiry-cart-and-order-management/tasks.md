## 1. 資料庫：Migration 與 Model

- [x] 1.1 建立 `orders` migration（含 `order_number` UNIQUE、`status` 預設 `pending`、`customer_*` 欄位）
- [x] 1.2 建立 `order_items` migration（含 FK → orders cascade delete、`product_id` nullable、`product_name` / `price` 快照欄位）
- [x] 1.3 執行 `./vendor/bin/sail artisan migrate` 確認無錯誤
- [x] 1.4 建立 `App\Models\Order`（`$fillable`、`hasMany orderItems`、`creating` 事件自動產生 `INQ-YYYYMMDD-XXXX` 編號）
- [x] 1.5 建立 `App\Models\OrderItem`（`$fillable`、`belongsTo order`）

## 2. 後台 API：Admin\OrderController

- [x] 2.1 建立 `app/Http/Controllers/Api/Admin/OrderController.php`
- [x] 2.2 實作 `index()`：分頁列表，支援 `?status=` 篩選與 `?search=` 關鍵字（order_number / company_name）
- [x] 2.3 實作 `show()`：回傳單一訂單 + eager load `orderItems`，加入 `subtotal` 計算欄位
- [x] 2.4 實作 `updateStatus()`：驗證 status 值，PATCH 更新並回傳更新後資料
- [x] 2.5 在 `routes/api.php` 新增 `admin/orders` 路由群組（index、show、updateStatus），置於 `auth:sanctum` middleware 內

## 3. 前台：InquiryController 與 Email

- [x] 3.1 建立 `app/Http/Controllers/Frontend/InquiryController.php`（`index()` 渲染結帳頁、`store()` 接收 POST）
- [x] 3.2 實作 `store()` 驗證（customer_name、customer_email、cart_items 必填）
- [x] 3.3 實作寫庫邏輯：解析 `cart_items` JSON → 以 `product_id` 從 DB 重新讀取 name/price → `DB::transaction` 建立 Order + OrderItems
- [x] 3.4 建立 `app/Mail/InquirySubmittedMail.php`（Markdown Mailable，含訂單編號、客戶資訊、商品列表）
- [x] 3.5 在 `store()` 中以 `try/catch` 發送 `InquirySubmittedMail` 至 `Project::first()->contact_email`
- [x] 3.6 在 `routes/web.php` 新增 `GET /inquiry`（`index`）與 `POST /inquiry`（`store`）路由

## 4. 前台 Blade：Layout 與 Header 修改

- [x] 4.1 在 `layouts/app.blade.php` 加入 `@alpinejs/persist` CDN（必須在 Alpine core 之前）
- [x] 4.2 在 `layouts/app.blade.php` 加入全域 `Alpine.store('cart', { ... })` script（含 `$persist`、`add`、`remove`、`updateQuantity`、`totalCount`、`clear` 方法）
- [x] 4.3 在 `partials/header.blade.php` 加入購物車圖示連結（連至 `/inquiry`）與動態徽章（`x-show="$store.cart.totalCount() > 0"` + `x-text`）

## 5. 前台 Blade：產品詳情頁修改

- [x] 5.1 在 `products/show.blade.php` 加入「加入詢價車」按鈕（`@click="$store.cart.add(...)"` 傳入 product_id、name、price、image）
- [x] 5.2 實作按鈕點擊後的 Toast 提示（純 Alpine.js，顯示 2 秒後自動消失）
- [x] 5.3 保留原有「立即詢價」連結（不移除）

## 6. 前台 Blade：詢價結帳頁與成功頁

- [x] 6.1 建立 `resources/views/inquiry/index.blade.php`（繼承 layouts.app）
- [x] 6.2 實作購物車商品列表（`x-for`、數量 +/- 按鈕、移除按鈕、小計即時計算）
- [x] 6.3 實作購物車為空時的提示區塊（`x-show`）
- [x] 6.4 實作聯絡資訊表單（customer_name、customer_email、company_name、notes）
- [x] 6.5 加入隱藏欄位 `<input type="hidden" name="cart_items" :value="JSON.stringify($store.cart.items)">`
- [x] 6.6 建立 `resources/views/inquiry/success.blade.php`，顯示成功訊息並加入清空購物車 JS（`Alpine.store('cart').clear()`）

## 7. i18n 翻譯檔更新

- [x] 7.1 在 `lang/zh-TW/frontend.php` 新增 `inquiry` 區塊（add_to_cart、cart_empty、go_shop、checkout、submit、success_title、success_message 等）
- [x] 7.2 在 `lang/en/frontend.php` 新增對應英文翻譯
- [x] 7.3 在 `lang/zh-TW/frontend.php` 的 `nav` 區塊新增 `inquiry`（購物車導覽文字）
- [x] 7.4 在 `lang/en/frontend.php` 的 `nav` 區塊新增 `inquiry`
- [x] 7.5 在 `lang/zh-TW/admin.php` 新增 `orders` 區塊（nav.orders、orders.title、orders.order_number、orders.company、orders.customer、orders.status、orders.date、orders.notes、orders.items、orders.subtotal、orders.total、orders.update_status、orders.status_pending / processing / completed / cancelled）
- [x] 7.6 在 `lang/en/admin.php` 新增對應英文翻譯

## 8. 後台 Vue：訂單管理元件

- [x] 8.1 建立 `resources/js/services/orderService.js`（封裝 `getOrders`、`getOrder`、`updateStatus` API 呼叫）
- [x] 8.2 在 `resources/js/services/index.js` 匯出 `orderService`
- [x] 8.3 建立 `resources/js/admin/views/orders/index.vue`（OrderList：el-table、狀態彩色標籤、篩選、搜尋、分頁）
- [x] 8.4 建立 `resources/js/admin/views/orders/detail.vue`（OrderDetail：區塊 A 客戶資訊、區塊 B 狀態控制、區塊 C 商品列表與總金額）
- [x] 8.5 在 `resources/js/admin/router/index.js` 新增 `/admin/orders` 與 `/admin/orders/:id` 路由（permission: `order.view`）
- [x] 8.6 在後台側邊欄 Layout 新增「詢價訂單」nav 入口

## 9. 驗收測試

- [x] 9.1 前台：在產品頁加入商品 → 導覽列徽章即時更新 → 重整頁面後購物車保留
- [x] 9.2 前台：進入 `/inquiry` → 增減數量、移除商品、填寫聯絡資訊 → 送出 → 顯示成功頁 → 購物車清空
- [x] 9.3 後台：登入 → 進入詢價訂單列表 → 點擊查看詳情 → 更新狀態 → 顯示成功提示
- [x] 9.4 確認資料庫 `orders` 與 `order_items` 正確寫入（order_number 格式正確、price 來自 DB 非前端）
- [x] 9.5 確認管理員信箱收到詢價通知 Email（或 SMTP 未設定時不拋錯）
