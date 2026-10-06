## Context

系統目前使用 `.env` 全域 SMTP 設定寄信，`InquiryController` 透過 `Mail::to()` 寄送詢價通知，`ContactController` 的寄信功能尚未啟用（TODO）。在多租戶架構下，每個 Project 需要獨立的 SMTP 設定，讓寄件人顯示客戶自己的品牌信箱。

## Goals / Non-Goals

**Goals:**
- 每個 Project 可獨立設定 SMTP 連線資訊（主要支援 Gmail）
- SMTP 密碼加密儲存，API 不洩漏明文
- 後台提供測試寄信功能，驗證設定是否正確
- 未設定 SMTP 的 Project 自動 fallback 至全域設定

**Non-Goals:**
- 不支援 OAuth2 認證（僅支援 Gmail 應用程式密碼）
- 不做郵件模板管理系統
- 不做寄信佇列或排程寄信
- 不支援多個 SMTP 帳號切換

## Decisions

### 1. SMTP 欄位直接放在 projects 表

**選擇**: 在 `projects` 表新增 7 個欄位
**替代方案**: 獨立 `project_smtp_settings` 表
**理由**: SMTP 設定與 Project 是 1:1 關係，欄位數量少（7 個），不需要額外的表和關聯。與現有的 `recaptcha_site_key`、`recaptcha_secret_key` 等安全設定保持一致的存放模式。

### 2. 密碼加密使用 Laravel encrypted cast

**選擇**: `'smtp_password' => 'encrypted'`
**替代方案**: 自行實作 AES 加密、使用 vault 服務
**理由**: Laravel 內建機制，使用 `APP_KEY` + AES-256-CBC，零額外配置。對此規模的應用足夠安全，且與框架慣例一致。

### 3. 動態 Mailer 使用 Laravel Mail Manager

**選擇**: 透過 `Mail::mailer()` 搭配 runtime config 建立動態 mailer
**做法**:
```php
// 在 AppServiceProvider 或獨立 Service 中
config(['mail.mailers.project_' . $project->id => [
    'transport' => 'smtp',
    'host' => $project->smtp_host,
    'port' => $project->smtp_port,
    'encryption' => $project->smtp_encryption,
    'username' => $project->smtp_username,
    'password' => $project->smtp_password,
]]);

Mail::mailer('project_' . $project->id)
    ->to($recipient)
    ->send($mailable);
```
**替代方案**: 直接建立 Symfony EsmtpTransport
**理由**: 使用 Laravel 原生 Mail Manager API，維持一致性，可直接使用所有 Mailable 功能（markdown template 等），不需要繞過框架。

### 4. API 密碼欄位處理策略

**選擇**: ProjectResource 中 `smtp_password` 回傳布林值（`has_smtp_password`），表示是否已設定
**更新邏輯**: 前端送空字串或 null 表示不修改密碼，送非空值表示更新
**理由**: 避免密碼在 API 來回傳輸，同時讓前端知道是否已設定密碼以顯示正確的 UI 狀態。

### 5. 測試寄信端點

**選擇**: `POST /api/projects/{id}/test-smtp`
**行為**: 使用該 Project 的 SMTP 設定，發送一封測試信到 `contact_email`
**回應**: 成功或失敗訊息（含錯誤詳情，如認證失敗、連線逾時等）
**理由**: 讓管理者在儲存設定後立即驗證，避免到實際詢價時才發現設定錯誤。

## Risks / Trade-offs

- **APP_KEY 遺失** → 所有加密的 SMTP 密碼無法解密。緩解：部署文件強調 APP_KEY 備份。
- **Gmail 應用程式密碼被撤銷** → 寄信靜默失敗。緩解：測試寄信功能可主動檢測；寄信失敗時記錄 log。
- **連線逾時拖慢請求** → SMTP 連線可能需 5-10 秒。緩解：目前不做佇列（Non-Goal），但 try-catch 確保不影響主流程回應。未來可升級為 queue。
- **多租戶 config 污染** → runtime 設定 `config()` 在同一 request 中是全域的。緩解：使用 `project_{id}` 作為 mailer 名稱隔離，每個 Project 有獨立的 mailer config key。
