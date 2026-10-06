## Context
目前系統後端使用標準化的錯誤格式 (`success`, `code`, `error_code`, `message`, `errors`)。前端需要一個集中式的地方來處理這些錯誤，避免在每個 API 呼叫中重複寫 `try-catch` 和 `ElMessage.error`。

## Goals / Non-Goals

**Goals:**
- 建立 `src/constants/errors.ts` 管理錯誤訊息。
- 修改 `src/services/http.js` (Axios 實例) 實作統一攔截。
- 自動化 Toast 顯示 (Element Plus `ElMessage`)。
- 確保 422 錯誤能正確傳遞給 Vue 元件處理欄位紅字。

**Non-Goals:**
- 不涉及多語系 (i18n) 實作，直接使用繁體中文硬編碼於 `errors.ts`。
- 不修改後端 API 回傳格式。

## Decisions

### 1. Error Mapping Strategy
- **Decision**: 使用簡單的 Key-Value 物件 (`Record<string, string>`)。
- **Rationale**: 需求暫無多語系要求，純物件查詢效能最好且易於維護。

### 2. Interceptor Logic Flow
- **Decision**: 在 `response.use(success, error)` 的 `error` callback 中處理。
- **Logic**:
  1. 檢查 `error.response` 是否存在。
  2. 若 `status === 422`: 顯示 "資料驗證失敗" Toast，並 `reject(error.response.data.errors)` (注意：這裡需要確認回傳格式是否方便元件使用，通常回傳完整 error 物件讓元件自己取 data 比較標準，但為了簡化元件邏輯，我們將保持 reject error，但在 error物件上掛載解析後的 errors)。
  3. 若其他 Status: 解析 `error_code` -> 查表 -> 顯示 Toast -> `reject(error)`。

### 3. UI Component
- **Decision**: 使用 `Element Plus` 的 `ElMessage`。
- **Rationale**: 專案已安裝並配置 Element Plus (`resources/js/admin/main.js`)，保持一致性。

## Risks / Trade-offs

- **[Risk] 錯誤訊息覆蓋**: 後端有時會動態組裝 message (例如 "User ID 123 not found")，靜態表會丟失這些動態資訊。
  - **Mitigation**: 僅對固定的業務邏輯錯誤 (`error_code`) 查表。如果查無此 code，則優先顯示後端回傳的 `message`。

- **[Risk] 併發錯誤**: 多個 API 同時失敗會導致 Toast 重疊或洗版。
  - **Mitigation**: `ElMessage` 預設會自動排列，Element Plus 處理得還不錯。若過多，可考慮加入 `grouping: true` 選項。
