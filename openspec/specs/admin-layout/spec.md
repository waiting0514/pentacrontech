## ADDED Requirements

### Requirement: 後台主佈局結構
系統 SHALL 提供包含左側側邊欄、頂部導航列、主內容區的三欄式佈局。

#### Scenario: 載入後台頁面
- **WHEN** 已登入使用者訪問任何後台路由
- **THEN** 頁面 SHALL 顯示左側側邊欄、頂部導航列和主內容區

### Requirement: 側邊欄選單
系統 SHALL 在左側側邊欄顯示導航選單，選單項目從路由配置自動生成。

#### Scenario: 顯示選單項目
- **WHEN** 後台佈局載入
- **THEN** 側邊欄 SHALL 顯示所有未標記 `hidden` 的路由項目，包含圖標和標題

#### Scenario: 當前路由高亮
- **WHEN** 使用者處於某個頁面
- **THEN** 側邊欄對應的選單項目 SHALL 顯示為激活狀態

### Requirement: 側邊欄收合
系統 SHALL 支援側邊欄展開/收合切換。

#### Scenario: 收合側邊欄
- **WHEN** 使用者點擊收合按鈕
- **THEN** 側邊欄 SHALL 收合為僅顯示圖標的窄版模式，主內容區自動擴展

#### Scenario: 展開側邊欄
- **WHEN** 使用者點擊展開按鈕
- **THEN** 側邊欄 SHALL 恢復為完整寬度，顯示圖標和文字

### Requirement: 頂部導航列
系統 SHALL 在頂部顯示導航列，包含側邊欄收合按鈕、使用者資訊和登出按鈕。

#### Scenario: 顯示使用者資訊
- **WHEN** 後台佈局載入
- **THEN** 頂部導航列 SHALL 顯示當前登入使用者的名稱和頭像（首字母）

#### Scenario: 登出操作
- **WHEN** 使用者點擊登出按鈕
- **THEN** 系統 SHALL 呼叫登出 API、清除 token、跳轉到登入頁
