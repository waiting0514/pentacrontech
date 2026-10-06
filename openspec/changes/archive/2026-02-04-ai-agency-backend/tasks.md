# AI Agency Backend 實作任務

## 階段 1: 環境準備與套件安裝

### 1.1 安裝必要套件
- [ ] 安裝 Laravel Scout: `./vendor/bin/sail composer require laravel/scout`
- [ ] 安裝 Activity Log: `./vendor/bin/sail composer require spatie/laravel-activitylog`
- [ ] 安裝 OpenAI PHP SDK: `./vendor/bin/sail composer require openai-php/laravel`
- [ ] 發布 Scout 配置: `./vendor/bin/sail artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"`
- [ ] 發布 Activity Log 配置: `./vendor/bin/sail artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"`

### 1.2 環境配置
- [ ] 更新 `.env` 檔案,新增 AI API Keys:
  - `OPENAI_API_KEY`
  - `PHOTOROOM_API_KEY`
  - `CLIPDROP_API_KEY`
- [ ] 設定 Scout driver 為 `database`
- [ ] 確認 Docker Compose 的 `client_max_body_size` ≥ 20MB

---

## 階段 2: 資料庫設計

### 2.1 建立 Migrations

#### Products Table
- [ ] 建立 migration: `create_products_table`
  ```php
  Schema::create('products', function (Blueprint $table) {
      $table->id();
      $table->json('name');           // {"zh": "...", "en": "..."}
      $table->json('description');    // {"zh": "...", "en": "..."}
      $table->text('specs')->nullable();
      $table->decimal('price', 10, 2);
      $table->string('image_url')->nullable();
      $table->timestamps();
  });
  ```

#### Users Table (擴充)
- [ ] 建立 migration: `add_cost_control_to_users_table`
  ```php
  Schema::table('users', function (Blueprint $table) {
      $table->decimal('monthly_usage_usd', 10, 4)->default(0);
      $table->decimal('monthly_budget_usd', 10, 4)->default(30.00);
      $table->string('role')->default('client'); // 'admin' or 'client'
  });
  ```

#### AI Usage Logs Table
- [ ] 建立 migration: `create_ai_usage_logs_table`
  ```php
  Schema::create('ai_usage_logs', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->string('provider'); // 'openai', 'photoroom', 'clipdrop'
      $table->string('feature');  // 'text_gen', 'translation', 'vision_parse', etc.
      $table->integer('input_tokens')->nullable();
      $table->integer('output_tokens')->nullable();
      $table->decimal('cost_usd', 10, 6);
      $table->string('status'); // 'success', 'failed'
      $table->timestamps();
  });
  ```

#### Activity Log Migration
- [ ] 執行: `./vendor/bin/sail artisan migrate` (Activity Log 套件會自動建立表)

### 2.2 建立 Models

#### Product Model
- [ ] 建立 `app/Models/Product.php`
- [ ] 設定 JSON casting: `protected $casts = ['name' => 'array', 'description' => 'array'];`
- [ ] 加入 `use Searchable;` trait (Laravel Scout)

#### User Model (擴充)
- [ ] 更新 `app/Models/User.php`
- [ ] 新增 `monthly_usage_usd`, `monthly_budget_usd`, `role` 到 `$fillable`
- [ ] 加入 `use LogsActivity;` trait (Spatie Activity Log)

#### AiUsageLog Model
- [ ] 建立 `app/Models/AiUsageLog.php`
- [ ] 定義關聯: `belongsTo(User::class)`

---

## 階段 3: 後端服務層

### 3.1 AiCostControlService
- [ ] 建立 `app/Services/AiCostControlService.php`
- [ ] 實作方法:
  - `checkBudget(User $user): bool` - 檢查是否超過預算
  - `recordUsage(User $user, array $logData): void` - 記錄使用量
  - `incrementUsage(User $user, float $cost): void` - 增加使用量

### 3.2 AiTextService
- [ ] 建立 `app/Services/AiTextService.php`
- [ ] 實作方法:
  - `chat(string $context, string $userMessage, ?array $ragData = null): string`
    - 根據 context 設定不同的 System Prompt
    - 如果有 ragData,注入到 System Prompt
  - `translate(array $data, array $targetLangs): array`
    - 使用 GPT-4o-mini 的 JSON mode
    - 輸入: `['text' => '...']`
    - 輸出: `['zh' => '...', 'en' => '...', ...]`
- [ ] 所有方法都要通過 `AiCostControlService::checkBudget()`
- [ ] 記錄每次呼叫到 `ai_usage_logs`

### 3.3 AiImageService
- [ ] 建立 `app/Services/AiImageService.php`
- [ ] 實作方法:
  - `parseDmImage(UploadedFile $image): array`
    - 使用 GPT-4o Vision
    - 回傳: `['name' => '...', 'specs' => '...', 'meta_title' => '...', ...]`
  - `removeBackground(UploadedFile $image): string`
    - 呼叫 Clipdrop/Remove.bg API
    - 回傳: 處理後的圖片 URL
  - `generateScene(UploadedFile $image, string $prompt): string`
    - 呼叫 Photoroom API
    - 回傳: 生成的圖片 URL
- [ ] 所有方法都要通過 `AiCostControlService::checkBudget()`
- [ ] 記錄每次呼叫到 `ai_usage_logs`

### 3.4 RAG Service (簡化版)
- [ ] 建立 `app/Services/RagService.php`
- [ ] 實作方法:
  - `searchProducts(string $query): Collection`
    - 使用 Laravel Scout 搜尋產品
    - 回傳前 5 筆相關產品

---

## 階段 4: API Controllers

### 4.1 ProductController
- [ ] 建立 `app/Http/Controllers/Api/ProductController.php`
- [ ] 實作 RESTful API:
  - `index()` - 列表(支援搜尋)
  - `store()` - 新增
  - `show($id)` - 單筆
  - `update($id)` - 更新
  - `destroy($id)` - 刪除

### 4.2 AiTextController
- [ ] 建立 `app/Http/Controllers/Api/AiTextController.php`
- [ ] 實作端點:
  - `POST /api/ai/chat` - 情境感知聊天
    - Request: `{ "context": "product", "message": "...", "rag_query": "..." }`
  - `POST /api/ai/translate` - 一鍵翻譯
    - Request: `{ "text": "...", "target_langs": ["zh", "en"] }`

### 4.3 AiImageController
- [ ] 建立 `app/Http/Controllers/Api/AiImageController.php`
- [ ] 實作端點:
  - `POST /api/ai/parse-image` - Vision Parse
    - Request: `multipart/form-data` with `image` file
  - `POST /api/ai/remove-background` - 移除背景
  - `POST /api/ai/generate-scene` - 生成場景
    - Request: `{ "image": file, "prompt": "..." }`

### 4.4 UsageController
- [ ] 建立 `app/Http/Controllers/Api/UsageController.php`
- [ ] 實作端點:
  - `GET /api/usage/current` - 當月使用量
  - `GET /api/usage/logs` - 使用記錄(分頁)

### 4.5 Routes
- [ ] 更新 `routes/api.php`,新增所有 API 路由
- [ ] 套用 `auth:sanctum` middleware

---

## 階段 5: 前端元件 (Vue 3)

### 5.1 AiAssistant.vue
- [ ] 建立 `resources/js/components/AiAssistant.vue`
- [ ] Props:
  - `context: String` (required)
  - `initialData: Object` (optional, for RAG)
- [ ] 功能:
  - 聊天介面(訊息列表 + 輸入框)
  - 呼叫 `/api/ai/chat` API
  - 支援串流回應(可選)
  - "插入到表單" 按鈕

### 5.2 CameraUploader.vue
- [ ] 建立 `resources/js/components/CameraUploader.vue`
- [ ] 功能:
  - `<input type="file" accept="image/*" capture="environment">`
  - "掃描" 按鈕觸發 `/api/ai/parse-image`
  - 顯示解析結果的表單(可編輯)
  - "確認" 按鈕儲存產品

### 5.3 ImageEditor.vue
- [ ] 建立 `resources/js/components/ImageEditor.vue`
- [ ] Props:
  - `imageUrl: String` (required)
- [ ] 功能:
  - 顯示產品圖片
  - "移除背景" 按鈕 → 呼叫 `/api/ai/remove-background`
  - "生成場景" 按鈕 + 提示詞輸入 → 呼叫 `/api/ai/generate-scene`
  - 顯示載入狀態(Spinner)

### 5.4 整合到主應用
- [ ] 更新 `resources/js/app.js`,註冊元件
- [ ] 建立測試頁面(例如 `resources/views/test-ai.blade.php`)

---

## 階段 6: 基礎設施配置

### 6.1 Docker Compose
- [ ] 檢查 `compose.yaml`,確認 MySQL 8.0 配置
- [ ] 確認 Nginx 設定 `client_max_body_size: 20M`

### 6.2 Laravel Scheduler
- [ ] 建立 Command: `app/Console/Commands/ResetMonthlyUsage.php`
  ```php
  // 每月 1 日重置所有使用者的 monthly_usage_usd 為 0
  User::query()->update(['monthly_usage_usd' => 0]);
  ```
- [ ] 註冊到 `app/Console/Kernel.php`:
  ```php
  $schedule->command('usage:reset-monthly')->monthlyOn(1, '00:00');
  ```

### 6.3 環境變數範例
- [ ] 更新 `.env.example`,新增:
  ```
  OPENAI_API_KEY=sk-...
  PHOTOROOM_API_KEY=...
  CLIPDROP_API_KEY=...
  SCOUT_DRIVER=database
  ```

---

## 階段 7: 測試與驗證

### 7.1 單元測試
- [ ] 測試 `AiCostControlService::checkBudget()`
- [ ] 測試 `AiTextService::translate()`
- [ ] 測試 `RagService::searchProducts()`

### 7.2 功能測試
- [ ] 測試 Product CRUD API
- [ ] 測試 AI Chat API(模擬 OpenAI 回應)
- [ ] 測試成本超額時的阻擋機制

### 7.3 手動驗證
- [ ] 使用 Postman/Insomnia 測試所有 API 端點
- [ ] 測試前端元件:
  - AiAssistant 聊天功能
  - CameraUploader 拍照解析
  - ImageEditor 圖片編輯
- [ ] 驗證 Scheduler 是否正確重置使用量

### 7.4 文件更新
- [ ] 建立 `openspec/changes/ai-agency-backend/walkthrough.md`
- [ ] 更新 `openspec/specs/` (如果有主規格文件)

---

## 完成檢查清單

- [ ] 所有 Migrations 已執行
- [ ] 所有 Models 已建立並設定正確
- [ ] 所有 Services 已實作並通過測試
- [ ] 所有 API 端點已建立並可正常呼叫
- [ ] 所有前端元件已建立並可正常運作
- [ ] Docker 環境配置正確
- [ ] Scheduler 已設定並測試
- [ ] 環境變數已配置
- [ ] 測試已通過
- [ ] 文件已更新
