## Why

本專案為 B2B 多租戶 CMS，產品頁目前只有「聯絡我們」靜態連結，無法讓訪客收集多件商品一次送出詢價單。需要建立前台詢價購物車（Blade + Alpine.js）與對應的後台訂單管理（Vue 3 SPA），讓業務流程從訪客詢價到管理員跟進形成完整閉環。

## What Changes

- **新增** Alpine.js `$persist` 全域購物車 Store，支援 LocalStorage 跨頁持久化
- **新增** 前台「加入詢價車」按鈕（產品詳情頁），點擊後顯示 Toast 回饋
- **新增** 前台導覽列購物車徽章（顯示商品總件數）
- **新增** 前台詢價結帳頁 `/inquiry`（購物車列表 + 聯絡資訊表單，POST 送出）
- **新增** 前台詢價送出成功頁
- **新增** `orders` 資料表（詢價主單，含唯一編號 `INQ-YYYYMMDD-XXX`、客戶資訊、狀態）
- **新增** `order_items` 資料表（詢價明細，快照商品名稱/價格）
- **新增** `InquiryController`（前台，接收 POST、寫庫、發信）
- **新增** `Admin\OrderController` API（列表/詳情/狀態更新）
- **新增** 後台 Vue 3 訂單列表頁 `OrderList.vue`
- **新增** 後台 Vue 3 訂單詳情頁 `OrderDetail.vue`
- **更新** 後台路由與側邊欄加入「詢價訂單」入口
- **更新** i18n 翻譯檔（`lang/zh-TW/admin.php`、`lang/en/admin.php`、`lang/zh-TW/frontend.php`、`lang/en/frontend.php`）加入訂單相關字串

## Capabilities

### New Capabilities

- `frontend-inquiry-cart`: 前台 Alpine.js 全域購物車 Store（$persist）、產品頁加入詢價車按鈕、導覽列徽章、結帳頁（購物車列表 + 聯絡表單）、送出後清空 LocalStorage
- `inquiry-order-backend`: `orders` 與 `order_items` 資料表、Model 關聯、`InquiryController` 前台送出邏輯（驗證、寫庫、寄信）
- `admin-order-management`: `Admin\OrderController` REST API（列表分頁篩選、詳情 eager load、狀態 PATCH）、Vue 3 `OrderList.vue` 與 `OrderDetail.vue`、後台路由與 i18n 整合

### Modified Capabilities

- `product-detail`: 產品詳情頁新增「加入詢價車」按鈕（原有「立即詢價」連結保留），屬於 UI 行為擴充，不影響資料規格

## Impact

- **資料庫**: 新增 2 張 migration（`orders`、`order_items`）
- **後端**: 新增 2 個 Controller、2 個 Model、2 條 route 群組（web inquiry + api admin/orders）
- **前端 Blade**: 修改 `layouts/app.blade.php`（加入 Alpine persist CDN + cart store script）、`partials/header.blade.php`（加入購物車徽章）、`products/show.blade.php`（加入詢價車按鈕）；新增 `inquiry/index.blade.php`、`inquiry/success.blade.php`
- **前端 Vue**: 新增 `resources/js/admin/views/orders/index.vue`、`detail.vue`；修改 `router/index.js`、側邊欄 nav
- **i18n**: 修改 4 個翻譯檔
- **Alpine.js**: 需額外載入 `@alpinejs/persist` CDN 插件
- **Email**: 沿用現有混合式 SMTP 設定（`Mail` facade），新增 Mailable 或 inline mail
