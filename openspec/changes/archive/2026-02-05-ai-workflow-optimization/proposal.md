## 為什麼要進行此變更

目前系統中的 AI 工具與產品管理流程是斷裂的，使用者需手動複製貼上內容，且缺乏全域的 AI 功能開關來控制成本與隱私。我們需要將 AI 功能無縫嵌入工作流，並提供專案層級的啟用/停用控制，將 AI 從「獨立工具」升級為「情境式助手」。

## 變更內容

- **新增 AI 功能總開關**: 在專案設定中新增 `is_ai_enabled` 開關，全域控制 AI 功能的啟用狀態。
- **產品編輯器 AI 整合**: 在產品編輯對話框中加入「AI 一鍵翻譯」功能，支援語系選擇與內容覆蓋檢查。
- **解析導向優化**: DM 解析完成後，提供「直接建立產品」按鈕，自動帶入解析結果並跳轉至編輯頁面。
- **後端防護**: 實作 Middleware 或權限檢查，確保在 AI 功能關閉時攔截相關 API 請求。

## 核心功能 (Capabilities)

### 新增功能 (New Capabilities)
- `ai-governance`: 定義專案層級的 AI 功能開關與權限控制規範。
- `workflow-integration`: 定義 AI 工具與業務流程（如產品管理）的整合互動規範。

### 修改現有功能 (Modified Capabilities)
- `project`: 更新規格以包含 `is_ai_enabled` 欄位與設定介面需求。

## 影響範圍

- **資料庫**: `projects` 資料表新增 `is_ai_enabled` 欄位。
- **後端 API**: 
    - `AiTextController`, `AiImageController` 需加入啟用狀態檢查。
    - `ProjectController` 需支援新欄位的更新。
- **前端介面**:
    - `Settings/index.vue`: 新增開關設定。
    - `Product/index.vue`: 整合翻譯按鈕與對話框。
    - `AiTools/index.vue`: 新增解析後的導向邏輯。
    - `Sidebar`: 根據設定動態顯示/隱藏 AI 工具選單。
