## Why

現有後台管理介面以單一 `Dashboard.vue` 元件搭配多個 Manager 元件構成，缺乏標準化的後台框架支撐（路由管理、側邊欄導航、權限控制、佈局系統）。隨著功能增長，維護成本上升且使用者體驗不一致。採用 Vue Pure Admin 框架重構後台，可獲得成熟的企業級後台架構、統一的 UI 元件庫（Element Plus）、以及內建的路由/權限/佈局系統。

## What Changes

- **BREAKING** 移除現有 `Dashboard.vue` 單頁面架構，改用 Vue Pure Admin 的多路由頁面架構
- **BREAKING** 移除 Tailwind CSS 後台樣式，改用 Element Plus 元件庫
- 新增 Vue Pure Admin 框架核心（佈局系統、側邊欄、標籤頁導航、麵包屑）
- 將現有 Manager 元件重構為獨立路由頁面：
  - `ProductManager.vue` → 產品管理頁面（含列表/新增/編輯）
  - `ProductCategoryManager.vue` → 產品分類管理頁面
  - `NewsManager.vue` → 新聞管理頁面（含列表/新增/編輯）
  - `NewsCategoryManager.vue` → 新聞分類管理頁面
  - `BannerManager.vue` → Banner 管理頁面
  - `ProjectManager.vue` → 專案管理頁面
  - `AiAssistant.vue` → AI 助手頁面
  - `SystemSettings.vue` → 系統設定頁面
  - `UsageMonitor.vue` → 用量監控頁面
- 重構 `Login.vue` 使用 Element Plus 表單元件
- 整合現有 Axios API 呼叫與 Laravel Sanctum 認證
- 保留現有前台頁面（Blade 模板）不受影響

## Capabilities

### New Capabilities
- `admin-layout`: Vue Pure Admin 佈局系統整合（側邊欄、頂部導航、標籤頁、麵包屑）
- `admin-routing`: 後台多頁面路由架構與選單配置
- `admin-auth`: 後台登入/登出流程與路由守衛整合 Sanctum 認證

### Modified Capabilities
- `product`: 產品管理 UI 從自訂元件遷移至 Element Plus 表格/表單
- `banner`: Banner 管理 UI 遷移至 Element Plus 元件

## Impact

- **前端依賴變更**: 新增 `@pureadmin/layout`、`element-plus`、`pinia`、`vue-router`；移除後台對 Tailwind CSS 的依賴
- **建構配置**: `vite.config.js` 需調整以支援 Element Plus 按需引入與 Pure Admin 配置
- **API 層**: 現有 API 端點不變，僅重構前端呼叫方式（統一 Axios 封裝與攔截器）
- **Blade 模板**: `dashboard.blade.php` 需更新入口點引用
- **前台頁面**: 不受影響，前台仍使用 Blade + Tailwind CSS
