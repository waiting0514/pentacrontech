## ADDED Requirements

### Requirement: 全域 Loading 狀態管理
系統必須維護一個全域 Loading 狀態，使用計數器機制來追蹤活躍的 API 請求，以處理同時發生的請求。

#### Scenario: 請求開始
- **WHEN**透過 HTTP Client 發起 API 請求
- **THEN** 全域 Loading 狀態必須變為 true
- **AND** Loading 計數器必須增加

#### Scenario: 請求結束
- **WHEN** API 請求完成（成功或失敗）
- **THEN** Loading 計數器必須減少
- **AND** 如果計數器歸零，全域 Loading 狀態必須變為 false

### Requirement: Loading 遮罩 UI
當全域 Loading 狀態為活躍時，系統必須顯示一個全螢幕的阻擋式遮罩 (Overlay)。

#### Scenario: 顯示遮罩
- **WHEN** 全域 Loading 狀態為 true
- **THEN** 遮罩必須可見
- **AND** 遮罩的 z-index 必須高於所有其他應用程式內容（例如模態視窗、頁首）
- **AND** 遮罩必須阻止使用者與底層元素的互動
- **AND** 遮罩必須顯示視覺指示器（旋轉圖示/Spinner）

#### Scenario: 隱藏遮罩
- **WHEN** 全域 Loading 狀態為 false
- **THEN** 遮罩必須被隱藏

### Requirement: 條件式略過 Loading
系統必須允許特定的 API 請求透過設定來略過全域 Loading 機制。

#### Scenario: 略過設定
- **WHEN** 發起 API 請求時帶有 `globalLoading: false` 設定
- **THEN** 全域 Loading 狀態不得改變
- **AND** Loading 計數器不得改變
