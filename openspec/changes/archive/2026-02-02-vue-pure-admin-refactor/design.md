## Context

現有後台是一個單頁 Vue 3 應用，入口為 `resources/js/app.js`，根據 `window.location.pathname` 動態載入 `Login.vue` 或 `Dashboard.vue`。Dashboard 以 tab 切換方式載入各 Manager 元件，沒有 vue-router、沒有狀態管理、沒有標準佈局系統。

前端建構使用 Laravel Vite Plugin，CSS 使用 Tailwind CSS 4。API 服務層已模組化（`resources/js/services/`），認證使用 localStorage 存 token + Sanctum。

前台頁面使用 Blade 模板 + Tailwind CSS，與後台共用 `app.css`，需保持不受影響。

## Goals / Non-Goals

**Goals:**
- 導入 Vue Pure Admin 精簡版架構（vue-router、pinia、Element Plus、佈局系統）
- 將 tab 切換改為標準的多路由頁面架構
- 保留現有所有 API service 層程式碼
- 保留現有 Manager 元件的業務邏輯，僅重構 UI 層使用 Element Plus
- 與 Laravel Vite Plugin 整合，維持 `php artisan serve` + `npm run dev` 開發流程

**Non-Goals:**
- 不重構後端 API 或 Laravel 程式碼
- 不修改前台 Blade 模板頁面
- 不導入完整 Pure Admin 的權限系統（目前只有單一管理員角色）
- 不導入 Pure Admin 的 i18n 國際化模組（維持中文）
- 不導入 Pure Admin 的 mock 資料系統

## Decisions

### 1. 使用 pure-admin-thin 精簡版而非完整版

**選擇**: 參考 pure-admin-thin 架構，手動整合核心模組到 Laravel 專案中

**理由**: 完整版包含大量範例頁面和不需要的功能。純精簡版更適合嵌入現有 Laravel 專案。不獨立建專案，而是在 `resources/js/` 下建立 Pure Admin 風格的目錄結構。

**替代方案**:
- 完整 clone pure-admin-thin 作為獨立前端專案 → 需要額外的部署配置，與 Laravel 整合複雜度高
- 只用 Element Plus 不用 Pure Admin → 失去佈局系統、路由配置等成熟架構

### 2. 目錄結構

```
resources/js/
├── admin/                    # 後台 SPA（Pure Admin 架構）
│   ├── App.vue               # 根元件
│   ├── main.js               # 入口點
│   ├── router/
│   │   └── index.js          # vue-router 配置
│   ├── store/
│   │   ├── index.js          # pinia store
│   │   └── modules/
│   │       └── user.js       # 使用者狀態
│   ├── layout/
│   │   ├── index.vue         # 主佈局（側邊欄 + 頂部）
│   │   ├── components/
│   │   │   ├── Sidebar.vue
│   │   │   ├── Navbar.vue
│   │   │   └── TagsView.vue  # 標籤頁導航
│   │   └── hooks/
│   │       └── useLayout.js
│   ├── views/                # 頁面（原 Manager 元件重構）
│   │   ├── login/
│   │   │   └── index.vue
│   │   ├── banner/
│   │   │   └── index.vue
│   │   ├── product/
│   │   │   ├── index.vue     # 產品列表
│   │   │   └── category.vue  # 產品分類
│   │   ├── news/
│   │   │   ├── index.vue     # 新聞列表
│   │   │   └── category.vue  # 新聞分類
│   │   ├── ai-tools/
│   │   │   └── index.vue
│   │   ├── usage/
│   │   │   └── index.vue
│   │   └── settings/
│   │       └── index.vue
│   ├── components/           # 共用元件
│   │   ├── RichTextEditor.vue
│   │   ├── ImageEditor.vue
│   │   └── CameraUploader.vue
│   └── utils/
│       ├── http.js           # Axios 封裝（沿用現有 service 層）
│       └── auth.js           # Token 管理
├── services/                 # 保留現有 API service（共用）
├── bootstrap.js              # 保留
└── app.js                    # 前台入口（移除後台邏輯）
```

### 3. 前後台入口分離

**選擇**: 建立獨立的後台入口 `resources/js/admin/main.js`

**做法**:
- `resources/js/app.js` → 僅保留前台邏輯（或空檔案）
- `resources/js/admin/main.js` → 後台 SPA 入口，初始化 vue-router、pinia、Element Plus
- `dashboard.blade.php` → 改為引用 `admin/main.js`
- `vite.config.js` → 新增 admin entry point

### 4. 佈局系統

**選擇**: 自建簡化版 Pure Admin 風格佈局，不引入 `@pureadmin/layout`

**理由**: `@pureadmin/layout` 套件與獨立 Pure Admin 專案耦合較深，在 Laravel 整合場景下自建更可控。佈局包含：
- 左側可收合側邊欄（選單從 router 配置自動生成）
- 頂部導航列（用戶資訊、登出）
- 標籤頁導航（可選，後續加入）
- 主內容區（`<router-view>`）

### 5. 路由與選單配置

```js
// router/index.js
const routes = [
  {
    path: '/login',
    component: () => import('../views/login/index.vue'),
    meta: { title: '登入', hidden: true }
  },
  {
    path: '/',
    component: Layout,
    redirect: '/banner',
    children: [
      { path: 'banner', component: () => import('../views/banner/index.vue'), meta: { title: '輪播圖管理', icon: 'Picture' } },
      { path: 'product', component: () => import('../views/product/index.vue'), meta: { title: '產品管理', icon: 'Goods' } },
      { path: 'product/category', component: () => import('../views/product/category.vue'), meta: { title: '產品分類', icon: 'PriceTag' } },
      { path: 'news', component: () => import('../views/news/index.vue'), meta: { title: '最新消息', icon: 'Document' } },
      { path: 'news/category', component: () => import('../views/news/category.vue'), meta: { title: '新聞分類', icon: 'Folder' } },
      { path: 'ai-tools', component: () => import('../views/ai-tools/index.vue'), meta: { title: 'AI 工具', icon: 'MagicStick' } },
      { path: 'usage', component: () => import('../views/usage/index.vue'), meta: { title: '用量監控', icon: 'DataAnalysis' } },
      { path: 'settings', component: () => import('../views/settings/index.vue'), meta: { title: '系統設定', icon: 'Setting' } },
    ]
  }
];
```

側邊欄選單從 `router.options.routes` 自動生成，`meta.hidden` 控制是否顯示。

### 6. 認證流程

保留現有 localStorage token 機制，用 vue-router 的 `beforeEach` 守衛替代目前 `onMounted` 中的手動跳轉：

```js
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('api_token');
  if (to.path !== '/login' && !token) {
    next('/login');
  } else if (to.path === '/login' && token) {
    next('/');
  } else {
    next();
  }
});
```

### 7. 新增依賴

```
dependencies:
  vue-router: ^4.5
  pinia: ^3
  element-plus: ^2.9
  @element-plus/icons-vue: ^2.3

devDependencies:
  unplugin-vue-components: ^28    # Element Plus 按需引入
  unplugin-auto-import: ^19       # API 自動引入
```

## Risks / Trade-offs

- **UI 風格變更** → 後台從 Tailwind 自訂風格轉為 Element Plus 風格，視覺差異較大。可透過 Element Plus 主題客製化調整。
- **前台 CSS 影響** → Element Plus 全域樣式可能影響前台。→ 後台入口獨立，確保 Element Plus 只在後台載入。
- **遷移期間功能中斷** → 核心 CRUD 邏輯保留在 service 層不變，僅重構 UI 層，降低風險。
- **Quill Editor 相容性** → `@vueup/vue-quill` 與 Element Plus 共存需測試。→ 保留為獨立元件，不依賴 Element Plus 樣式。

## Migration Plan

1. 安裝新依賴（vue-router、pinia、element-plus）
2. 建立 `resources/js/admin/` 目錄結構
3. 建立佈局元件（Layout、Sidebar、Navbar）
4. 配置 vue-router 與路由守衛
5. 配置 pinia store（user state）
6. 遷移 Login 頁面
7. 遷移核心頁面（Banner、Product、News 管理）
8. 遷移輔助頁面（AI 工具、用量監控、系統設定）
9. 更新 `vite.config.js` 與 `dashboard.blade.php`
10. 移除舊的 Dashboard.vue 與相關程式碼
