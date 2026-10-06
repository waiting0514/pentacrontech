## ADDED Requirements

### Requirement: SMTP 欄位定義
Project schema SHALL 包含以下 SMTP 相關欄位：

| 欄位 | 類型 | 說明 |
|---|---|---|
| `smtp_host` | string, nullable | SMTP 主機，預設 `smtp.gmail.com` |
| `smtp_port` | integer, nullable | 連接埠，預設 `587` |
| `smtp_encryption` | string, nullable | 加密方式（`tls` / `ssl`） |
| `smtp_username` | string, nullable | SMTP 帳號 |
| `smtp_password` | text, nullable, encrypted | 應用程式密碼（加密儲存） |
| `smtp_from_address` | string, nullable | 寄件人地址 |
| `smtp_from_name` | string, nullable | 寄件人名稱 |

#### Scenario: 新增 SMTP 欄位至 projects 表
- **WHEN** 執行 migration
- **THEN** `projects` 表 SHALL 新增上述 7 個欄位，所有欄位皆為 nullable
