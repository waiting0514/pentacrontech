# Banner 多語系重構任務列表

## 1. 資料庫與模型 (Database & Model)

- [x] 1.1 修改 `database/migrations/2026_01_30_100000_create_banners_table.php` 將文字與圖片欄位改為 JSON
- [x] 1.2 更新 `app/Models/Banner.php`：引用 `HasTranslations` Trait 並定義 `$translatable` 欄位
- [x] 1.3 在 `Banner.php` 實作智慧圖片回退邏輯 (Accessors: `image_pc_url`, `image_mobile_url`)
- [x] 1.4 執行 `php artisan migrate:fresh` (開發階段直接刷新)

## 2. 後端端點 (Backend API)

- [x] 2.1 更新 `app/Http/Controllers/Api/BannerController.php` 驗證邏輯以支援多語系輸入
- [x] 2.2 更新 `app/Http/Resources/BannerResource.php` (若存在) 或 Controller 輸出，確保回傳翻譯資料

## 3. 管理後台 (Admin UI)

- [x] 3.1 修改 `resources/js/admin/views/banner/index.vue` 的編輯對話框
- [x] 3.2 標題與連結欄位改用 `TranslatableInput`
- [x] 3.3 圖片上傳組件整合多語系分頁切換，顯示各語言對應圖片
- [x] 3.4 在列表顯示中加入當前語系的圖片預覽與回退邏輯測試

## 4. 前端顯示 (Frontend Display)

- [x] 4.1 更新首頁輪播渲染邏輯，使用模型的 Accessors 獲取圖片路徑
- [x] 4.2 測試不同語系下的圖片、文字與連結顯示是否正確（包含回退至預設語系）
