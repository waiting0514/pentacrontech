# 後台管理系統使用說明

## 快速開始

### 1. 啟動開發環境

```bash
# 啟動 Laravel Sail (Docker)
./vendor/bin/sail up -d

# 執行 Migrations
./vendor/bin/sail artisan migrate

# 啟動前端開發伺服器
./vendor/bin/sail npm run dev
```

### 2. 訪問後台

開啟瀏覽器,訪問: **http://localhost/dashboard**

---

## 功能模組說明

### 📦 產品管理

**位置**: Dashboard → 產品管理 Tab

**功能**:
- ✅ 新增產品 (支援中英文多語言)
- ✅ 編輯產品資訊
- ✅ 刪除產品
- ✅ 搜尋產品 (使用 Laravel Scout)

**操作步驟**:
1. 點擊「新增產品」按鈕
2. 填寫產品資訊:
   - 產品名稱 (中文/English)
   - 描述 (中文/English)
   - 規格
   - 價格
   - 圖片 URL
3. 點擊「儲存」

**注意事項**:
- 中文名稱和描述為必填
- 價格必須為正數
- 圖片 URL 為選填,可稍後透過 AI 工具生成

---

### 🤖 AI 工具

**位置**: Dashboard → AI 工具 Tab

#### 1. AI 助手 (左上)

**功能**: 情境感知聊天,支援產品推薦和 RAG 搜尋

**使用方式**:
1. (選填) 在「搜尋相關產品」輸入關鍵字,啟用 RAG 功能
2. 在訊息框輸入問題,例如:
   - "推薦一款適合辦公室的咖啡機"
   - "這款產品的主要特色是什麼?"
3. 點擊「發送」或按 Enter
4. AI 回應後,可點擊「插入到表單」按鈕

**情境模式**:
- **產品助手**: 專注於產品推薦和說明
- **新聞助手**: 適合撰寫行銷文案 (可在程式碼中切換)

#### 2. 拍照上傳 & 智能解析 (右上)

**功能**: 使用 GPT-4o Vision 自動解析產品圖片

**使用方式**:
1. 點擊上傳區域,選擇產品照片
   - 支援行動裝置相機拍照
   - 支援 JPG、PNG 格式,最大 10MB
2. 點擊「🔍 開始掃描解析」
3. 等待 5-15 秒,AI 會自動解析:
   - 產品名稱
   - 規格
   - SEO 標題
   - SEO 關鍵字
   - 描述
4. 檢查並手動編輯解析結果
5. 複製資料到產品管理表單

**適用場景**:
- 快速建檔實體產品
- 從 DM 或包裝照片提取資訊
- 批量處理產品圖片

#### 3. 圖片編輯器 (下方)

**功能**: AI 驅動的圖片處理

**操作**:

**移除背景**:
1. 確認顯示的圖片
2. 點擊「移除背景」按鈕
3. 等待處理完成 (約 5-10 秒)
4. 新圖片會自動顯示

**生成場景**:
1. 點擊「生成場景」按鈕
2. 輸入場景描述,例如:
   - "在現代辦公室桌上,陽光從窗戶灑進來"
   - "放在咖啡廳吧台,背景模糊"
3. 點擊「生成」
4. 等待處理完成 (約 10-15 秒)

**成本提醒**:
- 移除背景: ~$0.03/次
- 生成場景: ~$0.05/次
- Vision Parse: ~$0.01-0.05/次 (視圖片大小)

---

### 📊 使用量監控

**位置**: Dashboard → 使用量監控 Tab

**功能**: 即時追蹤 AI API 使用量和成本

#### 本月使用量卡片

顯示資訊:
- **已使用**: 當月累計成本
- **預算**: 月度預算上限 (預設 $30)
- **剩餘**: 可用額度
- **使用率**: 百分比進度條

**顏色指示**:
- 🟢 綠色: < 70% (安全)
- 🟡 黃色: 70-90% (注意)
- 🔴 紅色: > 90% (警告)

#### 使用統計表

按服務和功能分組顯示:
- 服務商 (OpenAI, Photoroom, Clipdrop)
- 功能類型 (聊天、翻譯、圖片處理等)
- 使用次數
- 總成本

#### 使用記錄表

詳細記錄每次 API 呼叫:
- 時間戳記
- 服務商
- 功能
- Token 使用量 (僅 OpenAI)
- 成本
- 狀態 (成功/失敗)

---

## 成本控制機制

### 預算設定

**預設值**: 每位使用者 $30/月

**修改方式** (需資料庫操作):
```sql
UPDATE users SET monthly_budget_usd = 50.00 WHERE id = 1;
```

### 超額處理

**目前策略**: 記錄警告,但仍允許繼續使用

**如需阻擋超額請求**:
1. 編輯 `app/Services/AiCostControlService.php`
2. 修改第 28 行:
   ```php
   return false; // 改為 false 以阻擋超額請求
   ```

### 每月重置

**自動執行**: 每月 1 日 00:00 自動重置所有使用者的 `monthly_usage_usd` 為 0

**手動執行**:
```bash
./vendor/bin/sail artisan usage:reset-monthly
```

---

## API 端點參考

### 產品 API

```http
GET    /api/products          # 列表
POST   /api/products          # 新增
GET    /api/products/{id}     # 單筆
PUT    /api/products/{id}     # 更新
DELETE /api/products/{id}     # 刪除
```

### AI 文字 API

```http
POST /api/ai/chat              # 聊天
POST /api/ai/translate         # 翻譯
```

### AI 圖片 API

```http
POST /api/ai/parse-image       # Vision Parse
POST /api/ai/remove-background # 移除背景
POST /api/ai/generate-scene    # 生成場景
```

### 使用量 API

```http
GET /api/usage/current         # 當月使用量
GET /api/usage/logs            # 使用記錄
GET /api/usage/statistics      # 統計資料
```

---

## 環境配置

### 必要的 API 金鑰

在 `.env` 檔案中設定:

```env
# OpenAI (必須)
OPENAI_API_KEY=sk-your-actual-key-here

# Photoroom (選填,用於場景生成)
PHOTOROOM_API_KEY=your-key-here

# Clipdrop (選填,用於背景移除)
CLIPDROP_API_KEY=your-key-here

# Laravel Scout
SCOUT_DRIVER=database
```

### 取得 API 金鑰

1. **OpenAI**: https://platform.openai.com/api-keys
2. **Photoroom**: https://www.photoroom.com/api
3. **Clipdrop**: https://clipdrop.co/apis

---

## 常見問題

### Q1: 前端無法連接 API?

**解決方案**:
1. 檢查 Laravel Sail 是否正在運行: `./vendor/bin/sail ps`
2. 確認 API 路由已註冊: `./vendor/bin/sail artisan route:list`
3. 檢查瀏覽器 Console 是否有 CORS 錯誤

### Q2: AI 功能回傳 429 錯誤?

**原因**: 已超過月度預算

**解決方案**:
1. 檢查使用量監控頁面
2. 增加預算或等待下月重置
3. 修改 `AiCostControlService` 的阻擋策略

### Q3: 圖片上傳失敗?

**可能原因**:
- 檔案過大 (超過 10MB)
- 格式不支援
- Storage 權限問題

**解決方案**:
```bash
# 建立 storage link
./vendor/bin/sail artisan storage:link

# 檢查權限
./vendor/bin/sail exec laravel.test chmod -R 775 storage
```

### Q4: 搜尋功能無效?

**解決方案**:
```bash
# 重建 Scout 索引
./vendor/bin/sail artisan scout:import "App\Models\Product"
```

---

## 開發建議

### 新增語言支援

1. 修改 `AiTextService::translate()` 的 `$langNames` 陣列
2. 更新前端表單,加入新語言欄位

### 自訂 AI 情境

編輯 `AiTextService::getSystemPrompt()`:

```php
return match($context) {
    'product' => "...",
    'news' => "...",
    'custom' => "Your custom prompt here", // 新增
    default => "..."
};
```

### 效能優化

1. **啟用快取**:
   ```php
   Cache::remember('products', 3600, function () {
       return Product::all();
   });
   ```

2. **使用 Queue 處理圖片**:
   ```php
   ProcessImageJob::dispatch($image);
   ```

3. **升級 Scout Driver**:
   - 從 `database` 升級到 `meilisearch` 或 `algolia`

---

## 技術支援

**專案文件**:
- [Implementation Plan](file:///C:/Users/User/.gemini/antigravity/brain/0938269b-a6c9-4314-afe8-733a348b828b/implementation_plan.md)
- [Walkthrough](file:///C:/Users/User/.gemini/antigravity/brain/0938269b-a6c9-4314-afe8-733a348b828b/walkthrough.md)

**相關連結**:
- Laravel 文件: https://laravel.com/docs/11.x
- Vue 3 文件: https://vuejs.org/
- OpenAI API: https://platform.openai.com/docs
