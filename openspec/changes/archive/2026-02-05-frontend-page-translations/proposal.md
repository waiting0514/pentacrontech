# Frontend Page Translations - Proposal

## Why

翻譯基礎架構已建立完成，前台 Blade 頁面仍使用硬編碼的中文字串。需要將所有前台頁面改用 Laravel `__()` 翻譯函式，實現真正的多語系支援。

## What Changes

- 將所有前台 Blade 模板中的硬編碼字串改為 `{{ __('frontend.xxx') }}` 格式
- 補充 `lang/zh-TW/frontend.php` 和 `lang/en/frontend.php` 缺少的翻譯
- 導航列、頁尾、頁面內容等全面翻譯化

## Scope

前台 Blade 頁面：

### 頁面
1. `welcome.blade.php` - 首頁/歡迎頁
2. `pages/home.blade.php` - 首頁
3. `pages/contact.blade.php` - 聯絡我們

### 產品
4. `products/index.blade.php` - 產品列表
5. `products/show.blade.php` - 產品詳情

### 最新消息
6. `news/index.blade.php` - 消息列表
7. `news/show.blade.php` - 消息詳情

### 共用元件
8. `partials/header.blade.php` - 頁首導航
9. `partials/footer.blade.php` - 頁尾
10. `components/*` - 共用元件

## Capabilities

### New Capabilities
<!-- 無新增 capability，僅為內容翻譯工作 -->

### Modified Capabilities
<!-- 無修改現有 spec -->

## Impact

- **修改檔案**: 所有 `resources/views/**/*.blade.php` 前台模板
- **翻譯檔案**: `lang/zh-TW/frontend.php`, `lang/en/frontend.php` 新增缺少的 key
- **無 API 變更**: 僅前端字串替換

## Alternatives Considered

1. **使用 JavaScript i18n** - Blade 模板應使用 Laravel 原生翻譯
2. **僅翻譯部分頁面** - 不完整的體驗，不建議
