## 1. 依賴安裝

- [x] 1.1 安裝 `intervention/image-laravel` v3 套件

## 2. 資料庫

- [x] 2.1 建立 `create_product_images_table` migration（product_id FK, paths JSON, is_primary boolean, sort_order int）
- [x] 2.2 修改 `create_products_table` migration 移除 `image_url` 欄位

## 3. ImageService

- [x] 3.1 建立 `App\Services\ImageService`，實作 `processProductImage` 方法（JPG, 1:1, contain+pad, 三種尺寸）
- [x] 3.2 實作 `deleteProductImage` 方法（刪除三種尺寸檔案）

## 4. Model

- [x] 4.1 建立 `ProductImage` Model（fillable, casts, belongsTo product）
- [x] 4.2 修改 `Product` Model：移除 image_url 相關，新增 images hasMany 關聯及 primaryImage 方法

## 5. API

- [x] 5.1 建立 `ProductImageController`（upload, destroy, setPrimary, reorder）
- [x] 5.2 註冊產品圖片相關路由
- [x] 5.3 修改 ProductController store/update 移除 image_url 驗證

## 6. 前端後台

- [x] 6.1 修改 `ProductManager.vue`：移除 image_url input，新增多圖上傳區域（點擊/拖曳上傳、縮圖預覽、主圖選擇、刪除按鈕）
- [x] 6.2 產品列表表格改用 primaryImage thumb 顯示

## 7. 前台

- [x] 7.1 修改前台 products blade views，改用 product.images 關聯顯示圖片
