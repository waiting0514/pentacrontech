# Workflow Integration Spec

## 1. Overview
This specification defines how AI tools are integrated into core business workflows like product management.

## 2. Requirements

### Requirement: 產品編輯一鍵翻譯
產品編輯對話框 SHALL 提供「AI 一鍵翻譯」功能，允許使用者將當前語系的內容翻譯至其他已啟用的語系。

#### Scenario: 選擇翻譯語系
- **WHEN** 使用者點擊「AI 翻譯」按鈕
- **THEN** 顯示彈窗列出所有可翻譯的目標語系，預設勾選「內容為空」的語系，已有內容的語系預設不勾選並標示警語

#### Scenario: 執行翻譯填入
- **WHEN** 使用者確認翻譯
- **THEN** 系統呼叫翻譯 API，並將回傳結果自動填入對應語系的輸入欄位中

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
