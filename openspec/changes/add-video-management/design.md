# Design

## Context

後台 Admin SPA（Vue 3 + Element Plus）已有 Banner、News 等 CRUD 頁面，後端慣例為 `Api\*Controller` + `authorize('xxx.manage')` + `ApiResponse` trait + `Resource`，多語系欄位使用 Spatie `HasTranslations`（`zh-TW` 必填）。權限在 `RolesAndPermissionsSeeder` 定義。本 change 沿用這些慣例，動機見 proposal.md。

## Goals / Non-Goals

**Goals:**
- 以最少欄位讓客戶貼 YouTube 網址就能管理影片。
- 與現有 Banner 管理頁的互動與程式風格一致。
- 前台提供獨立影片列表與詳情頁，每支影片有自己的網址與 SEO 標籤。

**Non-Goals:**
- 首頁區塊、產品頁嵌入影片（之後再評估）。
- 影片檔案上傳、轉檔、Vimeo 等其他來源。
- 呼叫 YouTube Data API 取得標題、時長（避免 API key 與配額）。
- 影片分類、關聯產品／新聞。

## Decisions

1. **只存影片 ID，不存完整網址。**
   `videos.youtube_id`（varchar 11，unique）。前端送出網址，後端解析。原因：嵌入網址與縮圖都由 ID 組成；網址的各種變體（`youtu.be`、`shorts`、追蹤參數）正規化後可避免重複與 XSS／任意網址風險。
   替代方案：存原網址並在前台解析 → 每次顯示都要解析且無法以 unique 防重複，捨棄。

2. **解析邏輯集中在一個可單元測試的類別**（`App\Support\YouTubeUrl::extractId(string): ?string`），以 `parse_url` 比對 host（`youtube.com`、`www.`、`m.`、`music.`、`youtube-nocookie.com`、`youtu.be`）與路徑（`watch?v=`、`shorts/`、`embed/`、`live/`、`v/`），ID 需符合 `^[A-Za-z0-9_-]{11}$`。不使用單一巨型 regex，也不接受裸 11 碼 ID。
   驗證透過 Form Request 的自訂 rule 呼叫該類別，Controller 與 Request 不重複邏輯。

3. **文字與 SEO 欄位多語系，網址、排序、狀態不分語系。**
   `title`（必填 `zh-TW`）、`description`、`meta_title`、`meta_description`、`meta_keywords` 為 JSON 翻譯欄位，沿用 `HasTranslations`；`slug`、YouTube ID、排序、狀態與語系無關。slug 為單一唯一字串（不做多語系），由 `VideoObserver` 自動產生：避免 `SlugService` 的 JSON 路徑唯一性查詢不適用於字串欄位，也讓一支影片只有一個網址。

4. **縮圖由 ID 推導：** Resource 輸出 `thumbnail_url = https://img.youtube.com/vi/{id}/hqdefault.jpg`，不下載、不入庫。
   替代方案：下載後存 storage → 增加儲存與同步問題，且此階段不需要。

5. **API：** 使用 `Route::apiResource('videos', VideoController::class)`（index/store/show/update/destroy），皆 `authorize('video.manage')`。排序透過 update 的 `sort_order` 欄位，不另做拖曳 API（列表以數字欄位調整，與 Banner 的 `updateSort` 風格取捨：本次影片量預期少，先求簡單）。
   Resource 同時回傳 `youtube_url`（`https://www.youtube.com/watch?v={id}`）供編輯表單回填，及 `embed_url`（`https://www.youtube-nocookie.com/embed/{id}`）供日後前台使用。

6. **前端：** `views/video/index.vue` 使用 `el-table` + `el-dialog` 表單（網址、標題多語系分頁、排序、啟用開關）；刪除使用 `ElMessageBox.confirm`。網址欄位在前端做基本非空檢查，格式以後端為準並顯示後端錯誤訊息。
   路由 `meta.permission = 'video.manage'`，與 `banner` 同樣由現有路由守衛與選單過濾處理。

7. **權限：** 新增 `video.manage`，加入 `manager` 與 `editor`（`super-admin` 由 Gate::before 全權限）。`RolesAndPermissionsSeeder` 使用 `firstOrCreate` / `syncPermissions`，可安全重跑。

8. **前台路由與頁面：** 比照新聞，使用 `Route::multilang('/videos', ...)` → `videos.index`，以及 `/videos/{id}-{slug}` → `videos.show`，另有 `/videos/{id}` 301 導向。`Frontend\VideoController` 只查 `is_active = true`；停用或不存在回 404。列表每頁 12 筆。
   詳情頁以 `youtube-nocookie.com/embed/{id}` 的 `<iframe loading="lazy">` 嵌入，不帶 autoplay。SEO 傳入既有 `partials._meta`（`metaTitle` 回退標題、`metaDescription` 回退簡介摘要、`metaKeywords`、`ogImage` = YouTube 縮圖），並輸出 `VideoObject` JSON-LD（`name`、`description`、`thumbnailUrl`、`embedUrl`、`uploadDate`）。
   替代方案：只做列表頁、點擊以彈窗播放 → 每支影片沒有獨立網址，填寫的 SEO 欄位無處輸出，捨棄。
9. **可發現性：** 頁尾 Support 欄新增「影片」連結（不動主導覽，避免擠壓六個項目）；`GenerateSitemap` 加入列表頁與啟用影片詳情頁。
10. **樣式：** 沿用 Industrial Blue 的 `page-hero`、`ix-*` 卡片與 `ind-*` 分頁，不新增獨立設計語彙。

## Risks / Trade-offs

- [使用者貼入 YouTube 以外的網址] → 後端嚴格驗證 host 與 ID 格式，回 422 並顯示明確訊息。
- [影片被作者刪除或設為私人，縮圖壞圖] → 列表縮圖加載失敗時顯示佔位圖；不做存活檢查（需 API）。
- [`img.youtube.com` 在部分網路被擋] → 僅影響後台縮圖顯示，不影響資料；可接受。
- [iframe 隱私與 CSP] → 使用 `youtube-nocookie.com`；若專案日後啟用 CSP 需放行 `youtube-nocookie.com` 與 `img.youtube.com`。
- [影片 ID 變更或被下架導致詳情頁只剩黑畫面] → 不做存活檢查；客戶可在後台停用。
- [無拖曳排序] → 以數字排序欄位替代，影片數量增加後再評估。

## Migration Plan

1. 新增 migration 建立 `videos` 表（無破壞性、可 `migrate:rollback`）。
2. 部署後重跑 `php artisan db:seed --class=RolesAndPermissionsSeeder`（idempotent）建立 `video.manage` 並授予角色。
3. 回滾：`migrate:rollback` 移除資料表；`video.manage` 權限殘留無害。

## Open Questions

- 首頁是否要放影片區塊？不影響資料模型與本 change 任務，之後再決定。
