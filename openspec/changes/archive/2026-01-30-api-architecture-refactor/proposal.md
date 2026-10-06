## Why

目前 12 個 API Controller 各自使用 `response()->json()` 回傳不一致的格式，前端元件直接散落呼叫 axios，沒有統一的錯誤處理、回應解包、或 401 重導機制。需要建立統一的「API 協議」和前端「Service Layer」。

## What Changes

### Backend
- 建立 `ApiResponse` Trait，提供 `successResponse` / `errorResponse` 統一格式
- 配置全域 Exception Handler（bootstrap/app.php），攔截所有例外並回傳統一格式
- 建立 API Resources（ProductResource, NewsResource, BannerResource）標準化輸出
- 重構所有 12 個 API Controller 使用 Trait + Resources

### Frontend
- 建立 `resources/js/utils/http.js`（Axios instance + interceptors：解包回應、401 重導、錯誤 toast）
- 建立 `resources/js/services/` 目錄，為每個領域建立 service 檔案
- 重構所有 Vue 元件改用 service 層，不再直接使用 axios

## Capabilities

### New Capabilities
- `api-protocol`: 統一 API 回應格式（success/error JSON 結構）
- `frontend-services`: 前端集中式 service layer + axios 配置

### Modified Capabilities
- `product`: ProductController 改用 ApiResponse + ProductResource
- `banner`: BannerController 改用 ApiResponse + BannerResource
- `image-processing`: ProductImageController 改用 ApiResponse

## Impact

- 所有 API 回應格式改變（加上 success/code/message 包裝）
- 前端所有元件需配合調整資料存取方式
- 新增 Trait、Resources、Services 等檔案
- 全域 Exception Handler 影響所有 API 錯誤回應
