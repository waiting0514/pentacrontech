## Context

目前系統資料庫中的 `name`, `description`, `slug` 等欄位僅支援單一字串儲存，限制了系統支援多語言的能力。後台使用 Vue SPA，前台使用 Blade。為了達成多語系，我們需要升級資料結構並導入 `spatie/laravel-translatable`。

## Goals / Non-Goals

**Goals:**
- 將關鍵文字欄位無痛遷移至 JSON 格式，保留現有資料。
- 實現後台 Vue 元件，支援動態多語系輸入切換。
- 確保前台 Blade 模板能自動顯示正確語言。
- 確保 API 查詢 (尤其是 Slug) 能正確處理 JSON 欄位。

**Non-Goals:**
- 不涉及動態翻譯服務 (如 Google Translate API)，僅提供儲存欄位。
- 不修改後台 SPA 的整體路由架構，僅新增/修改輸入元件。

## Decisions

### 1. 資料庫 Migration 策略
我們將使用原生 SQL 或 Laravel 的 DB Raw 語法來執行欄位轉換，而非簡單的 `change()` 方法，以確保資料不遺失。
- **邏輯**: 
  1. 讀取舊字串值 (如 "Product A")。
  2. 轉換為 JSON 字串 (如 `{"zh-TW": "Product A"}`)。
  3. 修改欄位型態為 JSON。
- **原因**: 直接修改型態可能會導致資料截斷或格式錯誤。

### 2. Vue 元件設計 (`TranslatableInput.vue`)
- **Props**: 
  - `modelValue`: 接收來自父層的物件 (Object) 或空值。
  - `label`: 欄位標題。
  - `languages`: 啟用的語言列表 (從全域 Store 或 API 獲取)。
- **State**: 維護一個本地物件來綁定各語言輸入框。
- **Interaction**: 使用 Tabs 來切換不同語言的輸入框。如果是單語系模式，隱藏 Tabs。

### 3. Slug 處理
由於 Slug 現在是 JSON，`Route::bind` 或 Controller 查詢必須改變。
- **方案**: 在 `Product`, `News` 等 Model 中覆寫 `getRouteKeyName` 或自定義查詢 scope。
- **查詢語法**: `whereRaw("slug->'$."$locale"' = ?", [$slug])` 或 Laravel 的 `where('slug->' . $locale, $slug)`。

## Risks / Trade-offs

- **Risk**: JSON 查詢效能可能低於純文字索引。
  - **Mitigation**: 對於高流量查詢的 Slug 欄位，MySQL 5.7+ 支援 Generated Columns 建立索引。如果效能成為瓶頸，可考慮此方案。
- **Risk**: 舊程式碼直接存取 `$product->name` 可能會預期得到字串，但如果 Trait 未生效可能拿到 JSON 字串。
  - **Mitigation**: 確保所有 Model 正確引入 Trait，並在測試環境完整回歸測試。

## Migration Plan

1. **Package**: 安裝與設定 Spatie 套件。
2. **Backend**: 建立 Migration，執行欄位轉換。
3. **Model**: 更新 Model 加入 `$translatable`。
4. **Admin**: 建立 `TranslatableInput.vue` 並整合至表單頁面。
5. **Verify**: 驗證舊資料是否正常顯示，新資料是否能儲存多語言。
