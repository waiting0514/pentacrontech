## Why

目前前台 (Blade) 和後台 (Vue Admin) 共用所有翻譯字串，導致前台載入大量後台專用的翻譯內容，影響效能且有潛在安全疑慮（後台術語暴露給前端）。需要一個分層架構，讓前台只載入必要翻譯，後台可按需載入特定模組。

## What Changes

- 建立分層翻譯檔案結構：共用層 (JSON) + 專用層 (PHP modules)
- 新增 Translation API，支援 `scope` 參數動態載入指定翻譯模組
- 前台 Blade 使用 Laravel 原生 `__()` helper，自動載入對應語系檔案
- 後台 Vue 透過 API 按需載入 admin、products 等模組翻譯
- 整合 vue-i18n 或自訂 `$t()` helper 供 Vue 元件使用

## Capabilities

### New Capabilities
- `translation-api`: 提供 `/api/translations/{locale}?scope=admin,products` 端點，根據 scope 參數合併並回傳翻譯 JSON
- `translation-file-structure`: 定義 `lang/{locale}.json` (共用) 與 `lang/{locale}/*.php` (專用模組) 的檔案結構規範

### Modified Capabilities
<!-- 無需修改現有 spec -->

## Impact

- **新增檔案**:
  - `app/Http/Controllers/Api/TranslationController.php`
  - `lang/zh-TW.json`, `lang/en.json` (共用翻譯)
  - `lang/zh-TW/admin.php`, `lang/zh-TW/frontend.php` (專用翻譯)
  - `lang/en/admin.php`, `lang/en/frontend.php`
- **路由**: 新增 `/api/translations/{locale}` 路由
- **前端**: Vue admin 需在初始化時呼叫翻譯 API 並設定 i18n
- **依賴**: 可選擇性整合 `vue-i18n` 套件，或使用輕量自訂方案
