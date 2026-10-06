## ADDED Requirements

### Requirement: 集中式 Axios 實例
應用程式 SHALL 在 `resources/js/utils/http.js` 提供配置好的 Axios 實例，包含 baseURL `/api`、自動注入 Bearer Token、回應解包 (response unwrapping)、401 重導向以及錯誤處理。

#### Scenario: 成功回應解包
- **WHEN** API 呼叫成功時
- **THEN** 攔截器 (interceptor) 直接回傳 `response.data` (從 axios wrapper 解包)

#### Scenario: 401 未授權處理
- **WHEN** API 呼叫回傳 401 時
- **THEN** 攔截器清除儲存的 token 並重導向至 `/login`

#### Scenario: 422 驗證錯誤傳遞
- **WHEN** API 呼叫回傳 422 時
- **THEN** 攔截器 reject 該錯誤物件，以便呼叫者處理表單錯誤

### Requirement: 領域服務層 (Domain Service Layer)
每個領域 SHALL 在 `resources/js/services/` 中擁有專屬的服務檔案，用於封裝 API 呼叫。Vue 元件 SHALL 匯入服務而非直接使用 Axios。

#### Scenario: 元件使用服務獲取資料
- **WHEN** Vue 元件需要獲取產品資料時
- **THEN** 它匯入 `ProductService` 並呼叫 `ProductService.getAll()`，而非 `axios.get('/api/products')`

#### Scenario: 服務處理請求格式化
- **WHEN** 帶有參數的服務方法被呼叫時
- **THEN** 服務在內部建構適當的 HTTP 請求（方法、URL、標頭、主體）