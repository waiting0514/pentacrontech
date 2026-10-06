## ADDED Requirements

### Requirement: 靜態錯誤訊息對應
系統必須提供一個靜態的錯誤代碼對應表，將後端特定的 `error_code` 轉換為繁體中文訊息。

#### Scenario: 已知錯誤代碼查詢
- **WHEN** 系統接收到後端回傳的 `error_code` (例如 "USER_NOT_FOUND")
- **THEN** 系統必須回傳對應的中文訊息 (例如 "查無此使用者")

#### Scenario: 未知錯誤代碼查詢
- **WHEN** 系統接收到不在對應表中的 `error_code`
- **THEN** 系統必須回傳後端原始的 `message` 欄位
- **AND** 若 `message` 也不存在，則回傳預設錯誤訊息 "系統發生未預期的錯誤"

### Requirement: 全域 API 錯誤攔截
系統必須透過 HTTP Client (Axios) 攔截器統一處理所有 API 回應錯誤。

#### Scenario: 處理 422 表單驗證錯誤
- **WHEN** API 回傳 HTTP 狀態碼 422
- **THEN** 攔截器必須顯示一個通用的 "資料驗證失敗" Toast 訊息
- **AND** 攔截器必須將 `response.data.errors` 物件回傳給呼叫端 (Promise.reject)，以便元件顯示欄位錯誤

#### Scenario: 處理業務邏輯與系統錯誤 (非 422)
- **WHEN** API 回傳 HTTP 狀態碼非 422 (如 400, 401, 403, 500)
- **THEN** 攔截器必須讀取 `response.data.error_code`
- **AND** 使用「靜態錯誤訊息對應」邏輯決定最終顯示訊息
- **AND** 使用 Toast 元件顯示最終訊息
- **AND** 必須將錯誤繼續拋出 (Promise.reject) 以便呼叫端進行必要的後續處理 (如停止 Loading)

### Requirement: 巢狀錯誤欄位支援
系統必須能夠處理並傳遞巢狀的驗證錯誤鍵值 (例如 `description.zh`)。

#### Scenario: 巢狀欄位錯誤傳遞
- **WHEN** 後端回傳 422 錯誤包含巢狀 Key `{"description.zh": ["必填"]}`
- **THEN** 前端表單元件必須能接收到原始的 Key 並對應到 Input 欄位
