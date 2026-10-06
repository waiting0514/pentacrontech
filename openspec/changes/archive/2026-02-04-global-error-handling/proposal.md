## 為什麼 (Why)

目前前端缺乏統一的錯誤處理機制。後端回傳的錯誤格式固定，但前端未進行統一攔截與轉換，導致：
1. 使用者看到的錯誤訊息不友善（可能是後端原始代碼或英文）。
2. 表單驗證錯誤 (422) 處理分散，重複程式碼多。
3. 缺乏統一的 UI 回饋標準 (Toast vs Alert)。

我們需要建立一個全域錯誤處理機制，統一轉譯後端錯誤代碼，並自動化處理驗證錯誤與系統錯誤的 UI 呈現。

## 變更內容 (What Changes)

- **錯誤對應 (Error Mapping)**: 建立靜態對應表，將後端 `error_code` 轉換為繁體中文的使用者友善訊息。
- **網路層 (Network Layer)**: 修改 Axios 攔截器，區分處理 422 驗證錯誤與其他系統錯誤。
- **使用者介面 (UI)**: 整合 Toast 元件 (Element Plus ElMessage) 來顯示錯誤訊息。
- **表單整合**: 確保攔截器能正確傳遞 422 `errors` 物件給元件進行欄位級別的錯誤顯示。

## 功能範疇 (Capabilities)

### 新功能 (New Capabilities)
- `global-error-handling`: 包含錯誤代碼對應表、Axios 錯誤攔截邏輯、以及全域 Toast 通知機制。

### 修改功能 (Modified Capabilities)
<!-- 無 -->

## 影響範圍 (Impact)

- **前端架構**: `src/utils/axios.ts` (或 `http.js`) 將大幅修改錯誤處理邏輯。
- **新增檔案**: `src/constants/errors.ts`。
- **相依性**: 依賴現有的 UI Library (Element Plus) 進行通知顯示。
