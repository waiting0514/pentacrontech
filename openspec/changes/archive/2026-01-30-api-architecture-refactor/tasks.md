## 1. Backend 基礎設施

- [x] 1.1 建立 `App\Traits\ApiResponse` trait（successResponse, errorResponse）
- [x] 1.2 配置 `bootstrap/app.php` 全域 Exception Handler（ValidationException, ModelNotFoundException, AuthenticationException, Throwable）
- [x] 1.3 建立 `ProductResource`（含圖片完整 URL、images 關聯）
- [x] 1.4 建立 `NewsResource`
- [x] 1.5 建立 `BannerResource`（含圖片完整 URL）

## 2. Backend Controller 重構

- [x] 2.1 重構 `ProductController`：use ApiResponse + ProductResource
- [x] 2.2 重構 `ProductImageController`：use ApiResponse
- [x] 2.3 重構 `ProductCategoryController`：use ApiResponse
- [x] 2.4 重構 `NewsController`：use ApiResponse + NewsResource
- [x] 2.5 重構 `NewsCategoryController`：use ApiResponse
- [x] 2.6 重構 `BannerController`：use ApiResponse + BannerResource
- [x] 2.7 重構 `AuthController`：use ApiResponse
- [x] 2.8 重構 `ProjectController`：use ApiResponse
- [x] 2.9 重構 `AiTextController`：use ApiResponse
- [x] 2.10 重構 `AiImageController`：use ApiResponse
- [x] 2.11 重構 `UsageController`：use ApiResponse
- [x] 2.12 重構 `ImageUploadController`：use ApiResponse

## 3. Frontend 基礎設施

- [x] 3.1 建立 `resources/js/services/http.js`（Axios instance + interceptors）
- [x] 3.2 建立 `resources/js/services/productService.js`
- [x] 3.3 建立 `resources/js/services/productCategoryService.js`
- [x] 3.4 建立 productImage 方法整合至 productService.js
- [x] 3.5 建立 `resources/js/services/newsService.js`
- [x] 3.6 建立 `resources/js/services/newsCategoryService.js`
- [x] 3.7 建立 `resources/js/services/bannerService.js`
- [x] 3.8 建立 `resources/js/services/authService.js`
- [x] 3.9 建立 `resources/js/services/aiService.js`
- [x] 3.10 建立 `resources/js/services/projectService.js`
- [x] 3.11 建立 `resources/js/services/usageService.js`
- [x] 3.12 建立 `resources/js/services/index.js`（統一匯出）

## 4. Frontend 元件重構

- [x] 4.1 重構 `ProductManager.vue`：改用 productService + productCategoryService
- [x] 4.2 重構 `ProductCategoryManager.vue`：改用 productCategoryService
- [x] 4.3 重構 `NewsManager.vue`：改用 newsService + newsCategoryService
- [x] 4.4 重構 `NewsCategoryManager.vue`：改用 newsCategoryService
- [x] 4.5 重構 `BannerManager.vue`：改用 bannerService
- [x] 4.6 重構 `AiAssistant.vue`：改用 aiService
- [x] 4.7 重構 `UsageMonitor.vue`：改用 usageService
- [x] 4.8 重構 `SystemSettings.vue`：改用 projectService
- [x] 4.9 重構 `Login.vue`：改用 authService
- [x] 4.10 重構 `Dashboard.vue`：改用 authService（user/logout）
- [x] 4.11 重構 `ImageEditor.vue`：改用 aiService
- [x] 4.12 重構 `CameraUploader.vue`：改用 aiService
