# Proposal: Multi-Image Context Analysis

## Why

現有 AI 圖片解析功能只能一次處理一張圖片。但實際使用情境中，用戶經常需要上傳**同一產品的多張圖片**來獲得更完整的資訊：

- **多角度拍攝**：產品正面、背面、側面、細節
- **DM + 包裝**：宣傳單張 + 實體外盒說明
- **規格變體**：不同顏色、尺寸的同款產品
- **組合資訊**：主圖 + 規格表 + 成分標示

單張圖片往往資訊不完整，導致 AI 解析結果缺漏。需要將多張圖片**合併為單一 context** 發送給 AI，讓它能綜合所有資訊。

## What Changes

### 前端變更
- `CameraUploader.vue` 支援多圖選取（最多 5 張）
- 預覽網格顯示所有已選圖片
- 每張圖片可單獨移除
- 點擊「開始掃描」後，**所有圖片一次發送給 AI**
- 顯示單一綜合解析結果

### 後端變更
- **新增端點** `POST /api/ai/parse-images`（複數）
- 將多張圖片作為 GPT-4o Vision 的多圖輸入
- AI 綜合分析所有圖片，返回完整產品資訊
- 保留原有 `/api/ai/parse-image` 向後相容

### GPT-4o Vision 多圖輸入

OpenAI Vision API 原生支援多圖輸入：

```php
$messages = [
    ['role' => 'user', 'content' => [
        ['type' => 'text', 'text' => '綜合分析這些產品圖片...'],
        ['type' => 'image_url', 'image_url' => ['url' => $image1Base64]],
        ['type' => 'image_url', 'image_url' => ['url' => $image2Base64]],
        ['type' => 'image_url', 'image_url' => ['url' => $image3Base64]],
    ]]
];
```

## Capabilities

### New Capabilities
- `multi-image-parse`: 多圖合併解析 API

### Modified Capabilities
- `CameraUploader`: 更新為多圖上傳模式

## Impact

### 前端
- `resources/js/admin/components/CameraUploader.vue` - 多圖上傳 UI
- `resources/js/admin/views/ai-tools/index.vue` - 事件處理

### 後端
- `app/Http/Controllers/Api/AiImageController.php` - 新增 parseImages 方法
- `app/Services/AiImageService.php` - 新增多圖解析邏輯
- `routes/api.php` - 新增路由

## Cost Estimate

- 單圖解析：~$0.03
- 多圖解析（3-5張）：~$0.05-0.10（視圖片尺寸）

## Success Criteria

- [ ] 支援 1-5 張圖片同時上傳
- [ ] AI 能綜合多圖資訊返回完整產品資料
- [ ] 成本追蹤正確記錄
- [ ] 保持與現有單圖 API 的向後相容
