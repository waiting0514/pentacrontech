## ADDED Requirements

### Requirement: 前台歷史訂單列表頁
系統 SHALL 提供 `GET /account/orders` 路由（需 `auth` middleware），顯示已登入客戶自己的詢價訂單列表。

列表 SHALL 顯示：`order_number`、`status`、`created_at`、訂單商品件數、點擊進入詳情。

查詢 SHALL 以 `customer_id = auth()->id()` 篩選，客戶 SHALL 無法看到其他客戶的訂單。

#### Scenario: 已登入客戶查看訂單列表
- **WHEN** 已登入客戶訪問 `GET /account/orders`
- **THEN** 頁面 SHALL 僅顯示該客戶 `customer_id` 對應的訂單
- **THEN** 訂單 SHALL 以建立時間倒序排列

#### Scenario: 尚無訂單
- **WHEN** 已登入客戶尚未送出任何詢價
- **THEN** 頁面 SHALL 顯示空狀態提示，並提供連結至產品列表

#### Scenario: 未登入存取
- **WHEN** 未登入使用者訪問 `GET /account/orders`
- **THEN** 系統 SHALL redirect 至 `/login`

---

### Requirement: 前台歷史訂單詳情頁
系統 SHALL 提供 `GET /account/orders/{order}` 路由（需 `auth` middleware），顯示單一訂單詳情。

詳情 SHALL 顯示：`order_number`、`status`、`created_at`、客戶聯絡資訊、商品列表（名稱、數量、單價、小計）、訂單總金額。

系統 SHALL 驗證 `order.customer_id === auth()->id()`，不符合 SHALL 回傳 403。

#### Scenario: 已登入客戶查看自己的訂單
- **WHEN** 已登入客戶訪問 `GET /account/orders/{order}` 且該訂單屬於自己
- **THEN** 頁面 SHALL 顯示完整訂單資訊

#### Scenario: 嘗試查看他人訂單
- **WHEN** 已登入客戶訪問 `GET /account/orders/{order}` 且該訂單屬於其他客戶
- **THEN** 系統 SHALL 回傳 403 Forbidden

#### Scenario: 訂單不存在
- **WHEN** `{order}` 對應的訂單在資料庫不存在
- **THEN** 系統 SHALL 回傳 404
