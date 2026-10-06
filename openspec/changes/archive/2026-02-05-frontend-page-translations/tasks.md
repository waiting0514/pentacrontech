# Frontend Page Translations - Tasks

## Phase 1: Translation Files Setup

- [x] 1. 更新 `lang/zh-TW/frontend.php` 加入所有必要的翻譯 key
- [x] 2. 更新 `lang/en/frontend.php` 加入對應的英文翻譯

## Phase 2: Layout & Partials Translation

- [x] 3. 翻譯 `layouts/app.blade.php` - 主版型 (無硬編碼文字)
- [x] 4. 翻譯 `partials/header.blade.php` - 頁首導航
- [x] 5. 翻譯 `partials/footer.blade.php` - 頁尾
- [x] 6. 翻譯 `partials/_meta.blade.php` - Meta 標籤 (無硬編碼文字)

## Phase 3: Components Translation

- [x] 7. 翻譯 `components/lang-switcher.blade.php` - 語系切換器 (動態生成，無硬編碼)

## Phase 4: Pages Translation

- [x] 8. 翻譯 `welcome.blade.php` - 歡迎頁 (重定向到 home)
- [x] 9. 翻譯 `pages/home.blade.php` - 首頁
- [x] 10. 翻譯 `pages/contact.blade.php` - 聯絡我們
- [x] 11. 翻譯 `pages/maintenance.blade.php` - 維護頁面

## Phase 5: Products Translation

- [x] 12. 翻譯 `products/index.blade.php` - 產品列表
- [x] 13. 翻譯 `products/show.blade.php` - 產品詳情

## Phase 6: News Translation

- [x] 14. 翻譯 `news/index.blade.php` - 消息列表
- [x] 15. 翻譯 `news/show.blade.php` - 消息詳情

## Phase 7: Admin Pages (Backend rendered)

- [x] 16. 翻譯 `login.blade.php` - 登入頁 (Vue SPA，不需修改)
- [x] 17. 翻譯 `dashboard.blade.php` - 後台入口 (Vue SPA，不需修改)

## Phase 8: Verification

- [x] 18. 測試 `/zh-TW/*` 路徑中文顯示正確
- [x] 19. 測試 `/en/*` 路徑英文顯示正確
- [x] 20. 測試語系切換器功能正常
- [x] 21. 確認無 PHP 錯誤
