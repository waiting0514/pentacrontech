## MODIFIED Requirements

### Requirement: orders 資料表
系統 SHALL 在 `orders` 資料表新增 `customer_id` 欄位：

| 欄位 | 類型 | 說明 |
|------|------|------|
| `customer_id` | FK → users nullable | on delete set null；訪客訂單為 null |

其餘欄位維持不變（`order_number`、`customer_name`、`customer_email`、`company_name`、`notes`、`status`）。

#### Scenario: 建立 orders 資料表
- **WHEN** 執行 `artisan migrate`
- **THEN** `orders` 資料表 SHALL 存在且 `order_number` 欄位有 UNIQUE constraint

#### Scenario: customer_id nullable
- **WHEN** 訪客（未登入）送出詢價
- **THEN** 訂單的 `customer_id` SHALL 為 null

#### Scenario: 已登入客戶送出詢價
- **WHEN** 已登入客戶送出詢價
- **THEN** 訂單的 `customer_id` SHALL 等於 `auth()->id()`

---

### Requirement: InquiryController 接收詢價
系統 SHALL 提供 `App\Http\Controllers\Frontend\InquiryController`，處理：
- `GET /inquiry` → 渲染結帳頁，已登入時傳入 customer profile 資料供 Blade 預填
- `POST /inquiry` → 接收並驗證、寫庫、發信、redirect

POST 驗證規則：
- `customer_name`: required, string, max:255
- `customer_email`: required, email, max:255
- `company_name`: nullable, string, max:255
- `customer_phone`: nullable, string, max:50
- `customer_address`: nullable, string, max:500
- `notes`: nullable, string, max:2000
- `cart_items`: required, JSON string（至少含一件商品）

寫庫流程：
1. 解析 `cart_items` JSON
2. 對每件商品以 `product_id` 從 DB 重新讀取 `name` 與 `price`（**忽略前端傳來的價格**，防止偽造）
3. 若 `product_id` 不存在於 DB，以前端傳來的 `name` 和 `price: 0` 寫入（不拋錯）
4. 以 `DB::transaction` 建立 Order 與 OrderItems
5. Order SHALL 包含 `customer_id`（已登入時為 `auth()->id()`，未登入時為 null）

#### Scenario: 正常送出詢價
- **WHEN** POST `/inquiry` 帶有合法欄位與至少一件商品
- **THEN** 系統 SHALL 建立一筆 Order 與對應 OrderItems
- **THEN** 系統 SHALL redirect 至 `/inquiry/success`

#### Scenario: 購物車為空
- **WHEN** POST `/inquiry` 的 `cart_items` 為空陣列
- **THEN** 系統 SHALL 回傳 validation error，不寫入資料庫

#### Scenario: 商品已被刪除
- **WHEN** `cart_items` 中某 `product_id` 在 DB 不存在
- **THEN** OrderItem SHALL 以前端傳來的 `name` 與 `price: 0` 寫入
- **THEN** 訂單 SHALL 仍正常建立

#### Scenario: 已登入客戶送出
- **WHEN** 已登入客戶 POST `/inquiry`
- **THEN** Order.`customer_id` SHALL 等於該客戶的 `users.id`

#### Scenario: 訪客送出
- **WHEN** 未登入訪客 POST `/inquiry`
- **THEN** Order.`customer_id` SHALL 為 null
- **THEN** 訂單 SHALL 正常建立
