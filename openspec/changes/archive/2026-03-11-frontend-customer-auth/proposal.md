## Why

訪客詢價時須手動填寫公司名稱、地址、電話等資料，重複作業且容易出錯。透過前台客戶帳號系統，讓客戶登入後自動帶入個人資料、查閱歷史詢價紀錄，提升詢價體驗並強化客戶關係管理。未登入訪客維持現有訪客詢價流程不受影響。

## What Changes

- 新增前台客戶註冊、登入、登出功能（共用 `users` 表，分配 `customer` role）
- 新增 `customer_profiles` 資料表，一對一關聯 `users`，存放 company_name、phone、address
- 客戶可在會員中心修改個人資料（姓名、公司名、電話、地址）
- 詢價結帳頁：已登入客戶自動帶入個人資料預填表單（欄位仍可手動修改）；未登入維持訪客手動填寫
- 訂單建立時，已登入客戶的訂單關聯 `customer_id`（nullable FK → users）；訪客訂單為 null
- 新增前台「我的訂單」頁面，客戶可查看自己的歷史詢價訂單列表與詳情

## Capabilities

### New Capabilities
- `frontend-customer-auth`: 前台客戶帳號系統，涵蓋註冊、登入、登出、會員資料修改；共用 users 表搭配 customer role；customer_profiles 存放額外欄位
- `customer-order-history`: 前台客戶歷史訂單頁，已登入客戶可查看自己的詢價訂單列表與詳情

### Modified Capabilities
- `frontend-inquiry-cart`: 詢價結帳頁在已登入時自動帶入 customer_profile 資料預填；未登入維持現有訪客流程
- `inquiry-order-backend`: orders 資料表新增 `customer_id`（nullable FK → users）；InquiryController::store() 在已登入時關聯帳號

## Impact

- **新資料表**：`customer_profiles`（user_id FK、company_name、phone、address）
- **Migration**：orders 加欄 `customer_id` nullable FK → users
- **新路由（web）**：`/register`、`/login`、`/logout`、`/account`（會員資料）、`/account/orders`（歷史訂單）
- **Auth**：前台使用 `web` guard（Session）+ Spatie `customer` role；後台不受影響
- **Guard 隔離**：後台路由加 `role:super-admin|admin` middleware，前台客戶無法存取後台
- **i18n**：新增 zh-TW / en 翻譯（auth、account 區塊）
