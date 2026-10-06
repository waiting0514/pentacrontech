## Context

目前系統使用 Laravel 預設的翻譯機制，所有翻譯字串集中在單一檔案。前台 Blade 和後台 Vue Admin 共享所有翻譯，造成：
1. 前台載入不必要的後台專用字串（如 Dashboard、Settings 等）
2. 後台需要載入前台行銷用語
3. 無法針對不同情境優化翻譯載入

本設計建立分層翻譯架構，讓各端點按需載入必要翻譯。

## Goals / Non-Goals

**Goals:**
- 建立清晰的翻譯檔案分層結構（共用 + 專用模組）
- 提供 API 端點讓前端按 scope 載入翻譯
- 前台 Blade 維持使用 Laravel 原生 `__()` helper
- 後台 Vue 可透過 API 動態載入指定模組翻譯
- 支援未來擴充更多翻譯模組（orders、reports 等）

**Non-Goals:**
- 不實作翻譯管理後台 UI
- 不整合第三方翻譯服務（如 Google Translate）
- 不處理 RTL 語系支援

## Decisions

### 1. 翻譯檔案結構

採用 Laravel 原生支援的混合模式：

```
lang/
├── zh-TW.json          # 共用層：高頻詞彙 (Yes, No, Save, Cancel...)
├── en.json
├── zh-TW/
│   ├── frontend.php    # 前台專用 (landing page, cart, checkout)
│   ├── admin.php       # 後台專用 (dashboard, settings, users)
│   └── validation.php  # Laravel 驗證訊息
└── en/
    ├── frontend.php
    ├── admin.php
    └── validation.php
```

**原因**:
- JSON 檔案適合簡單 key-value，Laravel 自動載入
- PHP 檔案支援巢狀結構與註解，適合模組化管理
- 符合 Laravel 慣例，無需額外套件

### 2. Translation API 設計

```
GET /api/translations/{locale}?scope=admin,products
```

**回應格式**:
```json
{
  "Yes": "是",
  "No": "否",
  "admin.dashboard": "儀表板",
  "admin.settings": "設定",
  "products.name": "產品名稱"
}
```

**合併邏輯**:
1. 永遠載入 `lang/{locale}.json` (共用層)
2. 根據 scope 參數載入對應 PHP 檔案
3. PHP 檔案的 key 會加上檔名前綴 (如 `admin.dashboard`)
4. 合併成扁平化 JSON 回傳

**原因**: 扁平化結構方便前端直接使用 `$t('admin.dashboard')`

### 3. Vue 整合方案

採用輕量自訂方案而非 vue-i18n：

```javascript
// 在 app.js 初始化時載入
const translations = await fetch('/api/translations/zh-TW?scope=admin,products').then(r => r.json());

app.config.globalProperties.$t = (key) => translations[key] || key;
```

**原因**:
- vue-i18n 功能強大但對本專案過於龐大
- 自訂方案簡單、可控、無額外依賴
- 未來需要進階功能時可再升級

### 4. 快取策略

- API 回應加上 `Cache-Control: public, max-age=3600`
- 前端可選擇性快取於 localStorage
- 翻譯檔案更新後，透過版本號或時間戳清除快取

## Risks / Trade-offs

| Risk | Mitigation |
|------|------------|
| 翻譯 key 重複衝突 | PHP 檔案自動加前綴，JSON 用於通用詞彙 |
| API 每次請求重新讀檔 | 使用 Laravel Cache 快取合併結果 |
| 前端忘記載入必要 scope | 開發時 console.warn 提示缺少翻譯 |
| 語系檔案越來越大 | 保持模組化，定期清理未使用翻譯 |

## Migration Plan

1. 建立翻譯檔案結構 (`lang/` 目錄)
2. 建立 `TranslationController` 與路由
3. 前台 Blade 逐步使用 `__('frontend.xxx')` 格式
4. 後台 Vue 初始化時載入翻譯 API
5. 漸進式將現有硬編碼字串改為翻譯 key
