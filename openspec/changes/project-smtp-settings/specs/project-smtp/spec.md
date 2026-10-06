## ADDED Requirements

### Requirement: SMTP 設定儲存
系統 SHALL 允許管理者為每個 Project 設定獨立的 SMTP 連線資訊，包含 host、port、encryption、username、password、from_address、from_name。`smtp_password` SHALL 使用 Laravel `encrypted` cast 加密儲存。

#### Scenario: 儲存完整 SMTP 設定
- **WHEN** 管理者在後台填寫所有 SMTP 欄位並儲存
- **THEN** 系統 SHALL 將設定儲存至 `projects` 表，`smtp_password` 以加密形式儲存

#### Scenario: 部分更新不覆蓋密碼
- **WHEN** 管理者更新 SMTP 設定但未填寫密碼欄位（空值或 null）
- **THEN** 系統 SHALL 保留原有的 `smtp_password`，不進行覆蓋

### Requirement: SMTP 密碼 API 安全
系統 SHALL 在 API 回傳 Project 資料時，不回傳 `smtp_password` 明文。應以 `has_smtp_password`（boolean）欄位表示是否已設定密碼。

#### Scenario: API 回傳隱藏密碼
- **WHEN** API 回傳 Project 資料
- **THEN** 回應中 SHALL 包含 `has_smtp_password: true/false`，且 SHALL NOT 包含 `smtp_password` 明文

### Requirement: 動態 Mailer 建立
系統 SHALL 根據 Project 的 SMTP 設定動態建立 Mailer 實例。若 Project 未設定 SMTP，SHALL fallback 至全域 `.env` 設定。

#### Scenario: 使用 Project SMTP 寄信
- **WHEN** 系統為已設定 SMTP 的 Project 寄信
- **THEN** SHALL 使用該 Project 的 SMTP 設定建立獨立 Mailer，寄件人顯示為 `smtp_from_name <smtp_from_address>`

#### Scenario: Fallback 至全域設定
- **WHEN** 系統為未設定 SMTP 的 Project 寄信
- **THEN** SHALL 使用全域 `.env` 的 SMTP 設定寄信

### Requirement: 測試寄信
系統 SHALL 提供測試寄信端點，讓管理者驗證 SMTP 設定是否正確。

#### Scenario: 測試寄信成功
- **WHEN** 管理者對 `POST /api/projects/{id}/test-smtp` 發送請求
- **THEN** 系統 SHALL 使用該 Project 的 SMTP 設定發送測試信至 `contact_email`，並回傳成功訊息

#### Scenario: 測試寄信失敗
- **WHEN** SMTP 設定錯誤（認證失敗、連線逾時等）
- **THEN** 系統 SHALL 回傳失敗訊息，包含錯誤描述

#### Scenario: 未設定 SMTP 時測試
- **WHEN** Project 尚未設定 SMTP 且管理者嘗試測試寄信
- **THEN** 系統 SHALL 回傳錯誤，提示需先設定 SMTP

### Requirement: 後台 SMTP 設定 UI
後台專案設定頁面 SHALL 包含「郵件設定」區塊，提供 SMTP 相關欄位的輸入介面與測試寄信按鈕。

#### Scenario: 顯示郵件設定表單
- **WHEN** 管理者進入專案設定頁面
- **THEN** SHALL 顯示 SMTP 主機、連接埠、加密方式、帳號、密碼、寄件人地址、寄件人名稱等欄位

#### Scenario: 密碼欄位顯示狀態
- **WHEN** Project 已設定 SMTP 密碼
- **THEN** 密碼欄位 SHALL 顯示 placeholder 提示已設定，不顯示明文

#### Scenario: 測試寄信按鈕
- **WHEN** 管理者點擊測試寄信按鈕
- **THEN** SHALL 呼叫測試寄信 API 並顯示成功或失敗結果

### Requirement: SMTP 欄位驗證
系統 SHALL 對 SMTP 設定欄位進行格式驗證。

#### Scenario: 驗證規則
- **WHEN** 管理者提交 SMTP 設定
- **THEN** 系統 SHALL 驗證：`smtp_host` 為有效主機名稱、`smtp_port` 為整數（常見值 587/465）、`smtp_encryption` 為 `tls` 或 `ssl`、`smtp_from_address` 為有效 email 格式
