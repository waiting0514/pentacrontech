## MODIFIED Requirements

### Requirement: 功能開關
新增 AI 功能開關設定，以控制專案層級的 AI 服務存取。

#### Scenario: 設定 AI 開關
- **WHEN** 管理者更新專案設定
- **THEN** 系統 SHALL 儲存 `is_ai_enabled` (boolean) 欄位，預設為 `true`
