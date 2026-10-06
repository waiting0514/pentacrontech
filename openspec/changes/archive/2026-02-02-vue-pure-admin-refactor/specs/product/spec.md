## MODIFIED Requirements

### Requirement: 產品管理介面
產品管理 SHALL 使用 Element Plus 表格（el-table）和表單（el-form）元件替代現有 Tailwind CSS 自訂 UI。

#### Scenario: 產品列表顯示
- **WHEN** 使用者進入產品管理頁面
- **THEN** 系統 SHALL 使用 el-table 顯示產品列表，包含搜尋、分頁功能

#### Scenario: 新增產品
- **WHEN** 使用者點擊新增按鈕
- **THEN** 系統 SHALL 顯示 el-dialog 表單，包含產品名稱、分類、描述、圖片上傳等欄位

#### Scenario: 編輯產品
- **WHEN** 使用者點擊產品列表中的編輯按鈕
- **THEN** 系統 SHALL 顯示預填資料的 el-dialog 表單

#### Scenario: 刪除產品
- **WHEN** 使用者點擊刪除按鈕
- **THEN** 系統 SHALL 顯示 ElMessageBox 確認對話框，確認後呼叫刪除 API

### Requirement: 產品分類管理介面
產品分類管理 SHALL 使用 Element Plus 元件，作為獨立路由頁面。

#### Scenario: 分類 CRUD
- **WHEN** 使用者進入產品分類頁面
- **THEN** 系統 SHALL 使用 el-table 顯示分類列表，支援新增、編輯、刪除操作
