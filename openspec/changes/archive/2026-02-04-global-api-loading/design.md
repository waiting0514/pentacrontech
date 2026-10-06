## Context
目前系統後台缺乏統一的 API 請求 Loading 狀態回饋。我們將引入 Pinia Store 來管理全域 Loading 狀態，並利用 Axios Interceptor 自動攔截請求進行狀態切換，最後透過一個全域覆蓋層 (Overlay) 來阻擋使用者操作並顯示 Loading 動畫。

## Goals / Non-Goals

**Goals:**
- 實作全域 Loading 狀態管理 (Pinia)。
- 自動化請求攔截與狀態切換 (Axios)。
- 提供視覺回饋與操作阻擋 (UI Overlay)。
- 支援特定請求略過全域 Loading。

**Non-Goals:**
- 不涉及局部 Loading (如按鈕內的 Spinner) 的統一管理，這是全域遮罩專用的。
- 不修改現有 API 的錯誤處理邏輯 (僅做攔截)。

## Decisions

### 1. State Management: Pinia Store with Counter
- **Decision**: 使用計數器 (`loadingCount`) 而非單純的布林值 (`isLoading`)。
- **Rationale**: 頁面可能同時發出多個 API 請求 (例如初始化時)。如果使用布林值，第一個請求結束就會關閉 Loading，導致後續請求仍在進行但 Loading 已消失。計數器能確保所有請求都完成後才關閉。

### 2. Implementation: Axios Interceptors
- **Decision**: 在 Axios 實例層級統一處理，而非在每個 Component 或 Service 中手動呼叫。
- **Rationale**: 減少重複代碼，確保行為一致性，並降低開發者維護成本。
- **Mechanism**: 
  - Request: `loadingStore.startLoading()`
  - Response (Success/Error): `loadingStore.stopLoading()`
  - Config: 檢查 `config.globalLoading !== false` 才觸發。

### 3. UI: Fixed Overlay with Z-Index
- **Decision**: 使用 `fixed` 定位與極高 `z-index` (e.g., 9999) 的 Overlay。
- **Rationale**: 確保 Loading 期間使用者無法點擊任何按鈕或連結，防止重複提交或狀態不一致。

## Risks / Trade-offs

- **[Risk] UX 干擾**: 頻繁的小請求可能導致畫面不斷閃爍。
  - **Mitigation**: 對於背景同步或非關鍵請求，開發者應設定 `globalLoading: false`。
- **[Risk] Loading 卡死**: 如果請求被攔截但未正確進入 Response/Error 處理 (如網路層崩潰)，Loading 可能無法關閉。
  - **Mitigation**: 在 Axios Interceptor 的 `error` 處理中確保 `stopLoading` 總是被呼叫。考慮在 Store 中加入 `reset()` 方法供例外狀況使用。

## Open Questions
- 是否需要加入 "延遲顯示" (Debounce) 機制？(例如請求超過 200ms 才顯示 Loading，避免快速請求閃爍)。目前暫不實作，視後續回饋決定。
