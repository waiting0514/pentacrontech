## Why

使用者要用 AI 辨識建立商品時，必須先到 AI 工具頁上傳圖片，辨識完後跳轉到產品頁填表單，儲存後又要回 AI 工具頁才能繼續下一筆。連續建立多筆商品時來回跳頁體驗很差。將 CameraUploader 直接內嵌到產品頁，讓整個流程不離開產品管理頁面。

## What Changes

- 產品列表頁新增「AI 辨識建立」按鈕，點擊開啟 el-dialog 內嵌 CameraUploader
- 辨識完成後關閉 dialog，直接填入當頁新增表單，不再跳頁
- AI 工具頁移除圖片工具 tab，僅保留 AI Chat 功能，回歸單頁佈局
- 不再需要透過 Pinia store 跨頁傳遞 AI 解析資料（保留 store 方法但產品頁改為本地處理）

## Capabilities

### New Capabilities

（無新增 — 功能本身不變，只是搬移位置與改善流程）

### Modified Capabilities

- `workflow-integration`: 「DM 解析後自動建立」需求從跨頁跳轉改為產品頁內 dialog 操作，不再跳轉至 AI 工具頁

## Impact

- `resources/js/admin/views/product/index.vue` — 新增 AI 辨識按鈕與 dialog
- `resources/js/admin/views/ai-tools/index.vue` — 移除圖片工具 tab，簡化為 Chat 頁面
- `CameraUploader.vue` 元件本身不需改動
- Product store（`setPendingParsedData`）保留但不再是主要資料傳遞方式
