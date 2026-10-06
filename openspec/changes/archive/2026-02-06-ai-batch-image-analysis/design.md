# Design: Multi-Image Context Analysis

## Context

### 現狀
目前 AI 圖片工具使用 `CameraUploader.vue` 元件，僅支援單張圖片上傳與解析。API 端點 `POST /api/ai/parse-image` 接受單一圖片，回傳產品名稱、規格、描述等資訊。

### 使用情境
用戶需要上傳**同一產品的多張圖片**來獲得完整資訊：
- 產品正面 + 背面 + 側面
- DM 宣傳單 + 外盒包裝
- 不同顏色/規格的同系列產品

### 技術環境
- **前端**: Vue 3 + Composition API, Element Plus, Vite
- **後端**: Laravel 12, PHP 8.2+, OpenAI Laravel SDK
- **AI**: GPT-4o Vision（原生支援多圖輸入）

## Goals / Non-Goals

**Goals:**
- 支援一次選取多張圖片（最多 5 張）
- 所有圖片作為單一 context 發送給 AI
- AI 綜合所有圖片資訊返回完整產品資料
- 保留原有單圖 API 向後相容

**Non-Goals:**
- 批次處理多個不同產品（那用循環呼叫單圖 API）
- 圖片自動分類或分組

## Decisions

### Decision 1: 新增 API vs 修改現有 API

**選擇**: 新增 `POST /api/ai/parse-images` 端點

| 方案 | 優點 | 缺點 |
|------|------|------|
| **A: 新增端點** ✓ | 向後相容、職責分明 | 多一個端點維護 |
| **B: 修改現有** | 單一端點 | 破壞現有流程 |

**理由**: 多圖分析是新功能，應該有獨立端點。現有單圖 API 繼續運作。

### Decision 2: GPT-4o Vision 多圖輸入格式

**選擇**: 使用 OpenAI 原生多圖支援

```php
$response = OpenAI::chat()->create([
    'model' => 'gpt-4o',
    'messages' => [
        [
            'role' => 'system',
            'content' => $systemPrompt
        ],
        [
            'role' => 'user',
            'content' => [
                ['type' => 'text', 'text' => '綜合分析這些產品圖片，提取完整資訊...'],
                ['type' => 'image_url', 'image_url' => ['url' => "data:image/jpeg;base64,{$image1}"]],
                ['type' => 'image_url', 'image_url' => ['url' => "data:image/jpeg;base64,{$image2}"]],
                ['type' => 'image_url', 'image_url' => ['url' => "data:image/jpeg;base64,{$image3}"]],
            ]
        ]
    ],
    'response_format' => ['type' => 'json_object'],
]);
```

**理由**: GPT-4o 原生支援多圖輸入，無需額外處理。

### Decision 3: 前端狀態管理

**選擇**: 簡化狀態（多圖為一體）

```javascript
const imageFiles = ref([]);        // 已選圖片列表
const isLoading = ref(false);      // 解析中
const parsedData = ref(null);      // 解析結果（單一結果）
const error = ref(null);           // 錯誤訊息
```

**理由**: 多圖合併分析只產生一個結果，不需要每張圖片獨立狀態。

### Decision 4: 成本追蹤

GPT-4o Vision 多圖輸入的 token 計算：
- 每張圖片約 85-170 tokens（視尺寸）
- 5 張圖片約 500-850 input tokens
- 預估成本：$0.05-0.10 per request

## API 設計

### Request
```
POST /api/ai/parse-images
Content-Type: multipart/form-data

images[]: File (最多 5 個)
locale: string (zh-TW | en)
```

### Response
```json
{
  "success": true,
  "data": {
    "data": {
      "name": "產品名稱",
      "specs": "規格描述（綜合所有圖片）",
      "description": "完整描述",
      "meta_title": "SEO 標題",
      "meta_keywords": "關鍵字",
      "variants": [
        { "color": "黑色", "sku": "BLK-001" },
        { "color": "白色", "sku": "WHT-001" }
      ]
    },
    "usage": {
      "input_tokens": 850,
      "output_tokens": 200,
      "cost_usd": 0.08,
      "image_count": 3
    }
  }
}
```

## Risks / Trade-offs

| 風險 | 嚴重度 | 緩解措施 |
|------|--------|----------|
| **Timeout** | 中 | 設定 120s timeout、顯示進度 |
| **大圖片** | 中 | 後端壓縮、限制單檔 10MB |
| **成本較高** | 低 | 顯示預估成本、預算檢查 |

### Trade-offs

1. **單一結果 vs 多結果**: 選擇單一結果，因為這是「同一產品多角度」的情境
2. **新增端點 vs 修改現有**: 選擇新增，保持向後相容

## Open Questions

無。架構清晰，使用 OpenAI 原生功能。
