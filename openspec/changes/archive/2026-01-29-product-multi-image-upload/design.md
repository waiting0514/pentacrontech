## Context

產品目前使用 `image_url` 字串欄位。系統已有 `ImageUploadController` 處理富文本編輯器圖片上傳（簡單存檔，無處理）。需要新增專門的 `ImageService` 做圖片處理，以及 `product_images` 表支援多圖。

## Goals / Non-Goals

**Goals:**
- 產品支援多圖上傳，自動產出 main(1000px)/thumb(500px)/tiny(150px) 三種 JPG 尺寸
- 每張圖強制 1:1 正方形，使用 contain + 白色背景填充
- 支援主圖選擇（預設第一張為主圖）
- 前端拖曳或點擊上傳，即時預覽

**Non-Goals:**
- 不處理既有的富文本編輯器圖片上傳（ImageUploadController 維持不變）
- 不做圖片裁切 UI（前端不提供手動裁切框）
- 不做 CDN 整合

## Decisions

### 1. ImageService 設計
建立 `App\Services\ImageService`，方法 `processProductImage(UploadedFile $image, string $folderName = 'products'): array`。

使用 Intervention Image v3 API：
- `ImageManager::gd()` 建立管理器
- `->read($image)` 讀取上傳檔案
- `->contain(size, size)` 等比縮放並填入正方形
- `->pad(size, size, '#ffffff')` 白色背景填充
- `->toJpeg(quality: 80)` 轉為 JPG

檔名格式：`{date}_{uuid}.jpg`，儲存路徑 `uploads/{folder}/{size}/`。

**理由**：contain + pad 確保 1:1 不變形，三種尺寸分目錄管理清晰。JPG 相容性最佳。

### 2. 資料表設計
`product_images` 表：
- `id`, `product_id` (FK), `paths` (JSON: {main, thumb, tiny}), `is_primary` (boolean), `sort_order` (int), `timestamps`

用 JSON 欄位存三種尺寸路徑，避免三個獨立欄位。`is_primary` 標記主圖。

**理由**：JSON 欄位簡潔，一筆記錄代表一組圖片（三種尺寸同源）。

### 3. API 設計
- `POST /api/products/{product}/images` — 上傳圖片（支援多檔）
- `DELETE /api/products/{product}/images/{image}` — 刪除圖片（含檔案）
- `PUT /api/products/{product}/images/{image}/primary` — 設為主圖
- `PUT /api/products/{product}/images/reorder` — 重新排序

圖片上傳在產品建立後進行（先建產品，再上傳圖片）。

### 4. 前端元件
在 `ProductManager.vue` 表單中，移除 image_url input，改為圖片上傳區域：
- 點擊或拖曳上傳（使用 `<input type="file" multiple accept="image/*">`）
- 上傳後即時顯示 thumb 尺寸預覽
- 第一張自動設為主圖（綠色邊框標記）
- 點擊其他圖可設為主圖
- 每張圖有刪除按鈕

### 5. Migration 策略
由於尚未正式部署，直接修改 `create_products_table` migration 移除 `image_url` 欄位。新增 `create_product_images_table` migration。

## Risks / Trade-offs

- **需安裝 GD 擴充** → Docker/伺服器需確認 PHP GD 已啟用（通常預設有）
- **圖片處理耗時** → 三種尺寸同步處理，大圖可能需 1-2 秒。初版可接受，未來可改用 Queue
- **儲存空間** → 每張圖產出三份，但 JPG 壓縮率合理，可接受
- **移除 image_url 為 Breaking** → 由於未部署，直接修改 migration 無影響
