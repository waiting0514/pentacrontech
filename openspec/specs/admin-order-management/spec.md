## ADDED Requirements

### Requirement: 後台訂單列表 API
系統 SHALL 提供 `GET /api/admin/orders`，回傳分頁訂單列表，支援：
- `?status=` 篩選（pending / processing / completed / cancelled）
- `?search=` 關鍵字搜尋（`order_number` 或 `company_name`）
- 分頁（每頁 15 筆）
- 依 `created_at` DESC 排序

回應格式遵循系統 `ApiResponse` trait：
```json
{
  "success": true,
  "code": 200,
  "data": {
    "data": [...],
    "current_page": 1,
    "last_page": 5,
    "total": 72
  }
}
```

#### Scenario: 取得訂單列表
- **WHEN** 認證管理員 GET `/api/admin/orders`
- **THEN** SHALL 回傳分頁訂單列表，每筆含 `id`, `order_number`, `customer_name`, `company_name`, `status`, `created_at`

#### Scenario: 依狀態篩選
- **WHEN** GET `/api/admin/orders?status=pending`
- **THEN** 回傳資料 SHALL 只含 `status=pending` 的訂單

#### Scenario: 關鍵字搜尋
- **WHEN** GET `/api/admin/orders?search=ABC公司`
- **THEN** 回傳資料 SHALL 只含 `company_name` 或 `order_number` 包含該關鍵字的訂單

---

### Requirement: 後台訂單詳情 API
系統 SHALL 提供 `GET /api/admin/orders/{id}`，回傳單一訂單完整資料，包含 eager loaded `order_items`。

回應的 `order_items` 陣列每筆含：`product_id`, `product_name`, `price`, `quantity`, `subtotal`（price × quantity）

#### Scenario: 取得單一訂單詳情
- **WHEN** 認證管理員 GET `/api/admin/orders/{id}`
- **THEN** SHALL 回傳 Order 資料與其 `order_items` 陣列及總金額

#### Scenario: 訂單不存在
- **WHEN** GET `/api/admin/orders/9999`（ID 不存在）
- **THEN** SHALL 回傳 404

---

### Requirement: 後台更新訂單狀態 API
系統 SHALL 提供 `PATCH /api/admin/orders/{id}/status`，允許管理員更新訂單狀態。

請求 body：`{ "status": "processing" }`
驗證：`status` SHALL 為 `pending`、`processing`、`completed`、`cancelled` 其中之一

#### Scenario: 合法狀態更新
- **WHEN** PATCH `/api/admin/orders/{id}/status` 帶 `{ "status": "completed" }`
- **THEN** Order 的 `status` SHALL 更新為 `completed`
- **THEN** 回應 SHALL 包含更新後的 Order 資料

#### Scenario: 非法狀態值
- **WHEN** PATCH 帶 `{ "status": "unknown" }`
- **THEN** 系統 SHALL 回傳 422 validation error

---

### Requirement: Admin\OrderController
系統 SHALL 提供 `App\Http\Controllers\Api\Admin\OrderController`，實作上述三個端點，置於 `auth:sanctum` middleware 群組內。

#### Scenario: 未認證存取
- **WHEN** 未帶 Bearer token 存取任何 `/api/admin/orders` 端點
- **THEN** SHALL 回傳 401

---

### Requirement: 後台訂單列表頁 (OrderList.vue)
系統 SHALL 提供 `resources/js/admin/views/orders/index.vue`，功能包含：
- Element Plus `el-table` 顯示：訂單編號、公司名稱、客戶姓名、日期、狀態標籤
- 狀態標籤以不同顏色區分：`pending` 黃色、`processing` 藍色、`completed` 綠色、`cancelled` 灰色
- 頂部狀態篩選（`el-select`）與關鍵字搜尋（`el-input`）
- 分頁（`el-pagination`）
- 點擊列可進入 `/admin/orders/:id` 詳情頁
- 所有文字使用 `proxy.$t()` 取得 i18n 翻譯

#### Scenario: 載入訂單列表
- **WHEN** 管理員進入 `/admin/orders`
- **THEN** 頁面 SHALL 顯示訂單列表並支援分頁

#### Scenario: 篩選狀態
- **WHEN** 管理員選擇狀態篩選後
- **THEN** 表格 SHALL 只顯示對應狀態的訂單

---

### Requirement: 後台訂單詳情頁 (OrderDetail.vue)
系統 SHALL 提供 `resources/js/admin/views/orders/detail.vue`，分三個區塊：

**區塊 A：客戶資訊** — 顯示 `customer_name`、`customer_email`、`company_name`、`notes`

**區塊 B：狀態控制** — `el-select` 下拉選單顯示目前狀態，變更後呼叫 `PATCH /api/admin/orders/{id}/status`，成功後顯示 `ElMessage.success`

**區塊 C：商品列表** — `el-table` 顯示 `product_name`、`price`、`quantity`、小計；底部顯示總金額

所有文字使用 `proxy.$t()` 取得 i18n 翻譯

#### Scenario: 更新狀態
- **WHEN** 管理員在詳情頁變更狀態下拉選單並確認
- **THEN** 系統 SHALL 呼叫 PATCH API 並顯示成功提示

#### Scenario: 顯示商品總金額
- **WHEN** 管理員查看訂單詳情
- **THEN** 頁面 SHALL 顯示所有 order_items 的 price × quantity 加總

---

### Requirement: 後台路由與側邊欄整合
系統 SHALL 在後台 Vue Router 加入訂單相關路由，並在側邊欄顯示「詢價訂單」入口（使用 `order.view` permission）。

路由：
- `/admin/orders` → `OrderList.vue`（name: `Orders`）
- `/admin/orders/:id` → `OrderDetail.vue`（name: `OrderDetail`，hidden: true）

#### Scenario: 有 order.view 權限的管理員
- **WHEN** 具有 `order.view` 或 `super-admin` 角色的使用者登入後台
- **THEN** 側邊欄 SHALL 顯示「詢價訂單」入口

---

### Requirement: i18n 翻譯整合
系統 SHALL 在以下翻譯檔加入訂單相關字串：

`lang/zh-TW/admin.php` 與 `lang/en/admin.php` 新增：
- `admin.nav.orders` — 後台側邊欄入口名稱
- `admin.orders.title`, `admin.orders.order_number`, `admin.orders.company`, `admin.orders.customer`, `admin.orders.status`, `admin.orders.date`, `admin.orders.notes`, `admin.orders.items`, `admin.orders.subtotal`, `admin.orders.total`
- `admin.orders.status_pending`, `admin.orders.status_processing`, `admin.orders.status_completed`, `admin.orders.status_cancelled`
- `admin.orders.update_status`

`lang/zh-TW/frontend.php` 與 `lang/en/frontend.php` 新增：
- `frontend.inquiry.title`, `frontend.inquiry.add_to_cart`, `frontend.inquiry.cart_empty`, `frontend.inquiry.go_shop`, `frontend.inquiry.checkout`, `frontend.inquiry.submit`, `frontend.inquiry.success_title`, `frontend.inquiry.success_message`
- `frontend.nav.inquiry`（導覽列購物車連結文字）

#### Scenario: 後台顯示翻譯字串
- **WHEN** 後台 Vue 呼叫 `proxy.$t('admin.orders.title')`
- **THEN** SHALL 回傳對應語系的翻譯文字，不顯示 key 本身
