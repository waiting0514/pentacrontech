## ADDED Requirements

### Requirement: 多語系輸入元件 (Translatable Input Component)
後台必須提供一個 Vue 元件，用於編輯支援多語系的 JSON 欄位。

#### Scenario: 多語系模式下的分頁顯示
- **WHEN** 系統啟用超過一種語言
- **THEN** 元件應顯示分頁標籤 (Tabs)，例如 [繁體中文] [English]
- **AND** 使用者切換標籤時，應切換對應語言的輸入框內容

#### Scenario: 語言填寫狀態指示器
- **WHEN** 系統啟用超過一種語言
- **THEN** 每個語言標籤旁應顯示填寫狀態指示器
- **AND** 已填寫的語言標籤旁顯示綠色圓點 (●)
- **AND** 未填寫的語言標籤旁顯示灰色空心圓圈 (○)
- **AND** 使用者可一目瞭然哪些語言尚未填寫

#### Scenario: 單語系模式下的簡化顯示
- **WHEN** 系統僅啟用一種語言
- **THEN** 元件不應顯示分頁標籤
- **AND** 僅顯示單一輸入框，操作體驗應與標準輸入框一致

### Requirement: 資料雙向綁定與傳輸 (Data Binding and Transmission)
元件必須正確處理資料庫回傳的 JSON 物件，並在儲存時傳送正確的格式給 API。

#### Scenario: 提交表單資料
- **WHEN** 使用者在分頁中輸入不同語言內容並儲存
- **THEN** 元件應將資料組合成 `{ "zh-TW": "內容", "en": "Content" }` 的物件
- **AND** 透過 API 傳送給後端 Controller 進行儲存
