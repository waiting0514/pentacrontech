## ADDED Requirements

### Requirement: 統一 API 成功回應格式
所有 API 端點 SHALL 以此格式回傳成功回應：`{ success: true, code: <httpCode>, message: <string>, data: <object|array> }`。

#### Scenario: 成功取得資料
- **WHEN** GET 請求成功
- **THEN** 回應內容包含 `success: true`、`code: 200`，且資料位於 `data` 欄位

#### Scenario: 成功建立資源
- **WHEN** POST 請求建立資源
- **THEN** 回應內容包含 `success: true`、`code: 201`，且 `data` 中包含已建立的資源

### Requirement: 統一 API 錯誤回應格式
所有 API 錯誤回應 SHALL 回傳：`{ success: false, code: <httpCode>, error_code: <string>, message: <string>, errors: <object|null> }`。

#### Scenario: 驗證錯誤
- **WHEN** 請求未通過驗證
- **THEN** 回應回傳 code 422、error_code `VALIDATION_ERROR`，並在 `errors` 物件中包含欄位層級的錯誤

#### Scenario: 找不到資源
- **WHEN** 請求引用了不存在的資源
- **THEN** 回應回傳 code 404、error_code `RESOURCE_NOT_FOUND`

#### Scenario: 未經認證的請求
- **WHEN** 請求缺乏有效認證
- **THEN** 回應回傳 code 401、error_code `UNAUTHENTICATED`

#### Scenario: 生產環境伺服器錯誤
- **WHEN** 生產環境發生未處理的例外狀況
- **THEN** 回應回傳 code 500、error_code `SERVER_ERROR`，且不暴露堆疊追蹤 (stack trace)

### Requirement: 全域例外處理器
應用程式 SHALL 在 `bootstrap/app.php` 中配置全域例外處理器，攔截所有例外狀況並回傳統一錯誤格式。除非需要特定的復原邏輯，否則控制器 SHALL NOT 使用 try-catch 區塊。

#### Scenario: 自動捕捉例外
- **WHEN** API 路由中拋出任何例外
- **THEN** 全域處理器攔截該例外並回傳適當的錯誤回應格式

### Requirement: ApiResponse Trait
`ApiResponse` trait SHALL 提供 `successResponse` 和 `errorResponse` 方法供所有 API 控制器使用。

#### Scenario: 控制器使用 trait 回傳成功
- **WHEN** 控制器動作成功完成
- **THEN** 呼叫 `$this->successResponse($data, $message, $code)` 以回傳統一格式

#### Scenario: 控制器使用 trait 回傳錯誤
- **WHEN** 控制器需要回傳業務邏輯錯誤
- **THEN** 呼叫 `$this->errorResponse($message, $errorCode, $httpCode)` 以回傳統一格式