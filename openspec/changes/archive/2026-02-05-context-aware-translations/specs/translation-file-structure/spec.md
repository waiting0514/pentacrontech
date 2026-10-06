## ADDED Requirements

### Requirement: 共用翻譯層 (Shared Translation Layer)
系統必須支援 JSON 格式的共用翻譯檔案，存放各端點共用的高頻詞彙。

#### Scenario: JSON 共用翻譯檔案位置
- **WHEN** 系統需要載入共用翻譯
- **THEN** 系統必須從 `lang/{locale}.json` 讀取（例如 `lang/zh-TW.json`）
- **AND** JSON 檔案必須使用扁平 key-value 結構

#### Scenario: 共用翻譯內容範例
- **WHEN** 開發者需要新增共用翻譯
- **THEN** 應將高頻、跨情境使用的詞彙放入 JSON 檔案
- **AND** 範例內容包含：`"Yes": "是"`, `"No": "否"`, `"Save": "儲存"`, `"Cancel": "取消"`

### Requirement: 專用翻譯層 (Specific Translation Layer)
系統必須支援 PHP 格式的模組化翻譯檔案，依使用情境分離翻譯內容。

#### Scenario: PHP 模組翻譯檔案位置
- **WHEN** 系統需要載入特定模組翻譯
- **THEN** 系統必須從 `lang/{locale}/{module}.php` 讀取
- **AND** 支援的模組包含但不限於：`frontend.php`, `admin.php`, `products.php`, `validation.php`

#### Scenario: 前台專用翻譯 (frontend.php)
- **WHEN** 前台 Blade 頁面需要翻譯
- **THEN** 系統必須提供 `lang/{locale}/frontend.php`
- **AND** 內容包含前台專用字串：行銷文案、購物車、結帳流程等
- **AND** 使用 `__('frontend.xxx')` 格式存取

#### Scenario: 後台專用翻譯 (admin.php)
- **WHEN** 後台 Vue Admin 需要翻譯
- **THEN** 系統必須提供 `lang/{locale}/admin.php`
- **AND** 內容包含後台專用字串：儀表板、設定、使用者管理等
- **AND** 使用 `$t('admin.xxx')` 格式存取

### Requirement: 翻譯 Key 命名規範
系統必須遵循一致的翻譯 key 命名規範，避免衝突並提升可維護性。

#### Scenario: PHP 模組自動加前綴
- **WHEN** Translation API 載入 PHP 模組翻譯
- **THEN** 系統必須自動在 key 前加上模組名稱作為前綴
- **AND** 例如 `admin.php` 中的 `'dashboard' => '儀表板'` 會變成 `admin.dashboard`

#### Scenario: 避免 key 衝突
- **WHEN** JSON 共用層與 PHP 模組層有相同 key
- **THEN** PHP 模組層的 key（帶前綴）優先級較高
- **AND** 開發者應避免在 JSON 使用帶點號的 key（如 `admin.dashboard`）

### Requirement: 多語系支援
系統必須支援多種語系，且語系結構保持一致。

#### Scenario: 必要語系檔案
- **WHEN** 系統初始化
- **THEN** 系統必須包含以下語系檔案：
  - `lang/zh-TW.json` 與 `lang/zh-TW/*.php` (繁體中文)
  - `lang/en.json` 與 `lang/en/*.php` (英文)

#### Scenario: 新增語系
- **WHEN** 需要新增其他語系（如日文）
- **THEN** 開發者只需建立 `lang/ja.json` 和 `lang/ja/` 目錄
- **AND** 並複製現有 PHP 檔案結構進行翻譯
