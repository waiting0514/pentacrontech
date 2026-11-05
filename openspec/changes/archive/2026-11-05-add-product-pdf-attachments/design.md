# Design

## Context

產品圖片是獨立的 `ProductImage` 紀錄，透過 `ProductImageController`（`product.update` 權限）上傳到 `public` disk，資料庫只存相對路徑。Banner 的多語系圖片使用「語系 → 路徑」並在缺語系時回退預設語系。動機與範圍見 proposal.md。

已觀察到的現況：刪除產品時資料表以外鍵 cascade 刪除圖片列，**不會**觸發 model 事件，所以圖片實體檔目前不會被清除。附件不沿用這個缺口，需自行處理（圖片的既有缺口不在本次範圍）。

## Goals / Non-Goals

**Goals:**
- 每個產品可有多份 PDF 附件，標題與檔案依語系分開，公開下載。
- 上限（大小、數量）可設定；刪除時不留下孤兒檔案。

**Non-Goals:**
- 私有或需登入／填表才能下載（本次公開，之後若要再加）。
- 下載次數統計、PDF 預覽、縮圖、全文檢索。
- 圖片以外的檔案類型（Word、Excel 等）。
- 修補產品圖片刪除時不清檔的既有問題。
- 新聞、知識庫的附件（先做產品專用，不做多型關聯）。

## Decisions

1. **兩張資料表：`product_attachments` + `product_attachment_files`。**
   `product_attachments`：`id`、`product_id`（FK，cascade）、`title` json（多語系，沿用 `HasTranslations`）、`sort_order`、timestamps。
   `product_attachment_files`：`id`、`attachment_id`（FK，cascade）、`locale`、`path`、`original_name`、`size`（bytes）、timestamps，`unique(attachment_id, locale)`。
   原因：每個語系的檔案有路徑、原檔名、大小三項資料，獨立表比 JSON 欄位好驗證、好查詢，並由唯一索引保證「同語系至多一檔」。
   替代方案：附件表內放 `files` JSON（仿 Banner）→ 要自己維護結構與唯一性，捨棄。

2. **語系：** 檔案的 `locale` 取自後台語系清單（`languages` 表啟用中的語系），不寫死 zh/en；預設語系用 `Language::getDefault()`。前台解析檔案：當前語系 → 預設語系 → 附件中任一語系（避免預設語系檔被移除後附件失效）。標題同樣回退預設語系。

3. **設定：** 新增 `config/uploads.php`：`product_attachment_max_kb`（`env('PRODUCT_ATTACHMENT_MAX_KB', 10240)`）與 `product_attachment_max_per_product`（`env(..., 10)`），並寫入 `.env.example`。驗證規則與錯誤訊息的上限數字由設定帶入，避免寫死在字串中。

4. **驗證：** `file`、`mimes:pdf`、`mimetypes:application/pdf`、`max:<設定>`。`mimetypes` 由伺服器以內容偵測，防止改副檔名。數量上限在 Controller 以計數檢查（超過回 422）。

5. **儲存：** `Storage::disk('public')` 的 `product-attachments/{product_id}/` 下，以 `hashName()` 產生隨機檔名，不使用使用者檔名；原檔名存 `original_name` 供顯示與 `download` 屬性。公開下載直接用 `Storage::url()`／`asset('storage/...')`，不經 PHP。

6. **API（沿用 `ProductImageController` 風格，掛在產品下）：**
   - `GET /api/products/{product}/attachments`（`product.view`）
   - `POST /api/products/{product}/attachments`（`product.update`；multipart：`title[zh-TW]`、`title[en]`、`files[zh-TW]`、`files[en]`、`sort_order`）
   - `POST /api/products/{product}/attachments/{attachment}`，搭配 `_method=PUT` 更新標題、排序、替換或新增某語系檔案（PHP 對 multipart 的 PUT 不會解析，與 Banner 的註記一致）
   - `DELETE /api/products/{product}/attachments/{attachment}`
   - `DELETE /api/products/{product}/attachments/{attachment}/files/{locale}`（不可刪最後一個檔案）
   路由使用 scoped binding，附件不屬於該產品時回 404。路由名稱加 `api.` 前綴，避免與前台路由名稱衝突（影片功能時踩過）。

7. **檔案清理：** 以 model 事件集中處理：`ProductAttachmentFile` 的 `deleted` 事件刪除實體檔；`Product` 的 `deleting` 事件先逐筆刪除附件檔案紀錄（觸發檔案刪除），再由外鍵 cascade 清資料列。替換檔案時先存新檔、更新紀錄，再刪舊檔，失敗時不遺失舊檔。

8. **後台 UI：** 產品編輯對話框（`views/product/index.vue`）圖片區下方新增「附件」區塊，只在產品已儲存後顯示（與圖片上傳相同）。清單顯示各附件的標題與各語系檔案徽章；新增／編輯使用子對話框，每個語系一個分頁，各含「標題」與「選擇 PDF」。顯示上限提示（10 MB）。

9. **前台：** `Frontend\ProductController@show` 預載 `attachments.files`；`products/show.blade.php` 新增「下載資料」區塊（Industrial Blue 樣式），每筆顯示標題、檔案大小、語系標籤（僅在回退到非當前語系時顯示）與下載連結（`download` 屬性帶原檔名）。沒有附件時整個區塊不輸出。

## Risks / Trade-offs

- [PHP／nginx 上傳上限小於 10 MB 導致上傳靜默失敗] → 部署文件要求 `upload_max_filesize`、`post_max_size`、`client_max_body_size` 不小於設定值；後端對 `UPLOAD_ERR_INI_SIZE` 給出明確錯誤；Sail 預設 100M 已足夠。
- [公開網址可被猜測或轉載] → 已選擇公開，檔名隨機但不視為機密；若日後要限制下載，改用私有 disk 加下載路由（資料表設計不需變動）。
- [刪除時實體檔刪除失敗留下孤兒] → 以事件刪除並記錄 log；不因檔案刪除失敗阻擋資料庫刪除。
- [使用者上傳含惡意內容的 PDF] → 僅提供下載，不在站內解析或內嵌；內容類型伺服器端偵測。
- [`storage:link` 未建立導致連結 404] → 與產品圖片相同前提，沿用既有部署步驟。

## Migration Plan

1. 部署並執行 `php artisan migrate`（只新增兩張表，可 `migrate:rollback`）。
2. 確認 `storage:link` 已建立，以及 PHP／nginx 上傳上限。
3. 回滾：`migrate:rollback` 移除資料表；已上傳的檔案留在 `storage/app/public/product-attachments/`，可手動刪除。

## Open Questions

- 是否需要在上傳後記錄上傳者？目前不需要，不影響資料模型的擴充。
