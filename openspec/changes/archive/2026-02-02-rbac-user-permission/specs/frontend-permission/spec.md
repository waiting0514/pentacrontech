## ADDED Requirements

### Requirement: usePermission composable
前端 SHALL 提供 `usePermission()` composable，從 Pinia user store 讀取權限。

#### Scenario: 檢查權限
- **WHEN** 呼叫 `can('product.delete')`
- **THEN** SHALL 回傳使用者是否擁有該 permission 的 boolean

#### Scenario: 檢查角色
- **WHEN** 呼叫 `is('manager')`
- **THEN** SHALL 回傳使用者是否為該角色的 boolean

#### Scenario: super-admin 全權限
- **WHEN** 使用者角色為 super-admin 且呼叫 `can()` 任何權限
- **THEN** SHALL 永遠回傳 true

### Requirement: 選單權限過濾
側邊欄選單 SHALL 依使用者 permission 過濾顯示項目。

#### Scenario: Editor 看不到使用者管理
- **WHEN** editor 登入後台
- **THEN** 側邊欄 SHALL NOT 顯示「使用者管理」選單

#### Scenario: Editor 看不到用量監控預算
- **WHEN** editor 進入用量監控頁面
- **THEN** 頁面 SHALL NOT 顯示預算金額相關資訊

### Requirement: 按鈕權限控制
操作按鈕 SHALL 依 permission 顯示或隱藏。

#### Scenario: Editor 看不到產品刪除按鈕
- **WHEN** editor 進入產品管理頁面
- **THEN** 產品列表 SHALL NOT 顯示刪除按鈕

### Requirement: 使用者管理頁面
後台 SHALL 提供使用者管理頁面，路由為 `/admin/users`。

#### Scenario: 使用者管理列表
- **WHEN** 擁有 `user.manage` 權限的使用者進入使用者管理頁面
- **THEN** 頁面 SHALL 以 el-table 顯示使用者列表，包含 Name、Email、Role（Badge）、Status（Active/Inactive）、Last Login 欄位

#### Scenario: 新增使用者
- **WHEN** 使用者點擊新增按鈕
- **THEN** 頁面 SHALL 顯示表單，可輸入 name、email、password 並選擇角色（manager/editor）
