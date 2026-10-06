## Context

目前系統需要同時支援單語系與多語系專案。現有的路由設定並未考慮多語系架構，所有的路由都是直接定義在 `routes/web.php` 根層級。為了達成 SEO 最佳化 (如避免內容重複、保持預設語言網址乾淨) 並實現動態語言切換，我們需要重構路由架構並引入中介層邏輯。

## Goals / Non-Goals

**Goals:**
- 實作一套「寫一次，處處運行」的路由架構，適用於單語系與多語系專案。
- 確保預設語言使用「乾淨網址」(無前綴)，非預設語言使用帶前綴網址。
- 自動處理 SEO 301 重導向 (當使用者存取預設語言但帶有前綴時)。
- 提供前端 Blade 元件以生成正確的語言切換連結。

**Non-Goals:**
- 本次設計不包含「後台管理介面 (Admin Panel)」的多語系路由 (後台通常為 SPA 或不需要 SEO 嚴格規範)。
- 不涉及內容翻譯的儲存實作 (這是 `spatie/laravel-translatable` 或其他套件的職責，本設計僅專注於路由與 Locale 設定)。

## Decisions

### 1. 使用 Middleware 處理 Locale 設定與重導向
我們選擇使用一個全域 Middleware (`SetLocale`) 來負責 Locale 的偵測與設定，而非在每個 Controller 或 Route Group 中個別處理。
- **Rationale**: 集中管理邏輯，確保所有進入應用程式的 Request 都經過統一的語系判斷流程。能有效攔截錯誤的 URL 結構 (如 Case B) 並執行重導向。
- **Alternative**: 使用 Laravel 的 `Route::prefix('{locale}')` 群組。
    - *缺點*: 難以處理「預設語言不帶前綴」的需求 (需要定義兩組路由，一組有前綴一組沒有，造成維護困難)。

### 2. 資料庫驅動的語言設定
語言清單儲存於資料庫 `languages` 表，而非設定檔 (`config/app.php`)。
- **Rationale**: 允許管理員在後台動態啟用/停用語言，無需重新部署程式碼。
- **Caching**: 為了效能，`active_languages` 應被快取 (Cache)，避免每個 Request 都查詢資料庫。

### 3. 動態路由群組設計 (Revised)
原本計劃使用 `Route::group(['prefix' => '{locale?}'])`，但發現 Laravel 不支援在 URL 開頭使用 Optional Parameter (`{locale?}/products` 無法匹配 `/products`)。
因此，我們改採 **Macro + Dual Routes** 策略：
- 定義 `Route::multilang` Macro，內部自動產生兩個路由：
  1. Default (No Prefix): `/products` -> `products.index`
  2. Localized (Prefix): `/{locale}/products` -> `products.index.localized`
- 實作 `lroute()` Helper 來自動選擇正確的路由名稱生成 URL。
- 修改 `SetLocale` Middleware，處理預設語言的 301 重導向 (從 Localized 路由導向 Default 路由)。

### 4. 前端連結生成
不再依賴 `URL::defaults` (因為路由名稱不同)，而是：
- Blade View 中建議使用 `lroute('route.name')` 來自動生成帶有當前語系的連結。
- `LangSwitcher` 元件負責切換時的路由名稱轉換 (加/減 `.localized` 後綴)。

## Risks / Trade-offs

- **Risk**: 快取過期問題。
    - **Mitigation**: 當後台更新語言設定時，必須同時清除相關 Cache。
- **Risk**: 第三方套件路由衝突。
    - **Mitigation**: 第三方套件通常有自己的路由檔。若需納入多語系路由，需確認其路由定義是否允許被包覆或需手動處理。
- **Risk**: 效能影響。
    - **Mitigation**: Middleware 中的 DB 查詢必須使用快取。

## Migration Plan

1. **Database**: 建立 `languages` 表格並執行 migration。
2. **Seeding**: 根據現有專案狀態 (單/多語系) 填充初始資料。
3. **Code**: 實作 Middleware 與 Service Provider 邏輯。
4. **Refactor**: 修改 `routes/web.php`，將前台路由移入群組。
5. **View**: 部署 `<x-lang-switcher />` 元件並更新 Layout。
6. **Verify**: 測試各種 URL 組合 (有前綴/無前綴/無效前綴) 的行為。
