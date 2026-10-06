## 1. 環境與依賴設置

- [x] 1.1 安裝新依賴：vue-router、pinia、element-plus、@element-plus/icons-vue、unplugin-vue-components、unplugin-auto-import
- [x] 1.2 更新 vite.config.js：新增 admin 入口點、Element Plus 按需引入插件配置
- [x] 1.3 建立 `resources/js/admin/` 目錄結構（main.js、App.vue、router/、store/、layout/、views/、components/、utils/）

## 2. 核心框架搭建

- [x] 2.1 建立 admin/main.js 入口：初始化 Vue app、掛載 vue-router、pinia、Element Plus
- [x] 2.2 建立 admin/App.vue 根元件
- [x] 2.3 建立 admin/router/index.js 路由配置（含所有頁面路由與 meta 配置）
- [x] 2.4 建立路由前置守衛（token 認證檢查、頁面標題更新）
- [x] 2.5 建立 admin/store/modules/user.js Pinia store（使用者狀態、登入/登出 action）

## 3. 佈局系統

- [x] 3.1 建立 admin/layout/index.vue 主佈局元件（側邊欄 + 頂部 + 內容區）
- [x] 3.2 建立 admin/layout/components/Sidebar.vue 側邊欄（從路由自動生成選單、收合功能）
- [x] 3.3 建立 admin/layout/components/Navbar.vue 頂部導航列（使用者資訊、登出、收合按鈕）

## 4. 登入頁面

- [x] 4.1 建立 admin/views/login/index.vue（Element Plus 表單、呼叫 authService、整合 Pinia store）

## 5. 核心管理頁面遷移

- [x] 5.1 建立 admin/views/banner/index.vue（從 BannerManager.vue 遷移，改用 el-table/el-dialog）
- [x] 5.2 建立 admin/views/product/index.vue（從 ProductManager.vue 遷移）
- [x] 5.3 建立 admin/views/product/category.vue（從 ProductCategoryManager.vue 遷移）
- [x] 5.4 建立 admin/views/news/index.vue（從 NewsManager.vue 遷移）
- [x] 5.5 建立 admin/views/news/category.vue（從 NewsCategoryManager.vue 遷移）

## 6. 輔助頁面遷移

- [x] 6.1 建立 admin/views/ai-tools/index.vue（從 AiAssistant + CameraUploader + ImageEditor 整合）
- [x] 6.2 建立 admin/views/usage/index.vue（從 UsageMonitor.vue 遷移）
- [x] 6.3 建立 admin/views/settings/index.vue（從 SystemSettings.vue 遷移）

## 7. 共用元件遷移

- [x] 7.1 遷移 RichTextEditor.vue 到 admin/components/
- [x] 7.2 遷移 ImageEditor.vue 到 admin/components/
- [x] 7.3 遷移 CameraUploader.vue 到 admin/components/

## 8. 整合與清理

- [x] 8.1 更新 dashboard.blade.php 引用 admin/main.js 入口
- [x] 8.2 更新 resources/js/app.js 移除後台相關邏輯
- [x] 8.3 驗證前台頁面不受影響（Blade 模板正常運作）
- [x] 8.4 驗證所有後台頁面功能正常（CRUD 操作、API 呼叫）
