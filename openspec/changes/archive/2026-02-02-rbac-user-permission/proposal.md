## Why

目前系統只有單一管理員帳號，無法區分開發者、客戶管理者與編輯者的權限。需要導入 RBAC 角色權限系統，採用「程式碼檢查 Permission、管理介面指派 Role」策略，讓系統具備未來擴展細粒度權限的彈性。

## What Changes

- 新增 `spatie/laravel-permission` 套件
- 定義細粒度權限清單：`product.*`、`news.*`、`banner.manage`、`ai.use`、`user.manage`、`report.view_cost`、`system.manage`
- 定義三種預設角色：`super-admin`（全權限 via Gate::before）、`manager`（管理者權限組）、`editor`（內容編輯權限組）
- RolesAndPermissionsSeeder 建立角色與權限對應
- 新增 `is_active` 欄位到 users 表
- 所有 Controller/Policy 改為檢查 permission（非 role）
- UserController API：CRUD 使用者、切換啟用狀態
- `/api/user` 回傳增加 role 與 permissions 陣列
- 前端 `usePermission` composable 依 permission 控制 UI
- 後台新增使用者管理頁面（需 `user.manage` 權限）
- 後台選單與按鈕依 permission 顯示/隱藏
- **BREAKING** 現有 API 回應格式變更、刪除操作新增權限檢查

## Capabilities

### New Capabilities
- `rbac`: 角色權限系統核心（spatie 整合、Seeder、Gate::before、Permission 定義）
- `user-management`: 使用者管理（CRUD、停用/啟用、角色指派、UserPolicy）
- `frontend-permission`: 前端權限控制（usePermission composable、UI 條件渲染、選單過濾）

### Modified Capabilities
_(無修改現有 capability 的需求級變更)_

## Impact

- **後端依賴**: 新增 `spatie/laravel-permission`
- **資料庫**: spatie permission 相關 migration、users 表新增 `is_active`、seed 預設角色/權限/使用者
- **API**: `/api/user` 回傳格式變更、新增 `/api/users` CRUD 端點、所有現有 controller 加入 permission 檢查
- **前端**: 新增 `/admin/users` 頁面、路由新增權限 meta、選單依權限過濾
- **認證流程**: 登入後需載入使用者權限、middleware 需驗證 is_active
