## MODIFIED Requirements

### Requirement: Banner 管理介面
Banner 管理 SHALL 使用 Element Plus 元件替代現有 Tailwind CSS 自訂 UI。圖片上傳與文字輸入元件必須支援多語系分頁切換。

#### Scenario: Banner 列表顯示
- **WHEN** 使用者進入 Banner 管理頁面
- **THEN** 系統 SHALL 使用 el-table 顯示 Banner 列表，包含當前語系的圖片預覽、標題、排序與狀態

#### Scenario: 新增 Banner
- **WHEN** 使用者點擊新增按鈕
- **THEN** 系統 SHALL 顯示支援多語系分頁的 el-dialog 表單，允許針對各語系上傳圖片與設定連結

#### Scenario: 智慧圖片回退 (Smart Image Fallback)
- **WHEN** 特定語系的圖片欄位留空且該 Banner 被渲染時
- **THEN** 系統 SHALL 自動回退讀取並顯示「預設語系」的圖片路徑

#### Scenario: Banner 排序
- **WHEN** 使用者調整 Banner 順序
- **THEN** 系統 SHALL 呼叫排序 API 更新順序，此變更對所有語系同步生效

#### Scenario: 刪除 Banner
- **WHEN** 使用者點擊刪除按鈕
- **THEN** 系統 SHALL 顯示確認對話框，確認後呼叫刪除 API
