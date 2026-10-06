## 1. 產品頁 — 新增 AI 辨識 Dialog

- [x] 1.1 Import CameraUploader 元件，新增 `showAiDialog` ref 和 `pendingAiImages` ref
- [x] 1.2 抽出 `fillFormWithParsedData(data)` 函式，將 onMounted 中填表邏輯搬入（lines 349-379）
- [x] 1.3 新增 `handleAiCreate({ imageFiles, data })` handler：關閉 AI dialog → 存 pendingAiImages → nextTick → openCreate → fillFormWithParsedData
- [x] 1.4 在標題列「新增產品」按鈕旁加「AI 辨識建立」按鈕，v-if="projectStore.isAiEnabled"
- [x] 1.5 在 template 底部加 el-dialog，內嵌 CameraUploader，綁定 @create="handleAiCreate"
- [x] 1.6 更新 saveProduct 中的圖片上傳邏輯，同時支援 pendingAiImages 和 store 的 pendingImages
- [x] 1.7 儲存完成後清除 pendingAiImages 和 preview URLs

## 2. AI 工具頁 — 簡化為 Chat

- [x] 2.1 移除 CameraUploader import、activeTab ref、handleParsed、handleCreateProduct
- [x] 2.2 移除 el-tabs 結構，保留 AI Chat card 作為頁面主體
- [x] 2.3 移除 useProductStore import 和 useRouter（不再需要跳頁）

## 3. 驗證

- [x] 3.1 產品頁：AI 按鈕顯示/隱藏符合 isAiEnabled 狀態
- [x] 3.2 產品頁：上傳圖片 → 辨識 → 建立產品 → 表單自動填入 → 儲存成功含圖片（需手動測試）
- [x] 3.3 產品頁：連續建立第二筆，dialog 無殘留狀態（需手動測試）
- [x] 3.4 AI 工具頁：僅顯示 Chat 功能，無圖片工具
- [x] 3.5 Store 舊路徑：若 pendingParsedData 有值，onMounted 仍能正常填表
