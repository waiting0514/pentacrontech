## 1. 資料庫

- [x] 1.1 建立 `create_product_categories_table` migration（欄位：id, project_id, name JSON, slug, sort_order, is_active, timestamps）
- [x] 1.2 建立 migration 為 products 表新增 nullable `category_id` 外鍵（onDelete set null）

## 2. Model

- [x] 2.1 建立 `ProductCategory` Model（fillable, casts, hasMany products 關聯）
- [x] 2.2 修改 `Product` Model 新增 `category_id` 到 fillable，新增 belongsTo category 關聯

## 3. API

- [x] 3.1 建立 `ProductCategoryController`（index, store, update, destroy），參照 NewsCategoryController
- [x] 3.2 註冊 `/api/product-categories` 路由
- [x] 3.3 修改 ProductController index 支援 `category_id` query parameter 篩選

## 4. 前端後台

- [x] 4.1 建立 `ProductCategoryManager.vue` 元件，參照 NewsCategoryManager.vue
- [x] 4.2 修改 `ProductManager.vue` 新增分類下拉選單（建立/編輯表單）
- [x] 4.3 修改 `ProductManager.vue` 新增分類篩選功能（列表頂部）
- [x] 4.4 在 Dashboard 或路由中加入產品分類管理入口

## 5. 前台

- [x] 5.1 前台產品列表頁新增分類篩選（tab 或下拉），支援 `?category=slug` URL 參數

## 6. Seeder

- [x] 6.1 建立 ProductCategorySeeder 或更新 DatabaseSeeder 加入產品分類測試資料
