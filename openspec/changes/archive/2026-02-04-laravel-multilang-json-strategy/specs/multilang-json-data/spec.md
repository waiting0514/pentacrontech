## ADDED Requirements

### Requirement: JSON 多語系資料儲存 (JSON Multi-language Storage)
系統必須將需要翻譯的欄位從字串型態轉換為 JSON 型態，以支援多種語言版本的儲存。

#### Scenario: 欄位遷移與舊資料相容
- **WHEN** 執行資料庫遷移 (Migration)
- **THEN** 系統應將 `products`, `product_categories`, `news` 的文字欄位轉換為 JSON
- **AND** 系統必須將原有的純文字資料 (如 "產品 A") 自動轉換為預設語系的 JSON 格式 (如 `{"zh-TW": "產品 A"}`)

### Requirement: Fallback 語系機制 (Fallback Locale Mechanism)
當某個語言的翻譯內容不存在時，系統必須能夠自動顯示預設語言的內容，以避免前端顯示空白。

#### Scenario: 翻譯缺失時的顯示
- **WHEN** 當前語系為 "en" 且該欄位無英文翻譯
- **THEN** 系統必須根據 `config/translatable.php` 的設定回傳 "zh-TW" 的內容

### Requirement: 語系感知的 Slug 查詢 (Locale-aware Slug Queries)
由於 Slug 轉換為 JSON 格式，系統在進行路由綁定或資料查詢時，必須使用 JSON 路徑語法。

#### Scenario: 透過 Slug 尋找資源
- **WHEN** 前端或 API 傳入一個 Slug 進行查詢
- **THEN** 系統必須使用 `where('slug->' . App::getLocale(), $slug)` 或對應的資料庫語法進行精確比對
