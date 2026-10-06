## MODIFIED Requirements

### Requirement: 前台詢價結帳頁
系統 SHALL 提供 `/inquiry` 路由，渲染詢價結帳頁，包含：
1. 購物車商品列表（`x-for` 渲染，支援數量增減與移除）
2. 聯絡資訊表單（姓名、Email、公司名稱、電話、地址、備註）
3. 若客戶已登入，系統 SHALL 從 `customer_profiles` 讀取資料預填表單欄位（company_name、phone、address），欄位仍可手動修改
4. 若客戶未登入，所有欄位 SHALL 維持空白，由訪客手動填寫
5. 以標準 HTML POST 送出

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

#### Scenario: 已登入客戶進入詢價頁
- **WHEN** 已登入客戶進入 `/inquiry`
- **THEN** 表單的 company_name、phone、address 欄位 SHALL 預填客戶 profile 資料
- **THEN** 客戶仍可修改預填內容後送出

#### Scenario: 未登入訪客進入詢價頁
- **WHEN** 未登入訪客進入 `/inquiry`
- **THEN** 表單欄位 SHALL 維持空白（訪客自行填寫）
- **THEN** 訪客 SHALL 可正常完成詢價流程
