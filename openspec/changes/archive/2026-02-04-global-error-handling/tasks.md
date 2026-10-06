## 1. 錯誤對應表 (Error Map)

- [x] 1.1 建立 `resources/js/constants/errors.ts`。
- [x] 1.2 定義 `ERROR_MESSAGES` 常數，包含常見錯誤代碼 (VALIDATION_ERROR, UNAUTHORIZED, etc.) 與中文翻譯。

## 2. Axios 攔截器實作 (Interceptor Implementation)

- [x] 2.1 修改 `resources/js/services/http.js`。
- [x] 2.2 引入 `ERROR_MESSAGES` 與 Element Plus 的 `ElMessage`。
- [x] 2.3 實作 422 錯誤處理邏輯：顯示通用 Toast，並確保 `error.response.data.errors` 可被後續存取。
- [x] 2.4 實作通用錯誤處理邏輯：解析 `error_code`，查表或使用 Fallback 訊息，顯示 Toast。

## 3. 驗證與測試 (Verification)

- [x] 3.1 驗證 422 錯誤：送出空表單，確認 Toast 顯示「資料驗證失敗」，且 Input 下方顯示錯誤 (需配合現有 Form 元件測試)。
- [x] 3.2 驗證通用錯誤：模擬後端回傳 500 或特定 error_code，確認 Toast 顯示正確中文訊息。
- [x] 3.3 驗證未知錯誤：模擬後端回傳未定義代碼，確認顯示後端原始 message 或預設訊息。
