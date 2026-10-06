## Why

翻譯基礎架構已建立完成，但後台 Vue Admin 頁面仍使用硬編碼的中文字串。需要將所有後台頁面改用 `$t()` 翻譯函式，實現真正的多語系支援。

## What Changes

- 將所有後台 Vue 元件中的硬編碼字串改為 `$t('admin.xxx')` 格式
- 補充 `lang/zh-TW/admin.php` 和 `lang/en/admin.php` 缺少的翻譯
- 側邊欄、表單、按鈕、提示訊息等全面翻譯化

## Scope

後台 Vue 頁面共 11 個：
1. `dashboard/index.vue` - 儀表板
2. `login/index.vue` - 登入頁
3. `product/index.vue` - 產品管理
4. `product/category.vue` - 產品分類
5. `news/index.vue` - 最新消息
6. `news/category.vue` - 消息分類
7. `banner/index.vue` - 輪播圖管理
8. `settings/index.vue` - 系統設定
9. `users/index.vue` - 用戶管理
10. `usage/index.vue` - 用量統計
11. `ai-tools/index.vue` - AI 工具

## Capabilities

### New Capabilities
<!-- 無新增 capability，僅為內容翻譯工作 -->

### Modified Capabilities
<!-- 無修改現有 spec -->

## Impact

- **修改檔案**: 所有 `resources/js/admin/views/**/*.vue` 元件
- **翻譯檔案**: `lang/zh-TW/admin.php`, `lang/en/admin.php` 新增缺少的 key
- **無 API 變更**: 僅前端字串替換

## Alternatives Considered

1. **使用 Vue I18n 套件** - 功能完整但增加依賴，目前自訂 `$t()` 已足夠
2. **僅翻譯部分頁面** - 不完整的體驗，不建議
