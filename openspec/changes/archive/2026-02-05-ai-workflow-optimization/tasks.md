# AI 流程優化任務列表

## 1. 資料庫與後端 (Database & Backend)

- [x] 1.1 建立 Migration: `projects` 表新增 `is_ai_enabled` 欄位 (default: true)
- [x] 1.2 建立 Middleware: `EnsureAiEnabled` 檢查專案 AI 開關
- [x] 1.3 註冊 Middleware 至 `api` 路由群組或特定 AI 路由
- [x] 1.4 更新 `ProjectController` 允許更新 `is_ai_enabled` 欄位

## 2. 前端設定與防護 (Frontend Settings & Guard)

- [x] 2.1 更新 `admin/views/settings/index.vue`: 新增 AI 功能開關 UI
- [x] 2.2 更新 `Layout/Sidebar.vue`: 根據 `is_ai_enabled` 動態顯示/隱藏 AI 工具選單
- [x] 2.3 更新 `AiTools/index.vue`: 加入檢查，若被停用則顯示提示或導向

## 3. 工作流整合 (Workflow Integration)

- [x] 3.1 更新 `admin/views/product/index.vue`: 在編輯對話框新增「AI 一鍵翻譯」按鈕
- [x] 3.2 實作「翻譯設定」彈窗：顯示語系清單、偵測空值並預設勾選
- [x] 3.3 實作前端翻譯邏輯：呼叫 API 並將結果填回表單
- [x] 3.4 更新 `admin/views/ai-tools/index.vue`: 解析成功後新增「建立產品」按鈕
- [x] 3.5 實作解析資料傳遞：點擊按鈕後將資料存入 Store 並跳轉至產品頁面
- [x] 3.6 更新 `admin/views/product/index.vue`: 進入時檢查 Store 是否有解析資料，若有則自動開啟新增框並填入
