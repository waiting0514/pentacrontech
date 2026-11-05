## Why

客戶希望在產品詳情頁提供型錄、規格書等 PDF 供訪客下載，且中文與英文版本要分開提供。目前產品只有圖片與文字欄位，沒有任何附件機制。

## What Changes

- 後台產品編輯頁新增「附件」區塊：可為每個產品新增、編輯、刪除多份 PDF 附件。
- 每份附件有各語系的標題，以及各語系各自上傳的 PDF（例如 `zh-TW` 一份、`en` 一份）；至少要有一個語系的檔案。
- 單檔大小上限預設 **10 MB**，每個產品附件數上限預設 10 份；兩者做成設定值（`.env` / config），之後客戶有需求可直接調整。
- 檔案存放在 `public` disk，前台公開直接下載；只接受 PDF（驗證內容類型而非只看副檔名），檔名以隨機名稱儲存，原檔名另存供下載顯示。
- 前台產品詳情頁新增「下載資料」區塊：依當前語系顯示附件，該語系沒有檔案時回退預設語系的檔案，並標示檔案語系與大小。
- 刪除附件、單一語系檔案或產品時，同步刪除實體檔案。
- 新增 `product_attachments` 與 `product_attachment_files` 資料表與對應 API；沿用既有權限 `product.view` / `product.update`，不新增權限。

## Capabilities

### New Capabilities
- `product-attachments`: 產品 PDF 附件的後台管理（各語系標題與檔案、大小／數量限制、檔案清理）與前台公開下載（語系回退）。

### Modified Capabilities
<!-- 無：既有 product、product-images 的需求不變 -->

## Impact

- 後端：新增 `ProductAttachment`、`ProductAttachmentFile` model 與 migration、`config/uploads.php`、`Api\ProductAttachmentController`、`ProductAttachmentResource`、Product 的 `attachments()` 關聯與刪除時的檔案清理、前台 `ProductController@show` 載入附件。
- 前端（Admin SPA）：產品編輯對話框新增附件區塊、`productService` 新增附件方法。
- 前端（Blade）：`products/show.blade.php` 新增下載區、`lang` 新增文字。
- 部署：PHP `upload_max_filesize` / `post_max_size` 與 nginx `client_max_body_size` 需不小於上限（Sail 預設 100M，正式環境需確認）。
- 儲存：沿用 `storage/app/public`（需 `storage:link`）。
- 測試：後台 API（驗證、權限、清理）與前台顯示（語系回退）。
