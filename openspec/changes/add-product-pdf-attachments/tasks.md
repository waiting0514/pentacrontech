## 1. 資料與設定

- [x] 1.1 新增 `config/uploads.php`（`product_attachment_max_kb` 預設 10240、`product_attachment_max_per_product` 預設 10，皆讀 env）並在 `.env.example` 加入對應鍵。驗證：`php artisan tinker` 讀取 `config('uploads.product_attachment_max_kb')` 為 10240；以 env 覆寫後值改變（測試涵蓋）。
- [x] 1.2 新增 migration 建立 `product_attachments`（`product_id` FK cascade、`title` json、`sort_order`、timestamps）與 `product_attachment_files`（`attachment_id` FK cascade、`locale`、`path`、`original_name`、`size`、`unique(attachment_id, locale)`）。驗證：`migrate` 與 `migrate:rollback` 皆成功。
- [x] 1.3 新增 `ProductAttachment`（`HasTranslations`、`files()` 關聯、`fileFor($locale)` 依「當前語系 → 預設語系 → 任一」解析）、`ProductAttachmentFile`（`deleted` 事件刪除實體檔、`url` 與 `human_size` accessor）與 `Product::attachments()`（`deleting` 時逐筆刪除附件檔案）。驗證：`ProductAttachmentTest` 的 `test_file_resolution_falls_back_to_default_then_any` 涵蓋檔案語系回退；標題回退由 `displayTitle()` 實作並於前台測試涵蓋。

## 2. 後端 API

- [x] 2.1 新增 `Api\ProductAttachmentController` 與 `ProductAttachmentResource`：列表、新增、更新（`POST`，可替換／新增語系檔案與改標題排序）、刪除附件、刪除單一語系檔案；驗證 PDF（`mimes` + `mimetypes` + 設定大小）、`title.<預設語系>` 必填、至少一個檔案、數量上限、不可刪最後一個檔案、隨機檔名儲存、原檔名與大小入庫；路由 scoped binding 並加 `api.` 名稱前綴；權限 `product.view`（列表）、`product.update`（異動）。驗證：隨 2.2 的 Feature test 通過。
- [x] 2.2 新增 `tests/Feature/ProductAttachmentTest.php`（`Storage::fake('public')`）：有效 PDF（zh-TW + en）、僅單語系、無檔案 422、無預設語系標題 422、非 PDF 與偽裝 `.pdf` 422、超過 10 MB 422 且訊息含上限、調整 config 上限後生效、超過數量上限 422、替換語系檔案後舊檔被刪、移除語系檔案、不可移除最後一個、刪附件後實體檔消失、刪產品後所有實體檔消失、隨機檔名（`../../evil.pdf`）、附件不屬於該產品 404、無權限 403、未登入 401。

## 3. Admin SPA

- [x] 3.1 在 `resources/js/services/productService.js` 新增附件方法（list/create/update/delete/deleteFile，multipart 以 `FormData`，更新同樣用 `POST`）。驗證：`npm run build` 無錯誤。
- [ ] 3.2 在 `views/product/index.vue` 產品編輯對話框新增「附件」區塊與新增／編輯子對話框（每個語系一個分頁：標題 + PDF 選擇，顯示已有檔案名稱／大小與「移除」、顯示「單檔上限 10 MB」提示、前端先檢查副檔名與大小、顯示後端 422 訊息、刪除確認）。驗證：本機以 `admin` 帳號手動走完新增（中英文各一份）、替換、移除單語系、刪除附件、上傳非 PDF 與超大檔，並截圖確認。 （程式已完成、`npm run build` 通過；尚缺瀏覽器實際操作驗證）

## 4. 前台

- [x] 4.1 `Frontend\ProductController@show` 預載 `attachments.files`；`products/show.blade.php` 新增「下載資料」區塊（標題、檔案大小、回退語系標籤、`download` 連結、無附件時不輸出），沿用 Industrial Blue 樣式，並在 `lang/zh-TW`、`lang/en` 新增文字。驗證：`tests/Feature/ProductAttachmentFrontendTest.php` 涵蓋當前語系有檔案、語系回退與標籤、標題回退、無附件不顯示區塊、排序；headless Chrome 於 1440 與 500px 截圖無溢出。

## 5. 文件與收尾

- [x] 5.1 更新 README：附件功能、`PRODUCT_ATTACHMENT_MAX_KB` 設定、部署時需確認 PHP `upload_max_filesize`／`post_max_size` 與 nginx `client_max_body_size` 不小於上限、`storage:link`。驗證：文件已含上述內容。
- [x] 5.2 執行完整測試與格式化。驗證：新增測試全數通過、`pint --test` 對新增檔案無違規、`openspec validate add-product-pdf-attachments` 通過。
