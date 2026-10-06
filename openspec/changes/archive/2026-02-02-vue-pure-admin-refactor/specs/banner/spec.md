## MODIFIED Requirements

### Requirement: Banner 管理介面
Banner 管理 SHALL 使用 Element Plus 元件替代現有 Tailwind CSS 自訂 UI。

#### Scenario: Banner 列表顯示
- **WHEN** 使用者進入 Banner 管理頁面
- **THEN** 系統 SHALL 使用 el-table 顯示 Banner 列表，包含圖片預覽、排序、狀態

#### Scenario: 新增 Banner
- **WHEN** 使用者點擊新增按鈕
- **THEN** 系統 SHALL 顯示 el-dialog 表單，支援圖片上傳和連結設定

#### Scenario: Banner 排序
- **WHEN** 使用者調整 Banner 順序
- **THEN** 系統 SHALL 呼叫排序 API 更新順序

#### Scenario: 刪除 Banner
- **WHEN** 使用者點擊刪除按鈕
- **THEN** 系統 SHALL 顯示確認對話框，確認後呼叫刪除 API
