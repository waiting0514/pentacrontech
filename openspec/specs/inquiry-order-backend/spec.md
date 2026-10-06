## ADDED Requirements

### Requirement: orders 資料表
系統 SHALL 建立 `orders` 資料表，欄位如下：

| 欄位 | 類型 | 說明 |
|------|------|------|
| `id` | bigint PK | |
| `customer_id` | FK → users nullable | on delete set null；訪客訂單為 null |
| `order_number` | string UNIQUE | 格式 `INQ-YYYYMMDD-XXXX` |
| `customer_name` | string | 必填 |
| `customer_email` | string | 必填 |
| `company_name` | string nullable | |
| `notes` | text nullable | |
| `status` | string | 預設 `pending` |
| `created_at` / `updated_at` | timestamp | |

`status` 可選值：`pending`（待處理）、`processing`（處理中）、`completed`（已結案）、`cancelled`（取消）

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

### Requirement: order_items 資料表
系統 SHALL 建立 `order_items` 資料表，欄位如下：

| 欄位 | 類型 | 說明 |
|------|------|------|
| `id` | bigint PK | |
| `order_id` | FK → orders | cascade delete |
| `product_id` | bigint nullable | 商品被刪除後仍保留歷史紀錄 |
| `product_name` | string | 下單當下快照 |
| `price` | decimal(10,2) | 下單當下快照，從 DB 讀取 |
| `quantity` | unsigned int | |
| `created_at` / `updated_at` | timestamp | |

#### Scenario: 建立 order_items 資料表
- **WHEN** 執行 `artisan migrate`
- **THEN** `order_items` 資料表 SHALL 存在且 `order_id` 有外鍵約束

---

### Requirement: Order Model
系統 SHALL 提供 `App\Models\Order` Model，包含：
- `$fillable`: `order_number`, `customer_name`, `customer_email`, `company_name`, `notes`, `status`
- `hasMany` 關聯：`orderItems()`
- `creating` 事件中自動產生 `order_number`（格式 `INQ-YYYYMMDD-XXXX`，以 `DB::transaction + lockForUpdate` 避免重複）

#### Scenario: 自動產生訂單編號
- **WHEN** 建立新 Order 時未提供 `order_number`
- **THEN** 系統 SHALL 自動產生當日唯一的 `INQ-YYYYMMDD-XXXX` 編號

---

### Requirement: OrderItem Model
系統 SHALL 提供 `App\Models\OrderItem` Model，包含：
- `$fillable`: `order_id`, `product_id`, `product_name`, `price`, `quantity`
- `belongsTo` 關聯：`order()`

#### Scenario: OrderItem 關聯 Order
- **WHEN** 透過 `$orderItem->order` 存取
- **THEN** SHALL 回傳對應的 Order 物件

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

---

### Requirement: Email 通知管理員
系統 SHALL 在詢價送出成功後，寄送 Markdown Mailable（`InquirySubmittedMail`）至 `Project::first()->contact_email`。

- 郵件內容 SHALL 包含：訂單編號、客戶姓名、公司名稱、Email、備註、商品列表（名稱、數量）
- 若 SMTP 未設定或發送失敗，系統 SHALL 以 `try/catch` 靜默處理，**不影響**訂單寫庫與 redirect

#### Scenario: SMTP 正常時發信
- **WHEN** 詢價成功寫庫後且 SMTP 已設定
- **THEN** `contact_email` SHALL 收到詢價通知信

#### Scenario: SMTP 未設定時不報錯
- **WHEN** 詢價成功寫庫後且 SMTP 未設定
- **THEN** 系統 SHALL 靜默略過發信錯誤
- **THEN** 使用者 SHALL 仍被 redirect 至成功頁
