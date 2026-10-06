## Why

為了支援通用的 CMS 架構，使其能以同一套程式碼同時處理「單語系」與「多語系」專案，我們需要一套統一的路由系統。此系統必須能動態處理非預設語言的 URL 前綴 (例如 `/en/products`)，同時針對預設語言保持乾淨的 URL (例如 `/products`)，並透過 301 重導向防止重複內容，以確保最佳的 SEO 表現。

## What Changes

- **資料庫 (Database)**: 新增 `languages` 資料表，用於設定每個專案啟用哪些語言及預設語言。
- **中介層 (Middleware)**: 實作 `SetLocale` 中介層，負責檢查 URL 區段 (Segment)，並設定應用程式語系或執行 SEO 正規化 (301 重導向)。
- **路由 (Routing)**: 將前台路由包覆在帶有可選 `{locale?}` 前綴的動態群組中。
- **前端 (Frontend)**: 建立 Blade 元件 `<x-lang-switcher />`，用於生成對 SEO 友善的語言切換連結。
- **設定 (Configuration)**: 全域註冊新的中介層。

## Capabilities

### New Capabilities
- `multilang-routing`: 處理動態 URL 前綴、語系偵測以及基於專案設定的 SEO 重導向。

### Modified Capabilities
<!-- 目前沒有既有功能的需求被修改。 -->

## Impact

- **Routes**: `routes/web.php` 將被重構以使用動態語系群組。
- **Middleware**: 新的全域中介層將影響所有傳入的 HTTP 請求。
- **Database**: 新增 `languages` 資料表的 migration。
- **Views**: 新增用於語言切換的 Blade 元件；現有 View 可能需要更新以配合新的路由輔助函式行為 (雖然 Laravel 的 `route()` 通常能妥善處理上下文參數)。