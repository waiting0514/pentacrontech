## 1. 套件安裝與資料庫

- [x] 1.1 安裝 spatie/laravel-permission，發佈 migration 與 config
- [x] 1.2 建立 migration 為 users 表新增 `is_active` 欄位（boolean, default true）與 `last_login_at` 欄位
- [x] 1.3 User model 加入 HasRoles trait、fillable 欄位更新

## 2. Seeder 與權限定義

- [x] 2.1 建立 RolesAndPermissionsSeeder：定義所有 permissions（product.view/create/update/delete、news.*、banner.manage、ai.use、user.manage、report.view_cost、system.manage）
- [x] 2.2 Seeder 建立角色並指派權限（super-admin bypass、manager 權限組、editor 權限組）
- [x] 2.3 Seeder 建立預設使用者（super-admin + manager）並指派角色
- [x] 2.4 在 DatabaseSeeder 中註冊 RolesAndPermissionsSeeder

## 3. 後端權限邏輯

- [x] 3.1 在 AppServiceProvider 加入 Gate::before 讓 super-admin 繞過所有檢查
- [x] 3.2 建立 EnsureUserIsActive middleware 並註冊到 auth:sanctum 路由群組
- [x] 3.3 更新 ProductController 加入 permission 檢查（destroy 需 product.delete）
- [x] 3.4 更新 NewsController 加入 permission 檢查
- [x] 3.5 更新 BannerController 加入 permission 檢查（banner.manage）
- [x] 3.6 更新 AI 相關 Controller 加入 ai.use 權限檢查
- [x] 3.7 更新 UsageController：view_cost 資料僅 report.view_cost 權限可見

## 4. 使用者管理 API

- [x] 4.1 建立 UserController（index/store/update/toggleStatus/destroy）
- [x] 4.2 建立 UserResource（回傳 id、name、email、role、permissions、is_active、last_login_at）
- [x] 4.3 更新 /api/user 端點回傳 role 與 permissions 資訊
- [x] 4.4 在 api.php 註冊 /api/users 路由群組
- [x] 4.5 登入成功時更新 last_login_at

## 5. 前端權限系統

- [x] 5.1 建立 usePermission composable（can/is 方法，super-admin bypass）
- [x] 5.2 更新 Pinia user store：login 後載入 role 與 permissions
- [x] 5.3 更新 router/index.js：路由 meta 加入 permission 欄位
- [x] 5.4 更新 router beforeEach 守衛：檢查路由 permission
- [x] 5.5 更新 Sidebar.vue：依 permission 過濾選單項目

## 6. 前端頁面調整

- [x] 6.1 建立 admin/views/users/index.vue 使用者管理頁面（el-table + el-dialog CRUD）
- [x] 6.2 更新產品管理頁面：依 permission 隱藏刪除按鈕
- [x] 6.3 更新用量監控頁面：依 report.view_cost 權限控制預算資訊顯示
- [x] 6.4 在 router 新增 /admin/users 路由（permission: user.manage）
