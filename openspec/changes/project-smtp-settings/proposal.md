## Why

目前系統寄信（詢價通知、聯絡表單）使用全域 SMTP 設定（`.env`），所有 Project 共用同一組寄件設定。在多租戶架構下，每個客戶應能使用自己的 Gmail 帳號寄信，讓收件者看到的寄件人是客戶自己的品牌信箱，而非系統統一的地址。

## What Changes

- 新增 Project 層級的 SMTP 設定欄位（host、port、encryption、username、password、from_address、from_name）
- `smtp_password` 使用 Laravel `encrypted` cast 加密儲存
- 後台專案設定頁面新增「郵件設定」區塊，含測試寄信功能
- 寄信邏輯改為依 Project SMTP 設定動態建立 Mailer，若未設定則 fallback 至全域設定
- API 回傳 Project 資料時隱藏 SMTP 密碼明文

## Capabilities

### New Capabilities
- `project-smtp`: 專案層級 SMTP 設定管理，包含欄位定義、加密儲存、動態 Mailer 建立、測試寄信端點、後台 UI

### Modified Capabilities
- `project`: 新增 SMTP 相關欄位至 Project schema（smtp_host、smtp_port、smtp_encryption、smtp_username、smtp_password、smtp_from_address、smtp_from_name）

## Impact

- **資料庫**: `projects` 表新增 7 個欄位（migration）
- **Model**: `Project.php` 更新 fillable、casts、hidden
- **API**: `ProjectController` 擴充 update 驗證規則，新增 `test-smtp` 端點
- **Service**: 新增 `ProjectMailService` 處理動態 Mailer
- **前端**: `settings/index.vue` 新增郵件設定表單區塊
- **寄信邏輯**: `InquiryController`、`ContactController` 改用 `ProjectMailService`
