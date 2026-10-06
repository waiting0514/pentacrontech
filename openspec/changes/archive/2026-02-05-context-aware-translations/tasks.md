## 1. 翻譯檔案結構建立

- [x] 1.1 建立 `lang/zh-TW.json` 共用翻譯檔案（Yes, No, Save, Cancel 等高頻詞彙）
- [x] 1.2 建立 `lang/en.json` 共用翻譯檔案（英文版）
- [x] 1.3 建立 `lang/zh-TW/frontend.php` 前台專用翻譯
- [x] 1.4 建立 `lang/zh-TW/admin.php` 後台專用翻譯
- [x] 1.5 建立 `lang/en/frontend.php` 前台專用翻譯（英文版）
- [x] 1.6 建立 `lang/en/admin.php` 後台專用翻譯（英文版）

## 2. Translation API 實作

- [x] 2.1 建立 `TranslationController` 控制器
- [x] 2.2 實作 `index` 方法：載入共用 JSON 翻譯
- [x] 2.3 實作 scope 參數解析：根據 scope 載入對應 PHP 模組
- [x] 2.4 實作 key 前綴邏輯：PHP 模組的 key 自動加上模組名稱前綴
- [x] 2.5 實作翻譯合併：將 JSON 與 PHP 模組翻譯合併成扁平化 JSON
- [x] 2.6 加入 Laravel Cache 快取機制（1 小時）
- [x] 2.7 設定 HTTP Cache-Control 標頭

## 3. 路由設定

- [x] 3.1 新增 `/api/translations/{locale}` 公開路由（不需認證）
- [x] 3.2 驗證路由可正常存取並回傳翻譯 JSON

## 4. Vue Admin 整合

- [x] 4.1 在 admin app 初始化時呼叫翻譯 API
- [x] 4.2 建立全域 `$t()` helper 函式
- [x] 4.3 將 `$t` 掛載到 Vue app.config.globalProperties
- [x] 4.4 測試 Vue 元件中使用 `$t('admin.dashboard')` 可正常顯示

## 5. 前台 Blade 整合

- [x] 5.1 確認 Laravel `__()` helper 可正常讀取 `lang/zh-TW/frontend.php`
- [x] 5.2 在一個前台頁面測試 `{{ __('frontend.read_more') }}` 顯示正確

## 6. 驗證與測試

- [x] 6.1 測試不帶 scope 的 API 呼叫僅回傳共用翻譯
- [x] 6.2 測試帶 scope=admin,products 的 API 呼叫回傳合併翻譯
- [x] 6.3 測試不存在的語系回傳 404
- [x] 6.4 測試不存在的 scope 被忽略且不報錯
