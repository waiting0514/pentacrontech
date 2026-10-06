## Why

產品目前沒有分類功能，所有產品都是平面列表。需要加上分類以便前台展示和後台管理時能按類別篩選產品，與最新消息分類功能一致。

## What Changes

- 新增 `product_categories` 資料表，結構參照 `news_categories`（多語系名稱、slug、排序、啟用狀態）
- 產品表新增 `category_id` 外鍵關聯到產品分類
- 新增 `ProductCategory` Model 及 API Controller（CRUD）
- 新增 `ProductCategoryManager.vue` 前端管理元件，參照 `NewsCategoryManager.vue`
- `ProductManager.vue` 新增分類選擇欄位及按分類篩選功能
- 前台產品列表頁支援按分類篩選

## Capabilities

### New Capabilities
- `product-categories`: 產品分類的 CRUD 管理、與產品的關聯、前後台篩選

### Modified Capabilities
- `product`: 產品新增 `category_id` 欄位，支援按分類篩選

## Impact

- 資料庫：新增 `product_categories` 表，`products` 表新增 `category_id` 欄位
- API：新增 `/api/product-categories` 路由，修改產品 API 支援分類篩選
- 前端：新增分類管理元件，修改產品管理元件
- 前台：產品列表頁新增分類篩選
