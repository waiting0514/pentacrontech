## Why

產品目前使用單一 `image_url` 文字欄位手動輸入圖片網址，無法上傳多張圖片，也沒有圖片處理（壓縮、裁切、格式轉換）。需改為多圖上傳機制，支援主圖選擇，並透過 Intervention Image v3 自動產出三種尺寸的 WebP 圖片。

## What Changes

- 安裝 `intervention/image-laravel` v3 套件
- 新增 `ImageService` 處理圖片上傳與轉換（WebP, 1:1, 三種尺寸: main/thumb/tiny）
- 新增 `product_images` 資料表，支援一對多圖片關聯及主圖標記
- **BREAKING**: 移除 `products.image_url` 欄位，改用 `product_images` 關聯
- 新增產品圖片上傳 API（上傳、刪除、設定主圖、排序）
- 前端 `ProductManager.vue` 的圖片 URL input 改為多圖上傳元件，支援拖曳上傳、主圖選擇
- 前台產品頁面改用新的圖片關聯

## Capabilities

### New Capabilities
- `image-processing`: ImageService 圖片處理服務（WebP 轉換、三種尺寸產出、1:1 裁切）
- `product-images`: 產品多圖管理（上傳、刪除、主圖選擇、排序）

### Modified Capabilities
- `product`: 移除 image_url 欄位，改用 product_images 關聯取得圖片

## Impact

- 依賴套件：新增 `intervention/image-laravel` v3
- 資料庫：新增 `product_images` 表，修改 `products` 表移除 `image_url`
- 儲存：使用 `storage/app/public/uploads/products/{size}/` 結構
- API：新增產品圖片上傳相關端點
- 前端：ProductManager.vue 大幅修改圖片區域
- 前台：products blade views 改用新圖片關聯
