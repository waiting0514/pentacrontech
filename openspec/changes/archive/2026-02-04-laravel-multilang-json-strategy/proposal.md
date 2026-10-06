## Why

為了支援多語系內容管理，我們需要將現有的字串欄位轉換為支援多語言的 JSON 格式。這允許在單一資料庫欄位中儲存多種語言版本，簡化資料庫結構並利用 JSON 查詢的彈性，同時保持對舊有單語系資料的相容性。

## What Changes

- **Dependencies**: 安裝並設定 `spatie/laravel-translatable` 套件。
- **Database**: 將 `products`, `product_categories` (已是 json), `news` 等資料表的文字欄位 (name, title, description, content, slug) 轉換為 JSON 型態，並遷移舊資料。
- **Models**: 在相關 Model 引入 `HasTranslations` Trait 並設定 `$translatable` 屬性。
- **Frontend**: 建立 `TranslatableInput.vue` 元件，提供支援多語系輸入的分頁介面 (與現有 Vue Admin SPA 整合)。
- **Configuration**: 設定 fallback locale 以確保缺漏翻譯時能顯示預設內容。

## Capabilities

### New Capabilities
- `multilang-json-data`: 定義基於 JSON 的多語系資料儲存、讀取與查詢規範 (含 Slug 處理)。
- `admin-translatable-ui`: 定義後台多語系輸入介面的互動與顯示邏輯。

### Modified Capabilities
<!-- No existing capabilities are being modified in terms of requirements. -->

## Impact

- **Database**: Migration 將會修改現有資料表結構並執行資料轉換，需注意備份。
- **Models**: `Product`, `News` (Category 已支援) 將啟用多語系功能。
- **Admin UI**: 表單輸入將改用新的 Vue 元件。
- **Queries**: 涉及 Slug 的查詢需改為 JSON Path 查詢 (例如 `where('slug->en', 'my-slug')`)。
