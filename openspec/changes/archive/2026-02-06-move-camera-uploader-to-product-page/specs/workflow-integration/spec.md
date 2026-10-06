## MODIFIED Requirements

### Requirement: DM 解析後自動建立
產品管理頁面 SHALL 提供「AI 辨識建立」按鈕，開啟對話框內嵌 CameraUploader，讓使用者在產品頁內完成圖片辨識與產品建立流程，不需跳轉至 AI 工具頁。

#### Scenario: 開啟 AI 辨識對話框
- **WHEN** 使用者在產品列表頁點擊「AI 辨識建立」按鈕
- **THEN** 系統 SHALL 開啟 el-dialog，內嵌 CameraUploader 元件供上傳與辨識圖片

#### Scenario: AI 功能未啟用時隱藏按鈕
- **WHEN** 專案的 AI 功能未啟用（`isAiEnabled` 為 false）
- **THEN** 「AI 辨識建立」按鈕 SHALL 不顯示

#### Scenario: 辨識完成後填入表單
- **WHEN** 使用者在 AI 辨識對話框中完成辨識並點擊「建立產品」
- **THEN** 系統 SHALL 關閉 AI 辨識對話框，自動開啟產品新增對話框，並將解析出的名稱、描述、規格預先填入表單（預設填入主要語系），同時保留圖片檔案供儲存時自動上傳

#### Scenario: 連續建立多筆產品
- **WHEN** 使用者儲存產品後再次點擊「AI 辨識建立」按鈕
- **THEN** 系統 SHALL 開啟全新的 AI 辨識對話框，不殘留前次上傳或辨識結果
