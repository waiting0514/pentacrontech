## 1. 環境與套件設定

- [x] 1.1 安裝 `spatie/laravel-translatable` 套件 (使用 `sail composer require`)
- [x] 1.2 發布套件設定檔並將 `fallback_locale` 設為 `zh-TW`
- [x] 1.3 確保 `config/app.php` 的語系設定符合專案需求

## 2. 資料庫結構調整 (修改現有 Migration)

- [x] 2.1 修改 `create_products_table.php`：將 `name`, `description`, `content`, `slug` 改為 `json` 型態
- [x] 2.2 修改 `create_news_table.php`：將 `title`, `content`, `slug` 改為 `json` 型態
- [x] 2.3 確認 `create_product_categories_table.php`：`name`, `slug` 欄位已設為 `json`

## 3. 模型設定 (Model Setup)

- [x] 3.1 在 `Product` 模型中引用 `HasTranslations` 並設定 `$translatable` 屬性
- [x] 3.2 在 `News` 模型中引用 `HasTranslations` 並設定 `$translatable` 屬性
- [x] 3.3 在 `ProductCategory` 模型中引用 `HasTranslations` 並設定 `$translatable` 屬性
- [x] 3.4 確保所有模型正確設定 `$casts` 欄位為 `array` 或 `json`

## 4. 資料填充更新 (Seeder Update)

- [x] 4.1 更新 `ProductSeeder`：將測試資料改為 JSON 格式 (例如 `['zh-TW' => '產品名稱']`)
- [x] 4.2 更新 `NewsSeeder`：將測試資料改為 JSON 格式
- [x] 4.3 更新 `ProductCategorySeeder`：將測試資料改為 JSON 格式
- [x] 4.4 執行 `vendor/bin/sail artisan migrate:fresh --seed` 並驗證

## 5. 後台 Vue 元件實作

- [x] 5.1 建立 `TranslatableInput.vue` 元件，支援分頁切換不同語系輸入
- [x] 5.2 建立 `TranslatableRichText.vue` (用於富文本編輯器) 或整合現有編輯器
- [x] 5.3 在產品編輯與新聞編輯頁面中導入這些多語系組件 (已完成產品頁面更新)

## 6. API 與 路由優化

- [x] 6.1 更新前台 Controller 的 Slug 查詢邏輯，支援 JSON Path 搜尋
- [x] 6.2 確保後端 API 能正確接收並儲存 Vue 傳來的語系陣列 (透過 `LanguageController` 與 Spatie Trait)