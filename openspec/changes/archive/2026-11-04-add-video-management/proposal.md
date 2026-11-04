## Why

客戶希望在網站上呈現影片，但目前沒有影片管理，也沒有前台影片頁。先提供最低成本的做法：客戶在後台貼上 YouTube 網址、填寫影片名稱與 SEO 資訊即可建立影片，前台提供獨立的影片列表與詳情頁，不需處理檔案上傳、轉檔與頻寬。

## What Changes

- 新增後台「影片管理」頁面：列表、新增、編輯、刪除。
- 新增影片時貼上 YouTube 網址；系統自動解析影片 ID 並驗證格式，支援 `watch?v=`、`youtu.be/`、`shorts/`、`embed/` 等常見網址。
- 每支影片可輸入（名稱、簡介與 Meta 欄位支援多語系，`zh-TW` 僅標題必填）：影片名稱、簡介、網址代稱（slug，單一、全站唯一，可留空自動產生）、Meta Title、Meta Description、Meta Keywords。
- 列表顯示 YouTube 縮圖（由影片 ID 推導，不下載）、名稱、排序與啟用狀態；支援排序與啟用／停用。
- 新增前台獨立「影片列表」頁（`/videos`，分頁、只顯示啟用影片）與影片詳情頁（`/videos/{id}-{slug}`，內嵌播放器、套用該影片的 SEO 欄位、輸出 `VideoObject` 結構化資料）。
- 影片頁納入網站頁尾連結與 sitemap。
- 新增權限 `video.manage`，並授予 `manager`、`editor`（`super-admin` 透過 Gate 全權限）。
- 新增 `videos` 資料表與 `/api/videos` REST API。

## Capabilities

### New Capabilities
- `video-management`: 後台以 YouTube 網址建立、編輯、刪除影片，管理名稱、簡介與 SEO 欄位，並提供排序、啟用狀態與權限控管。
- `video-frontend`: 前台影片列表與詳情頁，內嵌 YouTube 播放、per-video SEO 與 sitemap 收錄。

### Modified Capabilities
<!-- 無：既有 spec 的需求不變 -->

## Impact

- 後端：`Video` model + migration、`VideoObserver`（slug）、`Api\VideoController`、`VideoResource`、`Frontend\VideoController`、`routes/web.php`、`RolesAndPermissionsSeeder`、`GenerateSitemap`。
- 前端（Admin SPA）：`views/video/index.vue`、`services/videoService.js`、路由與選單。
- 前端（Blade）：`videos/index`、`videos/show`、頁尾連結、`lang/{zh-TW,en}/frontend.php`，沿用 Industrial Blue 樣式。
- 測試：後台 API、網址解析、前台頁面（含停用影片 404、SEO 標籤）。
- 對外依賴：播放使用 YouTube 嵌入（`youtube-nocookie.com`）；縮圖取自 `img.youtube.com`；不呼叫 YouTube API。
