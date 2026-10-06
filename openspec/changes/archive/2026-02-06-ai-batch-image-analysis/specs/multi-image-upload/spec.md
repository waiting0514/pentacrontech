# Capability: Multi-Image Context Analysis

支援上傳同一產品的多張圖片（DM、包裝、不同角度等），合併為單一 context 發送給 AI 進行綜合分析。

## ADDED Requirements

### Requirement: Multiple file selection
系統 SHALL 允許使用者透過檔案選擇器一次選取多張圖片（最多 5 張）。

#### Scenario: User selects multiple images
- **WHEN** 使用者點擊上傳區域並選取 3 張圖片（產品正面、背面、DM）
- **THEN** 系統顯示 3 張圖片的預覽縮圖
- **AND** 顯示訊息「3 張圖片已選取」

#### Scenario: User selects too many images
- **WHEN** 使用者選取超過 5 張圖片
- **THEN** 系統顯示警告「最多只能上傳 5 張圖片」
- **AND** 僅保留前 5 張圖片

### Requirement: Preview grid display
系統 SHALL 以網格佈局顯示所有已選取圖片的縮圖預覽。

#### Scenario: Display thumbnail grid
- **WHEN** 使用者選取 4 張圖片
- **THEN** 系統顯示 4 張縮圖於響應式網格中
- **AND** 每張縮圖提供移除按鈕

### Requirement: Remove individual image
系統 SHALL 允許使用者移除任一已選取的圖片。

#### Scenario: User removes one image
- **WHEN** 使用者點擊某張圖片的移除按鈕
- **THEN** 該圖片從預覽網格中移除
- **AND** 圖片計數更新

### Requirement: Multi-image context analysis (Backend API)
系統 SHALL 提供新的 API 端點，將多張圖片合併為單一 context 發送給 GPT-4o Vision。

#### Scenario: Parse multiple images as single context
- **GIVEN** 後端收到 3 張圖片
- **WHEN** 呼叫 `POST /api/ai/parse-images`
- **THEN** 系統將 3 張圖片全部發送給 GPT-4o Vision
- **AND** AI 綜合所有圖片資訊返回單一結果
- **AND** 記錄 AI 使用量和成本

#### Scenario: GPT-4o Vision multi-image input
- **GIVEN** 3 張圖片（正面、背面、規格表）
- **WHEN** 發送給 GPT-4o Vision
- **THEN** 使用 OpenAI 原生多圖輸入格式
- **AND** AI 能識別並整合所有圖片的資訊

### Requirement: Single scan button
系統 SHALL 提供單一「開始掃描」按鈕，一次解析所有圖片。

#### Scenario: Scan all images together
- **WHEN** 使用者選取 3 張圖片後點擊「開始掃描」
- **THEN** 系統顯示 loading 狀態
- **AND** 呼叫 `/api/ai/parse-images` 傳送所有圖片
- **AND** 返回單一綜合解析結果

#### Scenario: Show loading state
- **WHEN** 系統正在解析圖片
- **THEN** 顯示「AI 分析中...」
- **AND** 禁用掃描按鈕

### Requirement: Display single result
系統 SHALL 顯示單一綜合解析結果（非每張圖片獨立結果）。

#### Scenario: Show combined result
- **WHEN** AI 解析完成
- **THEN** 顯示產品名稱（綜合所有圖片資訊）
- **AND** 顯示規格（從包裝/規格表提取）
- **AND** 顯示描述（整合 DM 內容）
- **AND** 顯示「建立產品」按鈕

#### Scenario: Show variants if detected
- **WHEN** AI 偵測到多個顏色/規格變體
- **THEN** 在結果中列出變體資訊
- **AND** 例如：黑色、白色、藍色

### Requirement: Error handling
系統 SHALL 妥善處理解析失敗的情況。

#### Scenario: API error
- **WHEN** API 返回錯誤
- **THEN** 顯示錯誤訊息
- **AND** 顯示「重試」按鈕
- **AND** 保留已選取的圖片

### Requirement: Create product from result
系統 SHALL 允許使用者從解析結果建立產品。

#### Scenario: Create product from parsed data
- **WHEN** 使用者點擊「建立產品」按鈕
- **THEN** 系統將解析資料傳遞給產品 store
- **AND** 導航至產品頁面

### Requirement: Reset all
系統 SHALL 提供重設功能清除所有圖片和結果。

#### Scenario: Reset all images
- **WHEN** 使用者點擊「重設」按鈕
- **THEN** 清除所有圖片預覽
- **AND** 清除解析結果
- **AND** 回到初始上傳狀態

### Requirement: Add more images
系統 SHALL 允許使用者在已有圖片的情況下繼續添加更多圖片。

#### Scenario: Add more images
- **WHEN** 使用者已選取 2 張圖片，再點擊「添加更多」選取 2 張
- **THEN** 預覽網格顯示共 4 張圖片

### Requirement: Backward compatibility
系統 SHALL 保留原有單圖 API `/api/ai/parse-image` 不變。

#### Scenario: Single image still works
- **WHEN** 使用者只上傳 1 張圖片並點擊掃描
- **THEN** 系統呼叫多圖 API（傳送 1 張圖片）
- **AND** 返回解析結果
