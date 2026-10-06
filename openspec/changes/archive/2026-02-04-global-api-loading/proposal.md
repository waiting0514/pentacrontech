## 為什麼 (Why)

目前後台管理系統在進行 API 請求 (Query/Mutation) 時缺乏視覺反饋。使用者無法得知操作是否正在處理中。我們需要一個自動化機制，當 Axios 發送請求時自動顯示 Loading 狀態，並在完成後自動隱藏，以提升使用者體驗 (UX)。

## 變更內容 (What Changes)

- **狀態管理 (State Management)**: 在 Pinia 中建立 `useLoadingStore`，使用計數器 (Counter) 機制管理 `isLoading` 狀態。
- **網路層 (Network Layer)**: 設定 Axios 的請求 (Request) 與回應 (Response) 攔截器，自動觸發 Loading Store 的狀態切換。
- **使用者介面 (UI)**: 建立一個 `GlobalLoadingOverlay` 元件，設定極高 z-index 以阻擋互動，並在 Loading 期間顯示旋轉動畫 (Spinner)。
- **組態設定 (Configuration)**: 允許透過 Axios 設定傳入參數，讓特定的背景請求略過全域 Loading 機制。

## 功能範疇 (Capabilities)

### 新功能 (New Capabilities)
- `global-loading`: 負責管理應用程式層級的 Loading 狀態、攔截器邏輯以及遮罩層 UI。

### 修改功能 (Modified Capabilities)
<!-- 無 -->

## 影響範圍 (Impact)

- **前端架構**: 新增全域 Store 與攔截器邏輯。
- **使用者體驗 (UX)**: 預設情況下所有 API 呼叫都會阻擋 UI 互動，除非特別設定略過。
- **檔案**: `src/stores/loading.ts`, `src/utils/axios.ts` (或相應路徑), `src/components/GlobalLoadingOverlay.vue`, `App.vue`。