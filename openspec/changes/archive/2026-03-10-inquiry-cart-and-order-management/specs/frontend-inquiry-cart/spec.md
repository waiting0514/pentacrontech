## ADDED Requirements

### Requirement: 全域購物車 Store
系統 SHALL 在前台 `layouts/app.blade.php` 以 `Alpine.store('cart', ...)` 建立全域購物車，並以 `$persist` 將 `items` 陣列持久化至 LocalStorage，重整頁面後購物車內容 SHALL 保留。

購物車 item 資料結構：
```json
{ "product_id": 1, "name": "商品名稱", "price": 1000, "quantity": 1, "image": "url" }
```

Store 方法：
- `add(product, qty)` — 若商品已存在則累加數量，否則 push 新 item
- `remove(index)` — 移除指定 index 的 item
- `updateQuantity(index, qty)` — 更新指定 index 的數量，qty < 1 時視同移除
- `totalCount()` — 回傳所有 items 的 quantity 總和
- `clear()` — 清空 items 陣列

#### Scenario: 加入新商品
- **WHEN** 呼叫 `$store.cart.add(product)`
- **THEN** item SHALL 被加入 `items` 陣列
- **THEN** LocalStorage 中的購物車資料 SHALL 同步更新

#### Scenario: 加入重複商品
- **WHEN** 呼叫 `$store.cart.add(product)` 且 `items` 中已存在相同 `product_id`
- **THEN** 該 item 的 `quantity` SHALL 增加，不新增重複項目

#### Scenario: 重整頁面後持久化
- **WHEN** 購物車有商品後重整頁面
- **THEN** `$store.cart.items` SHALL 從 LocalStorage 還原，內容不遺失

---

### Requirement: Alpine Persist 插件載入
系統 SHALL 在 Alpine core CDN 之前載入 `@alpinejs/persist` CDN 插件，確保 `$persist` 可正常使用。

#### Scenario: 插件載入順序正確
- **WHEN** 頁面 HTML 渲染完成
- **THEN** `@alpinejs/persist` script 標籤 SHALL 出現在 Alpine core script 標籤之前

---

### Requirement: 導覽列購物車徽章
系統 SHALL 在前台 header 顯示購物車圖示，並以徽章數字即時反映 `$store.cart.totalCount()`。

#### Scenario: 購物車有商品
- **WHEN** `totalCount()` > 0
- **THEN** 導覽列 SHALL 顯示購物車圖示與數字徽章

#### Scenario: 購物車為空
- **WHEN** `totalCount()` === 0
- **THEN** 徽章 SHALL 隱藏（不顯示 0）

---

### Requirement: 產品詳情頁加入詢價車按鈕
系統 SHALL 在產品詳情頁（`products/show.blade.php`）提供「加入詢價車」按鈕，點擊後呼叫 `$store.cart.add()`，並顯示 Toast 成功提示。

#### Scenario: 點擊加入詢價車
- **WHEN** 使用者點擊「加入詢價車」按鈕
- **THEN** 商品 SHALL 加入 `$store.cart.items`
- **THEN** 頁面 SHALL 顯示 Toast 提示（如「已加入詢價車」）持續約 2 秒

#### Scenario: is_inquiry_only 產品
- **WHEN** 產品 `is_inquiry_only` 為 true
- **THEN** 「加入詢價車」按鈕 SHALL 顯示（可詢價）
- **THEN** 商品價格顯示為「歡迎詢價」，不顯示具體金額

---

### Requirement: 前台詢價結帳頁
系統 SHALL 提供 `/inquiry` 路由，渲染詢價結帳頁，包含：
1. 購物車商品列表（`x-for` 渲染，支援數量增減與移除）
2. 聯絡資訊表單（姓名、Email、公司名稱、備註）
3. 以標準 HTML POST 送出

#### Scenario: 購物車為空時進入詢價頁
- **WHEN** 使用者進入 `/inquiry` 且購物車無商品
- **THEN** 頁面 SHALL 顯示空車提示與「去選購」連結

#### Scenario: 增減商品數量
- **WHEN** 使用者點擊數量 +/- 按鈕
- **THEN** `$store.cart.updateQuantity()` SHALL 被呼叫
- **THEN** 小計金額 SHALL 即時更新

#### Scenario: 移除商品
- **WHEN** 使用者點擊移除按鈕
- **THEN** `$store.cart.remove()` SHALL 被呼叫
- **THEN** 該商品 SHALL 從列表消失

#### Scenario: 表單送出
- **WHEN** 使用者填寫聯絡資訊並點擊送出
- **THEN** 隱藏欄位 `cart_items` SHALL 包含 `JSON.stringify($store.cart.items)` 的值
- **THEN** 表單 SHALL 以 POST 方式送出至 `/inquiry`

---

### Requirement: 詢價送出成功頁
系統 SHALL 在詢價成功後顯示成功頁面，並清空 Alpine 購物車。

#### Scenario: 送出成功後清空購物車
- **WHEN** `InquiryController@store` 處理成功並 redirect 至成功頁
- **THEN** 成功頁 SHALL 執行 JS 呼叫 `$store.cart.clear()` 清空 LocalStorage 購物車
