## Context

系統已有 `NewsCategory` 分類功能，包含 Model、API Controller、前端管理元件。產品（Product）目前無分類，需要新增相同模式的分類機制。

現有相關結構：
- `news_categories` 表：`id`, `project_id`, `name` (JSON 多語系), `slug`, `sort_order`, `is_active`
- `NewsCategoryController`：標準 CRUD API
- `NewsCategoryManager.vue`：前端分類管理介面
- `ProductManager.vue`：現有產品管理介面，需整合分類選擇

## Goals / Non-Goals

**Goals:**
- 產品可歸屬於一個分類
- 後台可管理產品分類（CRUD）
- 後台產品管理可按分類篩選
- 前台產品列表可按分類篩選
- 結構與 NewsCategory 保持一致

**Non-Goals:**
- 不做多層級（巢狀）分類
- 不做產品多分類（多對多）
- 不做分類圖片/icon

## Decisions

### 1. 資料表結構：複製 news_categories 模式
`product_categories` 表欄位與 `news_categories` 相同：`project_id`, `name` (JSON), `slug`, `sort_order`, `is_active`。

產品表新增 `category_id` nullable 外鍵，`onDelete('set null')`，允許產品暫時無分類。

**理由**：與現有模式一致，降低維護成本。nullable 避免刪除分類時連帶刪除產品。

### 2. API 路由：RESTful 標準
新增 `/api/product-categories` 路由，對應 `ProductCategoryController`，提供 index/store/update/destroy。

產品 API 的 index 端點新增 `category_id` query parameter 支援篩選。

### 3. 前端元件：複製 NewsCategoryManager 模式
新增 `ProductCategoryManager.vue`，結構參照 `NewsCategoryManager.vue`。

`ProductManager.vue` 新增分類下拉選單（建立/編輯時）及分類篩選（列表頂部）。

### 4. 前台整合
產品列表頁新增分類 tab 或篩選按鈕，URL 支援 `?category=slug` 參數。

## Risks / Trade-offs

- **既有產品無分類** → Migration 後 `category_id` 為 null，前台需處理「全部」或「未分類」的顯示邏輯
- **與 NewsCategory 重複程式碼** → 可接受，兩者獨立演進比共用抽象更務實
