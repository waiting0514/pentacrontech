## Context

系統有 12 個 API Controller，全部使用 `response()->json()` 直接回傳。前端 13 個 Vue 元件各自直接呼叫 axios，沒有統一的錯誤處理。`bootstrap/app.php` 的 exception handler 目前為空。

## Goals / Non-Goals

**Goals:**
- 所有 API 回傳統一 JSON 格式（success: true/false + data/error）
- 全域攔截例外，不再需要 Controller 內的 try-catch
- 前端集中式 service layer，元件不直接碰 axios
- Axios interceptor 自動處理 401 重導和錯誤提示

**Non-Goals:**
- 不做 API 版本管理（v1/v2）
- 不做前端狀態管理（Pinia/Vuex）
- 不改變現有的 API 路由結構

## Decisions

### 1. ApiResponse Trait
建立 `App\Traits\ApiResponse`，提供兩個方法：
- `successResponse($data, $message, $code)` → `{ success: true, code, message, data }`
- `errorResponse($message, $errorCode, $httpCode, $errors)` → `{ success: false, code, error_code, message, errors }`

所有 Controller `use ApiResponse`，取代所有 `response()->json()`。

### 2. 全域 Exception Handler
在 `bootstrap/app.php` 的 `withExceptions` 中配置 `renderable`：
- `ValidationException` → 422, `VALIDATION_ERROR`
- `ModelNotFoundException` → 404, `RESOURCE_NOT_FOUND`
- `AuthenticationException` → 401, `UNAUTHENTICATED`
- `AiServiceException`（自訂）→ 400, `AI_ERROR`
- `Throwable`（兜底）→ 500, `SERVER_ERROR`（production 隱藏 trace）

### 3. API Resources
建立 `ProductResource`、`NewsResource`、`BannerResource`：
- 圖片欄位回傳完整 URL（`asset('storage/' . $path)`）
- JSON 欄位保持原樣
- 關聯資料（images, category）自動包含

### 4. 前端 Axios Instance（http.js）
建立 `resources/js/utils/http.js`：
- `baseURL: '/api'`
- Request interceptor：自動帶 Bearer token
- Response interceptor：
  - 成功：回傳 `response.data`（解包 axios wrapper）
  - 401：清 token + 重導 `/login`
  - 422：回傳 error 物件（給表單處理）
  - 其他：alert error.message

### 5. Service Layer
每個領域一個 service 檔案，匯出物件方法：
- `product.service.js`：getAll, get, create, update, delete
- `news.service.js`：getAll, get, create, update, delete
- `banner.service.js`：getAll, create, update, updateSort, delete
- `auth.service.js`：login, logout, getUser
- `ai.service.js`：chat, translate, parseImage, removeBg, generateScene
- `productCategory.service.js`：getAll, create, update, delete
- `productImage.service.js`：upload, delete, setPrimary, reorder

元件 import service 而非 axios。

### 6. 重構順序
先建基礎設施（Trait → Handler → Resources → http.js → services），再逐一重構 Controller 和 Vue 元件。這樣可以漸進式遷移，不需一次全改。

## Risks / Trade-offs

- **所有 API 回應格式改變** → 前端必須同步調整，否則資料解析會壞。建議後端和前端一起改
- **interceptor 自動解包** → `response.data` 已經是解包後的物件，元件需配合（不再需要 `.data.data`）
- **全域 Handler 遮蔽錯誤** → production 不回傳 trace 是正確的，但開發時需檢查 log
