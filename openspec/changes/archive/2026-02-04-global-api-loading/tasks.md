## 1. 狀態管理 (State Management)

- [x] 1.1 建立 `resources/js/stores/loading.ts` (或相應路徑的 Pinia store)。
- [x] 1.2 實作 `loadingCount` 狀態與 `isLoading` 計算屬性。
- [x] 1.3 實作 `startLoading()`、`stopLoading()` 與 `resetLoading()` 動作 (Actions)。

## 2. Axios 攔截器 (Axios Interceptors)

- [x] 2.1 尋找專案中的 Axios 實例定義檔案 (例如 `resources/js/bootstrap.js` 或 `resources/js/api.js`)。
- [x] 2.2 實作 Request 攔截器：檢查 `config.globalLoading !== false` 並呼叫 `store.startLoading()`。
- [x] 2.3 實作 Response 攔截器 (Success)：呼叫 `store.stopLoading()`。
- [x] 2.4 實作 Response 攔截器 (Error)：確保在錯誤發生時也呼叫 `store.stopLoading()`。

## 3. UI 元件實作 (UI Component)

- [x] 3.1 建立 `resources/js/components/GlobalLoadingOverlay.vue`。
- [x] 3.2 使用 TailwindCSS 實作全螢幕半透明遮罩層 (Fixed, z-index: 9999)。
- [x] 3.3 實作中央旋轉動畫 (Spinner) 視覺效果。
- [x] 3.4 根據 `loadingStore.isLoading` 控制元件顯示。

## 4. 全域整合 (Global Integration)

- [x] 4.1 在主進入點或佈局檔案 (例如 `resources/js/App.vue`) 中引入並掛載 `GlobalLoadingOverlay` 元件。

## 5. 測試與驗證 (Testing)

- [x] 5.1 驗證單一請求時 Loading 正常顯示與消失。
- [x] 5.2 驗證多個併發請求時，計數器機制運作正常 (最後一個請求結束才關閉 Loading)。
- [x] 5.3 驗證傳入 `{ globalLoading: false }` 的請求是否能成功略過 Loading 遮罩。
- [x] 5.4 驗證 API 錯誤時，Loading 遮罩能正確關閉，不卡死畫面。
