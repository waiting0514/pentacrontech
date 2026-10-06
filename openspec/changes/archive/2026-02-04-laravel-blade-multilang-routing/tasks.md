## 1. 資料庫與模型準備

- [x] 1.1 建立 `languages` 資料表的 Migration (欄位: code, name, is_default, is_active)
- [x] 1.2 建立 `Language` Model 並設定全域或本地快取邏輯
- [x] 1.3 撰寫基礎的 Seeder 用於初始化語言資料 (例如預設為 zh-TW)

## 2. 核心邏輯實作 (Middleware & Provider)

- [x] 2.1 建立 `SetLocale` Middleware，實作 URL Segment 解析與 301 重導向邏輯
- [x] 2.2 在 `bootstrap/app.php` 中註冊 `SetLocale` Middleware 到 `web` 群組
- [x] 2.3 建立或修改 `AppServiceProvider`，實作 `URL::defaults` 邏輯以自動處理路由參數 (Updated: 使用 `lroute` Helper 與 Macro 取代)

## 3. 路由重構

- [x] 3.1 在 `routes/web.php` 中定義帶有 `{locale?}` 前綴的路由群組 (Updated: 使用 `Route::multilang` Macro)
- [x] 3.2 將現有的前台路由 (Home, Products, News, Contact) 移入該語系群組
- [x] 3.3 驗證路由名稱是否保持一致，避免破壞現有的 `route()` 連結

## 4. 前端元件實作

- [x] 4.1 建立 `LangSwitcher` View Component (指令: `php artisan make:component LangSwitcher`)
- [x] 4.2 實作元件邏輯：計算啟用的語言、生成當前路徑對應不同語系的 URL
- [x] 4.3 撰寫元件 Blade 範本，處理單語系不顯示與多語系列表渲染

## 5. 測試與驗證

- [x] 5.1 測試預設語言路徑 (例如 `/products`) 是否正確設定 Locale 且無前綴
- [x] 5.2 測試非預設語言路徑 (例如 `/en/products`) 是否正確設定 Locale 並顯示內容
- [x] 5.3 測試帶有預設語言前綴的路徑 (例如 `/zh-TW/products`) 是否執行 301 重導向
- [x] 5.4 驗證語言切換器在不同頁面下的連結生成是否正確 (包含帶參數的產品分頁)