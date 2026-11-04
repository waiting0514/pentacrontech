## 1. 後端資料與解析

- [x] 1.1 新增 `videos` migration（`id`、`title`/`description`/`meta_title`/`meta_description`/`meta_keywords` 為 json、`slug` varchar unique、`youtube_id` varchar(11) unique、`sort_order` int default 0、`is_active` bool default true、timestamps）與 `Video` model（`HasTranslations`、`published()`／active scope、`url` accessor 使用 `lroute('videos.show')`）。驗證：`php artisan migrate` 與 `migrate:rollback` 皆成功。
- [x] 1.2 新增 `App\Support\YouTubeUrl::extractId()`，支援 watch / youtu.be / shorts / embed / live / m. / music. / youtube-nocookie，拒絕非 YouTube host 與非 11 碼 ID。驗證：`tests/Unit/YouTubeUrlTest.php` 以資料提供者涵蓋有效變體、含 `t=`/`list=` 參數、無效網址、`javascript:` 與空字串。
- [x] 1.3 新增 `VideoObserver`（比照 `NewsObserver` 自動產生並確保唯一的 slug；使用者提供時標準化）並註冊。驗證：測試涵蓋留空自動產生、自填標準化、衝突加數字後綴。
- [x] 1.4 `RolesAndPermissionsSeeder` 新增 `video.manage` 並加入 `manager`、`editor`。驗證：重跑 seeder 兩次不報錯，兩個角色擁有該權限（Feature test 斷言）。

## 2. 後端 API

- [x] 2.1 新增 `StoreVideoRequest` / `UpdateVideoRequest`（`title.zh-TW` 必填、`description`/`slug`/`meta_*` 為選填陣列、`youtube_url` 以 `YouTubeUrl` 驗證、解析後 ID 唯一〔更新時忽略自身〕、`sort_order`、`is_active`）與 `VideoResource`（輸出全部多語系欄位、`youtube_id`、`youtube_url`、`embed_url`、`thumbnail_url`、`sort_order`、`is_active`）。驗證：隨 2.2 的 Feature test 通過。
- [x] 2.2 新增 `Api\VideoController` 與 `Route::apiResource('videos', ...)`，每個動作皆 `authorize('video.manage')`，列表依 `sort_order`、`id` 排序，新增預設啟用。驗證：`tests/Feature/VideoManagementTest.php` 涵蓋建立（三種網址變體、含 SEO 欄位並可讀回）、無效網址 422、缺 `zh-TW` 標題 422、重複影片 422、更新（換網址、同網址不誤判重複、無效網址保留原資料）、刪除、列表排序、無權限 403、未登入 401。

## 3. Admin SPA

- [x] 3.1 新增 `resources/js/services/videoService.js`（list/create/update/delete）並匯出。驗證：`npm run build` 無錯誤。
- [ ] 3.2 新增 `views/video/index.vue`：`el-table`（縮圖、名稱、排序、啟用開關、操作）；新增／編輯 `el-dialog`，含 YouTube 網址、各語系分頁的名稱／簡介／Slug／Meta Title／Meta Description／Meta Keywords、排序、啟用；刪除確認 `ElMessageBox`；顯示後端 422 訊息；縮圖失敗佔位圖；空狀態。驗證：本機以 `admin` 帳號手動走完新增（含 SEO 欄位）、編輯、刪除、貼無效網址流程並截圖。 （程式已完成、`npm run build` 通過；尚缺瀏覽器實際操作驗證）
- [ ] 3.3 於 `router/index.js` 新增 `video` 路由（`meta.permission = 'video.manage'`，標題「影片管理」）並確認選單顯示。驗證：`super-admin` 看得到選單；移除權限的帳號看不到選單且直接開網址被導回。

## 4. 前台

- [x] 4.1 新增 `Frontend\VideoController`（`index` 每頁 12 筆只含啟用影片；`show` 停用／不存在回 404；`redirectToSlug` 301）與 `routes/web.php` 的 `videos.index`、`videos.show`、`/videos/{id}` 路由（比照 news 的 `Route::multilang`）。驗證：`tests/Feature/VideoFrontendTest.php` 涵蓋列表只顯示啟用影片、停用影片詳情 404、slug 錯誤 301、各語系回退。
- [x] 4.2 新增 `videos/index.blade.php`（`page-hero`、影片卡片網格、分頁、空狀態）與 `videos/show.blade.php`（`youtube-nocookie` 延遲載入 iframe、名稱、簡介、其他影片），沿用 Industrial Blue 樣式，並在 `lang/zh-TW`、`lang/en` 的 `frontend.php` 新增 `video.*` 文字。驗證：headless Chrome 於 1440 與 500px 截圖，無橫向溢出、版型與新聞頁一致。
- [x] 4.3 詳情頁輸出 SEO：`metaTitle`（回退名稱）、`metaDescription`（回退簡介摘要）、`metaKeywords`、`ogImage` = YouTube 縮圖，以及 `VideoObject` JSON-LD；列表頁設定標題與描述。驗證：Feature test 斷言 HTML 內含自訂 `<title>`、description、keywords、`VideoObject`，且未填時使用回退值。
- [x] 4.4 頁尾 Support 欄加入「影片」連結，`GenerateSitemap` 收錄列表頁與啟用影片詳情頁。驗證：頁尾 HTML 含連結；執行 sitemap 指令後輸出含啟用影片、不含停用影片（Feature test）。

## 5. 文件與收尾

- [x] 5.1 更新 `README`／相關說明（若有後台功能清單）加入影片管理與 `video.manage` 權限，並註明部署後需重跑 `RolesAndPermissionsSeeder`。驗證：文件已含上述內容。
- [x] 5.2 執行完整測試與格式化。驗證：新增測試全數通過、`pint --test` 無新增違規、`openspec validate add-video-management` 通過。
