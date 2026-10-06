## Context

目前 AI 圖片辨識建立商品的流程是跨頁的：AI 工具頁 → CameraUploader 辨識 → store 暫存 → router.push 跳轉產品頁 → onMounted 讀取 store 填表單。產品頁已有完整的接收邏輯（`onMounted` 檢查 `productStore.pendingParsedData`，自動 `openCreate()` 並填入表單），但需要跳頁才能觸發。

CameraUploader 元件已解耦，透過 `@parsed` 和 `@create` 事件溝通，可直接嵌入任何父元件。

## Goals / Non-Goals

**Goals:**
- 在產品頁內完成「AI 辨識 → 填表單 → 儲存」的完整流程，不跳頁
- 複用現有的 CameraUploader 元件與表單填入邏輯
- AI 工具頁簡化為僅 Chat 功能

**Non-Goals:**
- 不改動 CameraUploader 元件本身
- 不改動後端 API
- 不移除 product store 的 `setPendingParsedData`（可能有其他入口使用）

## Decisions

### 1. 產品頁新增 AI Dialog

在產品列表頁的標題列加「AI 辨識建立」按鈕（與「新增產品」並排），點擊開啟 `el-dialog` 內嵌 CameraUploader。

**理由：** 與現有的新增/翻譯 dialog 模式一致，使用者熟悉。按鈕僅在 `projectStore.isAiEnabled` 時顯示。

### 2. 資料流改為本地處理（不經 store 跳頁）

CameraUploader 的 `@create` 事件直接在產品頁處理：
1. 關閉 AI dialog
2. 將 `imageFiles` 存入本地 ref（`pendingAiImages`）
3. 呼叫現有 `openCreate()` 開啟新增表單
4. 複用現有的 `onMounted` 填表單邏輯，但抽成獨立 function `fillFormWithParsedData(data)` 供兩處呼叫

**理由：** 避免跳頁，資料在同一元件內傳遞更簡潔。抽出填表函式避免重複程式碼。

### 3. AI 工具頁回歸單頁 Chat

移除 `el-tabs`、`activeTab`、CameraUploader 相關的 import 和 handler。頁面僅保留 AI Chat card。

**理由：** 圖片工具已搬到產品頁，AI 工具頁不需要 tab 切換。

## Risks / Trade-offs

- **Store 舊路徑仍保留** → 萬一有其他地方呼叫 `setPendingParsedData` 跳轉產品頁，仍能運作。但 AI 工具頁不再提供此入口。
- **Dialog 疊加** → AI dialog 關閉後立刻開啟新增 dialog，需確保動畫不衝突。用 `nextTick` 確保 DOM 更新完成。
