## ADDED Requirements

### Requirement: Translation API Endpoint
系統必須提供一個 API 端點，讓前端應用程式能根據指定的語系和範圍 (scope) 取得翻譯字串。

#### Scenario: 取得指定語系的共用翻譯
- **WHEN** 前端呼叫 `GET /api/translations/{locale}` 且未指定 scope 參數
- **THEN** 系統必須回傳該語系的共用翻譯 (`lang/{locale}.json`)
- **AND** 回應格式為 JSON 物件，key 為翻譯鍵，value 為翻譯文字

#### Scenario: 取得指定 scope 的模組翻譯
- **WHEN** 前端呼叫 `GET /api/translations/{locale}?scope=admin,products`
- **THEN** 系統必須載入共用翻譯 (`lang/{locale}.json`)
- **AND** 系統必須載入 `lang/{locale}/admin.php` 並將 key 加上 `admin.` 前綴
- **AND** 系統必須載入 `lang/{locale}/products.php` 並將 key 加上 `products.` 前綴
- **AND** 將所有翻譯合併成單一扁平化 JSON 物件回傳

#### Scenario: 處理不存在的語系
- **WHEN** 前端呼叫 `GET /api/translations/{locale}` 且該語系不存在
- **THEN** 系統必須回傳 404 錯誤
- **AND** 錯誤訊息必須說明該語系不可用

#### Scenario: 處理不存在的 scope
- **WHEN** 前端呼叫 `GET /api/translations/{locale}?scope=invalid_scope`
- **THEN** 系統必須忽略不存在的 scope 檔案
- **AND** 僅回傳存在的翻譯內容（至少包含共用翻譯）

### Requirement: Translation API 快取
系統必須對翻譯 API 回應進行適當快取，以提升效能。

#### Scenario: HTTP 快取標頭
- **WHEN** 翻譯 API 回傳成功回應
- **THEN** 回應必須包含 `Cache-Control: public, max-age=3600` 標頭
- **AND** 瀏覽器可快取該回應 1 小時

#### Scenario: 伺服器端快取
- **WHEN** 相同的 locale 和 scope 組合被多次請求
- **THEN** 系統應使用 Laravel Cache 快取合併後的翻譯結果
- **AND** 快取時間為 1 小時

### Requirement: Translation API 公開存取
翻譯 API 必須允許未認證的請求存取，因為前台頁面需要在登入前載入翻譯。

#### Scenario: 未認證請求
- **WHEN** 未帶 Authorization token 的請求呼叫翻譯 API
- **THEN** 系統必須正常回傳翻譯內容
- **AND** 不得要求身份驗證
