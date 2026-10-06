## ADDED Requirements

### Requirement: 多頁面路由架構
系統 SHALL 使用 vue-router 實現多頁面路由，每個管理功能對應獨立路由。

#### Scenario: 路由頁面列表
- **WHEN** 後台應用初始化
- **THEN** 系統 SHALL 註冊以下路由：`/banner`、`/product`、`/product/category`、`/news`、`/news/category`、`/ai-tools`、`/usage`、`/settings`

#### Scenario: 預設路由
- **WHEN** 使用者訪問根路徑 `/`
- **THEN** 系統 SHALL 重導向至 `/banner`

### Requirement: 路由懶載入
所有頁面元件 SHALL 使用動態 import 進行懶載入。

#### Scenario: 頁面按需載入
- **WHEN** 使用者首次導航到某個頁面
- **THEN** 該頁面的 JS chunk SHALL 才被下載和執行

### Requirement: 路由 meta 配置
每個路由 SHALL 包含 `meta` 配置，定義 `title`（頁面標題）和 `icon`（Element Plus 圖標名稱）。

#### Scenario: 頁面標題
- **WHEN** 使用者導航到某個頁面
- **THEN** 瀏覽器標題 SHALL 更新為該路由的 `meta.title`
