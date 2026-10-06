# Implementation Tasks

## 1. Backend - New Multi-Image API

- [x] 1.1 新增 `parseMultipleImages` 方法至 `AiImageService.php`
  - 接收多個 UploadedFile
  - 將所有圖片轉為 base64
  - 組成 GPT-4o Vision 多圖輸入格式
  - 返回綜合解析結果
- [x] 1.2 新增 `parseImages` 方法至 `AiImageController.php`
  - 驗證 images 陣列（1-5 個檔案）
  - 呼叫 service 層
- [x] 1.3 新增路由 `POST /api/ai/parse-images`
- [x] 1.4 新增前端 service 方法 `aiService.parseImages(formData)`

## 2. Frontend - CameraUploader Component

- [x] 2.1 重構元件為多圖合併分析模式
  - `imageFiles = ref([])` - 圖片列表
  - `isLoading = ref(false)` - 解析中
  - `parsedData = ref(null)` - 單一解析結果
  - `error = ref(null)` - 錯誤訊息
- [x] 2.2 實作多檔案選取功能（multiple 屬性，最多 5 張）
- [x] 2.3 實作預覽網格佈局
- [x] 2.4 實作單張圖片移除功能
- [x] 2.5 實作「開始掃描」功能（呼叫新 API）
- [x] 2.6 實作解析結果顯示（單一綜合結果）
- [x] 2.7 實作「建立產品」功能
- [x] 2.8 實作重設功能
- [x] 2.9 實作「添加更多圖片」功能
- [x] 2.10 實作錯誤處理與重試

## 3. Frontend - AI Tools Page

- [x] 3.1 簡化 `ai-tools/index.vue` 事件處理
- [x] 3.2 移除舊的 batch mode 相關狀態

## 4. Testing

- [x] 4.1 測試多圖上傳（2-3 張同產品圖片）
- [x] 4.2 驗證 AI 返回綜合資訊
- [x] 4.3 測試從結果建立產品
- [x] 4.4 測試錯誤處理與重試
