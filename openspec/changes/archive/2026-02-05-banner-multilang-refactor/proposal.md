## 為什麼要進行此變更

目前的輪播圖（Banner）實作假設圖片和連結僅支援單一語言，這限制了針對不同市場進行在地化行銷活動的能力。我們需要支援全欄位多語系的輪播圖架構，讓圖片、連結與文字皆能根據語系進行調整，以提升國際化市場的服務能力。

## 變更內容

- **重大變更 (BREAKING)**: 將 `banners` 資料表的以下欄位型態從 `string` 更改為 `json`：`title` (標題)、`link_url` (連結)、`image_pc` (電腦版圖片路徑)、`image_mobile` (手機版圖片路徑)。
- 在 `Banner` 模型中引用 `Spatie\Translatable\HasTranslations`。
- 實作「智慧圖片回退 (Smart Image Fallback)」邏輯：若特定語系的圖片欄位留空，系統將自動回退顯示「預設語系」的圖片。
- 更新管理後台 (Admin UI)，讓輪播圖編輯表單支援語言分頁（Language Tabs）。

## 核心功能 (Capabilities)

### 修改現有功能 (Modified Capabilities)
- `banner`: 更新規格以支援多語系欄位、回退邏輯以及相關的後台操作規範。

## 影響範圍

- **資料庫 (Database)**: 需修改 `banners` 資料表的遷移檔案或新增遷移。
- **模型 (Model)**: `Banner` 模型需加入 Trait 與 Accessor。
- **API**: 輪播圖相關端點將回傳 JSON 物件或根據語系解析後的字串。
- **後台介面 (Admin UI)**: 需更新輪播圖新增/編輯頁面，整合 `TranslatableInput` 與支援多語系的圖片上傳元件。
- **前台顯示 (Frontend)**: 渲染輪播圖的 Blade/Vue 組件需處理在地化資料與回退邏輯。