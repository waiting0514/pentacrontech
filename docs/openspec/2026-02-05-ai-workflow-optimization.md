# AI Workflow Optimization - Multi-Image Batch Analysis

**Document Version**: 1.0.0
**Created**: 2026-02-05
**Author**: Senior AI Full-Stack Engineer
**Status**: Specification Draft

---

## 1. Overview

### 1.1 Objective

擴充現有 AI 圖片工具，支援**多圖批次解析模式 (Multi-Image Batch Analysis)**。使用者可一次上傳多張圖片，系統將其作為單一情境 (Single Context) 發送給 AI 視覺模型（Gemini Pro Vision 或 GPT-4o），實現關聯性分析或綜合比較。

### 1.2 Key Benefits

- **關聯性分析**: AI 可分析多張圖片間的關係、差異或共同特徵
- **效率提升**: 一次批次處理多張圖片，減少使用者操作步驟
- **情境理解**: AI 能夠在多圖情境下做出更準確的判斷
- **成本優化**: 單次 API 呼叫處理多圖，相比多次呼叫更經濟

### 1.3 Use Cases

- **產品系列分析**: 上傳同系列多款產品圖片，AI 分析規格差異與定位
- **比較分析**: 比較不同產品的外觀、規格、特徵
- **場景理解**: 分析產品在不同場景、角度下的展示效果
- **品質檢測**: 批次檢測產品圖片品質、一致性

---

## 2. Frontend UI/UX Specifications

### 2.1 Upload Component Enhancement

#### 2.1.1 Multiple File Selection

**Requirements**:
- `<input type="file">` 必須支援 `multiple` 屬性
- 點擊上傳按鈕可一次選取多個檔案
- 支援的格式: JPG, PNG, WEBP
- 單檔案大小限制: 10MB
- 總檔案數量限制: **5-10 張** (可配置)

**Implementation**:
```html
<input
  type="file"
  multiple
  accept="image/jpeg,image/png,image/webp"
  @change="handleMultipleFileSelect"
/>
```

#### 2.1.2 Drag & Drop Zone

**Requirements**:
- 實作拖曳上傳區域 (Drag & Drop Zone)
- 視覺回饋: 拖曳時顯示高亮邊框或遮罩
- 支援一次拖入多個檔案
- 檔案類型驗證: 拒絕非圖片檔案

**Visual States**:
- **Default**: 顯示上傳圖示與提示文字
- **Drag Over**: 藍色邊框高亮，背景色變化
- **Uploading**: 顯示進度指示器
- **Error**: 紅色邊框，顯示錯誤訊息

**Implementation Pattern**:
```javascript
const handleDrop = (event) => {
  event.preventDefault();
  const files = Array.from(event.dataTransfer.files);
  validateAndAddFiles(files);
};

const validateAndAddFiles = (files) => {
  const validFiles = files.filter(file => {
    // Validate file type
    if (!file.type.startsWith('image/')) {
      ElMessage.warning(`${file.name} is not an image`);
      return false;
    }
    // Validate file size
    if (file.size > 10 * 1024 * 1024) {
      ElMessage.warning(`${file.name} exceeds 10MB`);
      return false;
    }
    return true;
  });

  // Check total count limit
  if (imageFiles.value.length + validFiles.length > MAX_IMAGES) {
    ElMessage.error(`Maximum ${MAX_IMAGES} images allowed`);
    return;
  }

  addFiles(validFiles);
};
```

### 2.2 Preview Grid Layout

#### 2.2.1 Grid Display

**Requirements**:
- 使用 CSS Grid 或 Flexbox 實現響應式網格佈局
- 每張圖片顯示為縮圖卡片 (Thumbnail Card)
- 卡片尺寸: **120px × 120px** (固定比例)
- 間距: 12px
- 支援響應式佈局 (RWD)

**Layout Specifications**:
```css
.preview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 12px;
  padding: 16px;
  max-height: 400px;
  overflow-y: auto;
}
```

#### 2.2.2 Thumbnail Card Components

**Card Structure**:
```
┌─────────────────┐
│  [Preview Img]  │  ← 120×120px thumbnail
│                 │
│  [X] Remove     │  ← Remove button (top-right)
│  [#] Order      │  ← Order number badge (top-left)
│  [Status Badge] │  ← Status indicator (bottom)
└─────────────────┘
```

**Card States**:
- **Pending**: 灰色邊框，等待解析
- **Uploading**: 進度條覆蓋
- **Ready**: 綠色邊框，準備送出
- **Error**: 紅色邊框，顯示錯誤圖示

**Implementation**:
```vue
<template>
  <div class="preview-grid">
    <div
      v-for="(image, index) in imageFiles"
      :key="image.id"
      :class="['thumbnail-card', `status-${image.status}`]"
    >
      <!-- Order Badge -->
      <span class="order-badge">{{ index + 1 }}</span>

      <!-- Preview Image -->
      <el-image
        :src="image.preview"
        fit="cover"
        class="thumbnail-image"
      />

      <!-- Remove Button -->
      <el-button
        type="danger"
        :icon="Close"
        circle
        size="small"
        class="remove-btn"
        @click="removeImage(image.id)"
      />

      <!-- Status Badge -->
      <el-tag
        :type="getStatusType(image.status)"
        size="small"
        class="status-badge"
      >
        {{ image.status }}
      </el-tag>
    </div>
  </div>
</template>
```

### 2.3 Validation & Limitations

#### 2.3.1 Frontend Validations

**File Type Validation**:
```javascript
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
const isValidType = (file) => ALLOWED_TYPES.includes(file.type);
```

**File Size Validation**:
```javascript
const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
const MAX_TOTAL_SIZE = 50 * 1024 * 1024; // 50MB total
```

**Count Validation**:
```javascript
const MIN_IMAGES = 2; // Minimum for batch analysis
const MAX_IMAGES = 10; // Maximum to avoid payload too large
```

#### 2.3.2 User Feedback

**Validation Messages**:
- ✅ 成功: "已選取 X 張圖片"
- ⚠️ 警告: "檔案 {filename} 超過 10MB 限制"
- ❌ 錯誤: "最多只能上傳 10 張圖片"
- ❌ 錯誤: "總檔案大小超過 50MB 限制"
- ⚠️ 警告: "批次分析需要至少 2 張圖片"

### 2.4 Action Controls

**Control Bar Layout**:
```
┌──────────────────────────────────────────────────────┐
│  [📊 X 張圖片]  [💰 預估費用: $X.XX]  [🔄 Reset]  [🚀 Batch Analyze]  │
└──────────────────────────────────────────────────────┘
```

**Button States**:
- **Batch Analyze Button**:
  - Disabled: 圖片數量 < 2
  - Enabled: 2 ≤ 圖片數量 ≤ 10
  - Loading: 分析進行中，顯示旋轉圖示

**Cost Estimation**:
- GPT-4o Vision: ~$0.03 per image
- Gemini Pro Vision: ~$0.025 per image
- Display formula: `總費用 = 圖片數量 × 單價 × 1.2` (含 buffer)

---

## 3. API Data Structure

### 3.1 Request Schema Changes

#### 3.1.1 Old Schema (Single Image)

```json
{
  "image": "data:image/jpeg;base64,/9j/4AAQSkZJRg...",
  "locale": "zh-TW",
  "context": "product"
}
```

#### 3.1.2 New Schema (Multiple Images)

```json
{
  "images": [
    "data:image/jpeg;base64,/9j/4AAQSkZJRg...",
    "data:image/jpeg;base64,iVBORw0KGgoAAAANSUhEUg...",
    "data:image/png;base64,iVBORw0KGgoAAAANSUhEUg..."
  ],
  "locale": "zh-TW",
  "context": "product",
  "analysis_type": "batch_comparison", // New field
  "prompt_template": "compare_products" // Optional
}
```

**New Fields**:
- `images` (array): Base64 編碼的圖片陣列
- `analysis_type` (string): 分析類型
  - `batch_comparison`: 批次比較分析
  - `series_analysis`: 系列產品分析
  - `quality_check`: 品質檢測
  - `general`: 一般綜合分析
- `prompt_template` (string, optional): 預定義提示詞模板

### 3.2 Response Schema

```json
{
  "success": true,
  "code": 200,
  "message": "Batch analysis completed",
  "data": {
    "analysis_type": "batch_comparison",
    "images_processed": 5,
    "results": {
      "summary": "分析了 5 款產品，包含 3 款筆記型電腦和 2 款平板電腦...",
      "individual_analyses": [
        {
          "image_index": 0,
          "name": "MacBook Pro 14\"",
          "specs": "M3 Pro 晶片，16GB RAM",
          "description": "專業級筆記型電腦...",
          "meta_title": "MacBook Pro 14\" - 專業創作者首選",
          "meta_keywords": "MacBook,筆電,M3,蘋果"
        },
        // ... more individual results
      ],
      "comparative_insights": [
        "產品 1 和產品 2 均為高階筆電，主要差異在處理器規格",
        "產品 3-5 屬於輕薄系列，適合行動辦公"
      ],
      "recommendations": {
        "categorization": ["筆記型電腦", "平板電腦"],
        "suggested_grouping": [
          { "group": "高階工作站", "products": [0, 1] },
          { "group": "輕薄行動", "products": [2, 3, 4] }
        ]
      }
    },
    "metadata": {
      "model_used": "gpt-4o-2024-11-20",
      "total_tokens": 12500,
      "cost_usd": 0.15,
      "processing_time_ms": 4500
    }
  }
}
```

### 3.3 Error Responses

**Payload Too Large**:
```json
{
  "success": false,
  "code": 413,
  "message": "Payload too large",
  "data": {
    "error_type": "PAYLOAD_TOO_LARGE",
    "current_size_mb": 52.3,
    "max_size_mb": 50.0,
    "suggestion": "Please reduce image count or compress images"
  }
}
```

**Token Limit Exceeded**:
```json
{
  "success": false,
  "code": 422,
  "message": "Token limit exceeded",
  "data": {
    "error_type": "TOKEN_LIMIT_EXCEEDED",
    "estimated_tokens": 150000,
    "max_tokens": 128000,
    "suggestion": "Please reduce image resolution or count"
  }
}
```

---

## 4. AI Service Layer Logic

### 4.1 Multi-Modal Message Construction

#### 4.1.1 OpenAI GPT-4o Format

**Message Structure**:
```javascript
const constructGPT4oPayload = (images, prompt, analysisType) => {
  return {
    model: "gpt-4o-2024-11-20",
    messages: [
      {
        role: "system",
        content: getSystemPrompt(analysisType)
      },
      {
        role: "user",
        content: [
          {
            type: "text",
            text: prompt
          },
          ...images.map((imageBase64, index) => ({
            type: "image_url",
            image_url: {
              url: imageBase64,
              detail: "high" // or "low" for faster processing
            }
          }))
        ]
      }
    ],
    max_tokens: 4096,
    temperature: 0.7
  };
};
```

**System Prompts by Analysis Type**:
```javascript
const SYSTEM_PROMPTS = {
  batch_comparison: `你是專業的產品分析師。使用者將提供多張產品圖片，請進行以下分析：
1. 辨識每張圖片中的產品名稱、規格、特徵
2. 比較產品間的差異、相似處、定位
3. 提供分類建議與分組策略
4. 給出 SEO 優化建議

請以結構化 JSON 格式回覆。`,

  series_analysis: `你是產品系列規劃專家。分析提供的產品圖片，找出：
1. 系列產品的共同設計語言
2. 各產品的市場定位與差異化
3. 產品線的完整性與缺口
4. 命名與分類建議`,

  quality_check: `你是品質檢測專家。檢查圖片品質並評估：
1. 圖片解析度、清晰度、光線
2. 產品擺放角度、構圖、背景
3. 色彩一致性、品牌形象呈現
4. 是否符合電商展示標準`,

  general: `你是 AI 視覺助手。分析提供的圖片並提供綜合洞察。`
};
```

#### 4.1.2 Google Gemini Pro Vision Format

**Message Structure**:
```javascript
const constructGeminiPayload = (images, prompt, analysisType) => {
  return {
    contents: [
      {
        role: "user",
        parts: [
          { text: getSystemPrompt(analysisType) },
          { text: prompt },
          ...images.map(imageBase64 => ({
            inline_data: {
              mime_type: detectMimeType(imageBase64),
              data: imageBase64.split(',')[1] // Remove data URL prefix
            }
          }))
        ]
      }
    ],
    generationConfig: {
      temperature: 0.7,
      maxOutputTokens: 8192
    }
  };
};
```

### 4.2 Backend Service Implementation

**Service File**: `app/Services/AiImageService.php`

#### 4.2.1 New Method: Batch Parse Images

```php
/**
 * Parse multiple images in a single API call
 *
 * @param array $images Array of uploaded files or base64 strings
 * @param string $locale User language preference
 * @param string $analysisType Type of analysis to perform
 * @return array Parsed results with comparative insights
 */
public function parseImageBatch(array $images, string $locale = 'zh-TW', string $analysisType = 'batch_comparison'): array
{
    // Validate image count
    if (count($images) < 2) {
        throw new \InvalidArgumentException('Batch analysis requires at least 2 images');
    }
    if (count($images) > 10) {
        throw new \InvalidArgumentException('Maximum 10 images allowed per batch');
    }

    // Convert images to base64 if needed
    $imageContents = [];
    $totalSize = 0;

    foreach ($images as $image) {
        if ($image instanceof \Illuminate\Http\UploadedFile) {
            $content = base64_encode(file_get_contents($image->getRealPath()));
            $size = $image->getSize();
        } else {
            $content = $image; // Already base64
            $size = strlen($content);
        }

        $imageContents[] = $content;
        $totalSize += $size;
    }

    // Check total payload size (50MB limit)
    if ($totalSize > 50 * 1024 * 1024) {
        throw new \Exception('Total payload size exceeds 50MB limit');
    }

    // Construct multi-modal message
    $messages = $this->constructMultiModalMessage($imageContents, $locale, $analysisType);

    // Call AI API
    $response = $this->callAiProvider($messages);

    // Parse and structure response
    $results = $this->parseAiResponse($response, count($images));

    // Log AI usage
    $this->logAiUsage([
        'type' => 'image_batch_parse',
        'images_count' => count($images),
        'tokens' => $response['usage']['total_tokens'] ?? 0,
        'cost' => $this->calculateCost($response),
        'analysis_type' => $analysisType
    ]);

    return $results;
}
```

#### 4.2.2 Image Compression Fallback

**Auto-Retry with Compression**:
```php
/**
 * Attempt API call with automatic compression on failure
 */
private function callAiProviderWithFallback(array $messages, array $originalImages): array
{
    try {
        // First attempt: original quality
        return $this->callAiProvider($messages);

    } catch (\Exception $e) {
        // Check if error is payload-related
        if ($this->isPayloadTooLargeError($e)) {
            Log::warning('Payload too large, retrying with compressed images');

            // Compress images to 80% quality
            $compressedImages = array_map(function($imageBase64) {
                return $this->compressBase64Image($imageBase64, 80);
            }, $originalImages);

            // Reconstruct message with compressed images
            $compressedMessages = $this->constructMultiModalMessage($compressedImages, ...);

            // Retry
            return $this->callAiProvider($compressedMessages);
        }

        throw $e;
    }
}

/**
 * Compress base64 image
 */
private function compressBase64Image(string $base64, int $quality = 80): string
{
    // Decode base64
    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64));

    // Create image resource
    $image = imagecreatefromstring($imageData);

    // Compress based on type
    ob_start();
    imagejpeg($image, null, $quality);
    $compressedData = ob_get_clean();

    imagedestroy($image);

    return 'data:image/jpeg;base64,' . base64_encode($compressedData);
}
```

### 4.3 Cost Calculation

**Pricing Model**:
```php
private function calculateBatchCost(array $response): float
{
    $provider = config('ai.default_provider'); // 'openai' or 'google'
    $tokens = $response['usage']['total_tokens'] ?? 0;

    $pricing = [
        'openai' => [
            'input_price_per_1k' => 0.01,
            'output_price_per_1k' => 0.03,
            'image_price' => 0.03 // Per image, high detail
        ],
        'google' => [
            'input_price_per_1k' => 0.00025,
            'output_price_per_1k' => 0.0005,
            'image_price' => 0.025 // Per image
        ]
    ];

    $inputTokens = $response['usage']['prompt_tokens'] ?? 0;
    $outputTokens = $response['usage']['completion_tokens'] ?? 0;
    $imageCount = $response['image_count'] ?? 0;

    $config = $pricing[$provider];

    $cost = (
        ($inputTokens / 1000 * $config['input_price_per_1k']) +
        ($outputTokens / 1000 * $config['output_price_per_1k']) +
        ($imageCount * $config['image_price'])
    );

    return round($cost, 4);
}
```

---

## 5. Backend API Endpoints

### 5.1 New Endpoint

**Route**: `POST /api/ai/parse-images-batch`

**Controller**: `AiImageController@parseImagesBatch`

**Request Validation**:
```php
public function parseImagesBatch(Request $request)
{
    $validated = $request->validate([
        'images' => 'required|array|min:2|max:10',
        'images.*' => 'required|string', // Base64 strings
        'locale' => 'nullable|string|in:zh-TW,en',
        'analysis_type' => 'nullable|string|in:batch_comparison,series_analysis,quality_check,general',
        'prompt_template' => 'nullable|string'
    ]);

    // Check AI budget
    $costControl = app(AiCostControlService::class);
    if (!$costControl->checkBudget()) {
        return $this->error('AI budget exhausted', 403);
    }

    try {
        $results = $this->aiImageService->parseImageBatch(
            $validated['images'],
            $validated['locale'] ?? 'zh-TW',
            $validated['analysis_type'] ?? 'batch_comparison'
        );

        return $this->success($results, 'Batch analysis completed');

    } catch (\Exception $e) {
        Log::error('Batch image parse failed', [
            'error' => $e->getMessage(),
            'images_count' => count($validated['images'])
        ]);

        return $this->error($e->getMessage(), 500);
    }
}
```

### 5.2 Backward Compatibility

**Keep Existing Endpoint**: `POST /api/ai/parse-image`

- 維持單圖解析功能不變
- 前端可根據圖片數量選擇呼叫哪個端點
- 漸進式遷移，避免破壞性變更

---

## 6. Error Handling & Edge Cases

### 6.1 Client-Side Error Handling

**Error Scenarios**:
```javascript
const handleBatchParse = async () => {
  try {
    isLoading.value = true;

    // Convert all images to base64
    const base64Images = await Promise.all(
      imageFiles.value.map(img => fileToBase64(img.file))
    );

    // Call API
    const response = await aiService.parseImagesBatch({
      images: base64Images,
      locale: document.documentElement.lang || 'zh-TW',
      analysis_type: analysisType.value
    });

    handleSuccess(response);

  } catch (error) {
    if (error.response?.status === 413) {
      // Payload too large
      ElMessage.error('圖片總大小超過限制，請減少圖片數量或壓縮後重試');
      showCompressionDialog();

    } else if (error.response?.status === 422) {
      // Token limit exceeded
      ElMessage.error('圖片解析度過高，建議降低品質後重試');

    } else if (error.response?.status === 403) {
      // Budget exhausted
      ElMessage.error('AI 預算不足，請聯繫管理員');
      router.push('/admin/settings/ai-budget');

    } else {
      ElMessage.error(error.response?.data?.message || '批次解析失敗');
    }
  } finally {
    isLoading.value = false;
  }
};
```

### 6.2 Server-Side Error Handling

**Timeout Protection**:
```php
// Set longer timeout for batch processing
set_time_limit(120); // 2 minutes

try {
    $response = Http::timeout(90)->post($apiUrl, $payload);
} catch (\Illuminate\Http\Client\ConnectionException $e) {
    Log::error('AI API timeout', ['images_count' => count($images)]);
    throw new \Exception('分析超時，請減少圖片數量後重試');
}
```

**Rate Limiting**:
```php
// Add rate limiting to prevent abuse
RateLimiter::for('ai-batch-parse', function (Request $request) {
    return Limit::perMinute(5)->by($request->user()->id);
});
```

### 6.3 Edge Cases

| Edge Case | Handling Strategy |
|-----------|------------------|
| 使用者選取 1 張圖片 | 自動切換到單圖模式 (`/api/ai/parse-image`) |
| 圖片格式不一致 | 統一轉換為 JPEG 格式，品質 90% |
| 部分圖片損壞 | 過濾損壞圖片，僅解析有效圖片 |
| AI 回應格式錯誤 | 使用備用解析邏輯，提取可用資訊 |
| 網路中斷 | 前端顯示「解析中斷」，提供重試按鈕 |
| 重複上傳相同圖片 | 顯示警告，允許使用者確認是否繼續 |

---

## 7. Testing Strategy

### 7.1 Unit Tests

**Backend Service Tests**:
```php
// tests/Unit/AiImageServiceTest.php
public function test_batch_parse_with_multiple_images()
{
    $images = [
        UploadedFile::fake()->image('product1.jpg', 800, 600),
        UploadedFile::fake()->image('product2.jpg', 800, 600),
        UploadedFile::fake()->image('product3.jpg', 800, 600)
    ];

    $result = $this->aiImageService->parseImageBatch($images, 'zh-TW', 'batch_comparison');

    $this->assertIsArray($result);
    $this->assertArrayHasKey('individual_analyses', $result);
    $this->assertCount(3, $result['individual_analyses']);
}

public function test_batch_parse_rejects_single_image()
{
    $this->expectException(\InvalidArgumentException::class);

    $images = [
        UploadedFile::fake()->image('product1.jpg', 800, 600)
    ];

    $this->aiImageService->parseImageBatch($images);
}

public function test_batch_parse_compresses_on_payload_too_large()
{
    // Mock large images
    $largeImages = array_fill(0, 8, $this->createLargeImageBase64());

    // Should auto-compress and retry
    $result = $this->aiImageService->parseImageBatch($largeImages);

    $this->assertIsArray($result);
    // Verify compression was triggered
    Log::assertLogged('warning', function($log) {
        return str_contains($log, 'retrying with compressed images');
    });
}
```

### 7.2 Integration Tests

**API Endpoint Tests**:
```php
// tests/Feature/AiImageBatchTest.php
public function test_batch_parse_endpoint_success()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/ai/parse-images-batch', [
            'images' => [
                $this->getBase64Image('test1.jpg'),
                $this->getBase64Image('test2.jpg'),
                $this->getBase64Image('test3.jpg')
            ],
            'locale' => 'zh-TW',
            'analysis_type' => 'batch_comparison'
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'summary',
                'individual_analyses',
                'comparative_insights',
                'metadata'
            ]
        ]);
}

public function test_batch_parse_requires_at_least_two_images()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/ai/parse-images-batch', [
            'images' => [
                $this->getBase64Image('test1.jpg')
            ]
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('images');
}
```

### 7.3 Frontend E2E Tests

**Playwright/Cypress Tests**:
```javascript
// tests/e2e/ai-batch-upload.spec.js
describe('AI Batch Image Upload', () => {
  it('should upload and parse multiple images', async () => {
    await page.goto('/admin/ai-tools');

    // Upload 3 images
    const fileInput = await page.locator('input[type="file"]');
    await fileInput.setInputFiles([
      'fixtures/product1.jpg',
      'fixtures/product2.jpg',
      'fixtures/product3.jpg'
    ]);

    // Verify preview grid
    await expect(page.locator('.thumbnail-card')).toHaveCount(3);

    // Click batch analyze
    await page.click('button:has-text("Batch Analyze")');

    // Wait for results
    await page.waitForSelector('.batch-results', { timeout: 30000 });

    // Verify results
    const summary = await page.locator('.analysis-summary').textContent();
    expect(summary).toContain('分析了 3 款產品');
  });

  it('should handle payload too large error', async () => {
    // Upload 10 very large images
    await fileInput.setInputFiles(Array(10).fill('fixtures/large-image.jpg'));

    await page.click('button:has-text("Batch Analyze")');

    // Should show error message
    await expect(page.locator('.el-message--error')).toContainText('超過限制');
  });
});
```

---

## 8. Performance Optimization

### 8.1 Client-Side Optimizations

**Progressive Image Loading**:
```javascript
const handleFileSelect = async (event) => {
  const files = Array.from(event.target.files);

  // Add files immediately to UI (show placeholders)
  files.forEach(file => {
    imageFiles.value.push({
      id: nextId++,
      file: file,
      preview: null, // Placeholder
      status: 'loading'
    });
  });

  // Load previews progressively
  for (const [index, file] of files.entries()) {
    const preview = await fileToBase64(file);
    imageFiles.value[index].preview = preview;
    imageFiles.value[index].status = 'ready';
  }
};
```

**Lazy Base64 Conversion**:
```javascript
// Only convert to base64 when user clicks "Analyze"
const convertImagesForApi = async () => {
  const conversions = imageFiles.value.map(async (img) => {
    if (!img.base64) {
      img.base64 = await fileToBase64(img.file);
    }
    return img.base64;
  });

  return Promise.all(conversions);
};
```

### 8.2 Server-Side Optimizations

**Parallel Image Processing**:
```php
use Illuminate\Support\Facades\Cache;

// Cache similar image analyses to reduce API calls
$cacheKey = 'ai_batch_' . md5(json_encode($imageHashes));

$results = Cache::remember($cacheKey, 3600, function() use ($images) {
    return $this->aiImageService->parseImageBatch($images);
});
```

**Queue for Large Batches**:
```php
// For batches > 5 images, use queue processing
if (count($images) > 5) {
    dispatch(new ProcessImageBatch($images, $user));
    return $this->success(['job_id' => $jobId], 'Processing started');
}
```

### 8.3 Database Optimization

**Batch Usage Logging**:
```php
// Log as single entry instead of multiple
AiUsageLog::create([
    'user_id' => auth()->id(),
    'project_id' => $currentProject->id,
    'type' => 'image_batch_parse',
    'images_count' => count($images),
    'tokens' => $totalTokens,
    'cost_usd' => $totalCost,
    'analysis_type' => $analysisType,
    'metadata' => json_encode($metadata)
]);
```

---

## 9. UI/UX Flow Diagram

```
┌─────────────────────────────────────────────┐
│  1. User lands on AI Tools page            │
└───────────────┬─────────────────────────────┘
                │
                ▼
┌─────────────────────────────────────────────┐
│  2. Click "Upload Multiple Images" or       │
│     Drag & Drop files into zone             │
└───────────────┬─────────────────────────────┘
                │
                ▼
┌─────────────────────────────────────────────┐
│  3. Frontend validates files:               │
│     - File type (JPG/PNG/WEBP)             │
│     - File size (< 10MB each)              │
│     - Total count (2-10 images)            │
└───────────────┬─────────────────────────────┘
                │
                ▼
┌─────────────────────────────────────────────┐
│  4. Display preview grid with thumbnails    │
│     - Show order numbers                    │
│     - Show remove buttons                   │
│     - Display total count & estimated cost  │
└───────────────┬─────────────────────────────┘
                │
                ▼
┌─────────────────────────────────────────────┐
│  5. User selects analysis type:             │
│     ○ Batch Comparison (default)           │
│     ○ Series Analysis                       │
│     ○ Quality Check                         │
│     ○ General Analysis                      │
└───────────────┬─────────────────────────────┘
                │
                ▼
┌─────────────────────────────────────────────┐
│  6. User clicks "Batch Analyze" button      │
└───────────────┬─────────────────────────────┘
                │
                ▼
┌─────────────────────────────────────────────┐
│  7. Frontend converts all images to base64  │
│     - Show progress indicator               │
└───────────────┬─────────────────────────────┘
                │
                ▼
┌─────────────────────────────────────────────┐
│  8. Send POST /api/ai/parse-images-batch    │
│     - Show loading overlay                  │
│     - Display "Analyzing..." message        │
└───────────────┬─────────────────────────────┘
                │
        ┌───────┴───────┐
        │               │
        ▼               ▼
   ┌─────────┐    ┌─────────┐
   │ Success │    │  Error  │
   └────┬────┘    └────┬────┘
        │               │
        ▼               ▼
┌─────────────┐   ┌─────────────┐
│ Display:    │   │ Handle:     │
│ - Summary   │   │ - Too large │
│ - Individual│   │ - Timeout   │
│ - Compare   │   │ - Budget    │
│ - Recommend │   │ - Retry     │
└─────────────┘   └─────────────┘
```

---

## 10. Migration & Rollout Plan

### 10.1 Phase 1: Backend Foundation (Week 1-2)

- [ ] Implement `AiImageService::parseImageBatch()` method
- [ ] Add new API endpoint `/api/ai/parse-images-batch`
- [ ] Implement multi-modal message construction for OpenAI & Gemini
- [ ] Add compression fallback logic
- [ ] Write unit tests for service layer
- [ ] Update cost calculation logic

### 10.2 Phase 2: Frontend Implementation (Week 2-3)

- [ ] Update `CameraUploader.vue` component
  - [ ] Add multiple file selection
  - [ ] Implement drag & drop zone
  - [ ] Build preview grid layout
  - [ ] Add validation logic
- [ ] Create analysis type selector
- [ ] Implement cost estimation display
- [ ] Add error handling & retry logic
- [ ] Update translation files (zh-TW, en)

### 10.3 Phase 3: Testing & Refinement (Week 3-4)

- [ ] Integration testing
- [ ] E2E testing with Playwright
- [ ] Performance testing (large batches)
- [ ] User acceptance testing
- [ ] Bug fixes & UX improvements

### 10.4 Phase 4: Deployment (Week 4)

- [ ] Deploy to staging environment
- [ ] Smoke testing
- [ ] Deploy to production
- [ ] Monitor AI usage & costs
- [ ] Collect user feedback

### 10.5 Rollback Plan

**If issues occur**:
1. Feature flag: `AI_BATCH_UPLOAD_ENABLED=false` in `.env`
2. Frontend automatically falls back to single-image mode
3. Keep old endpoint `/api/ai/parse-image` fully functional
4. No database migrations required (additive only)

---

## 11. Configuration

### 11.1 Environment Variables

```env
# AI Batch Upload Settings
AI_BATCH_UPLOAD_ENABLED=true
AI_BATCH_MAX_IMAGES=10
AI_BATCH_MAX_PAYLOAD_MB=50
AI_BATCH_TIMEOUT_SECONDS=90

# AI Provider Settings
AI_DEFAULT_PROVIDER=openai # or 'google'
OPENAI_API_KEY=sk-...
GOOGLE_AI_API_KEY=AIza...

# Cost Control
AI_MONTHLY_BUDGET_USD=500
AI_ALERT_THRESHOLD_PERCENT=80
```

### 11.2 Config File

**File**: `config/ai.php`

```php
return [
    'batch_upload' => [
        'enabled' => env('AI_BATCH_UPLOAD_ENABLED', true),
        'max_images' => env('AI_BATCH_MAX_IMAGES', 10),
        'max_payload_mb' => env('AI_BATCH_MAX_PAYLOAD_MB', 50),
        'timeout_seconds' => env('AI_BATCH_TIMEOUT_SECONDS', 90),

        'analysis_types' => [
            'batch_comparison' => 'Batch Comparison Analysis',
            'series_analysis' => 'Product Series Analysis',
            'quality_check' => 'Quality Check',
            'general' => 'General Analysis'
        ],

        'compression' => [
            'enabled' => true,
            'quality' => 80, // JPEG quality (1-100)
            'max_dimension' => 2048 // Max width/height in pixels
        ]
    ],

    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => 'gpt-4o-2024-11-20',
            'max_tokens' => 4096
        ],
        'google' => [
            'api_key' => env('GOOGLE_AI_API_KEY'),
            'model' => 'gemini-2.0-flash-exp',
            'max_tokens' => 8192
        ]
    ]
];
```

---

## 12. Success Metrics

### 12.1 KPIs

| Metric | Target | Measurement |
|--------|--------|-------------|
| **Adoption Rate** | 30% of users try batch upload within 1 month | Google Analytics |
| **Success Rate** | > 95% of batch requests succeed | Application logs |
| **Performance** | < 10s average response time for 5 images | API monitoring |
| **Cost Efficiency** | < $0.20 per batch analysis | AI usage logs |
| **User Satisfaction** | > 4.0/5.0 rating | In-app survey |

### 12.2 Monitoring

**Datadog/New Relic Metrics**:
- `ai.batch_upload.request_count` (counter)
- `ai.batch_upload.response_time` (histogram)
- `ai.batch_upload.images_per_request` (histogram)
- `ai.batch_upload.error_rate` (gauge)
- `ai.batch_upload.cost_usd` (counter)

**Alerts**:
- Error rate > 5% for 5 minutes → Slack alert
- Average response time > 15s → PagerDuty
- Daily cost > $50 → Email to admin

---

## 13. Future Enhancements

### 13.1 Short-term (Q2 2026)

- [ ] **Batch History**: 保存批次分析記錄，允許使用者回顧
- [ ] **Export Results**: 匯出批次分析結果為 CSV/JSON
- [ ] **Smart Grouping**: AI 自動將產品分組並建議類別
- [ ] **Duplicate Detection**: 自動偵測重複上傳的圖片

### 13.2 Long-term (Q3-Q4 2026)

- [ ] **Video Frame Analysis**: 支援影片批次截圖分析
- [ ] **OCR Integration**: 從圖片中提取文字資訊
- [ ] **Background Removal**: 自動去背並統一產品圖背景
- [ ] **A/B Testing**: 比較不同提示詞模板的效果
- [ ] **Multi-tenant Cost Tracking**: 每個專案獨立預算追蹤

---

## Document Revision History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0.0 | 2026-02-05 | Senior AI Engineer | Initial specification created |

---

## Appendix

### A. Related Documents

- `docs/api/ai-image-endpoints.md` - API 端點詳細文件
- `docs/architecture/ai-service-layer.md` - AI 服務層架構
- `docs/user-guide/ai-tools.md` - 使用者操作指南

### B. References

- [OpenAI Vision API Documentation](https://platform.openai.com/docs/guides/vision)
- [Google Gemini Pro Vision API](https://ai.google.dev/gemini-api/docs/vision)
- [Laravel File Upload Best Practices](https://laravel.com/docs/filesystem)
- [Element Plus Upload Component](https://element-plus.org/en-US/component/upload.html)

---

**End of Specification Document**
