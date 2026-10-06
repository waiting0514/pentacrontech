## ADDED Requirements

### Requirement: 路由守衛認證
系統 SHALL 使用 vue-router 的全域前置守衛驗證使用者認證狀態。

#### Scenario: 未登入訪問後台
- **WHEN** 未持有 token 的使用者訪問非登入頁路由
- **THEN** 系統 SHALL 重導向至 `/login`

#### Scenario: 已登入訪問登入頁
- **WHEN** 已持有 token 的使用者訪問 `/login`
- **THEN** 系統 SHALL 重導向至首頁 `/`

### Requirement: 登入頁面
系統 SHALL 提供使用 Element Plus 表單元件的登入頁面。

#### Scenario: 登入成功
- **WHEN** 使用者輸入正確的 email 和密碼並提交
- **THEN** 系統 SHALL 儲存 token 至 localStorage 並透過 vue-router 導航至首頁

#### Scenario: 登入失敗
- **WHEN** 使用者輸入錯誤的帳號密碼
- **THEN** 系統 SHALL 使用 Element Plus Message 顯示錯誤訊息

### Requirement: Pinia 使用者狀態管理
系統 SHALL 使用 Pinia store 管理使用者狀態（使用者資訊、token）。

#### Scenario: 載入使用者資訊
- **WHEN** 已認證使用者進入後台
- **THEN** 系統 SHALL 從 API 獲取使用者資訊並存入 Pinia store

#### Scenario: 登出清除狀態
- **WHEN** 使用者登出
- **THEN** 系統 SHALL 清除 Pinia store 中的使用者資訊和 localStorage 中的 token
