## Context

系統已有 `ImageService` 處理產品圖片（1:1 JPG 三種尺寸）。輪播圖需要不同的處理邏輯：PC 橫式（1920px 寬）和 Mobile 方形/直式（800px 寬），不強制 1:1 比例。首頁目前為 `home.blade.php`。

## Goals / Non-Goals

**Goals:**
- PC/Mobile 分開上傳，分別處理為最佳尺寸的 JPG
- 前台使用 `<picture>` 標籤 + Swiper.js（npm）實現響應式輪播
- 第一張圖 eager load + fetchpriority="high" 優化 LCP
- 後台可管理輪播圖（CRUD、排序、啟停）

**Non-Goals:**
- 不做影片輪播
- 不做輪播圖排程（定時上下架）
- 不做多語系標題

## Decisions

### 1. 圖片處理：擴充 ImageService
新增 `processBannerImage(UploadedFile $image, string $type): string` 方法。

- `$type = 'pc'`：resize to width 1920px，maintain aspect ratio，JPG quality 85
- `$type = 'mobile'`：resize to width 800px，maintain aspect ratio，JPG quality 85

不強制 1:1，使用 `scaleDown(width)` 保持原始比例。儲存路徑 `uploads/banners/{type}/`。

**理由**：輪播圖比例由設計師決定，不應強制裁切。

### 2. 資料表：單表存 PC/Mobile 路徑
`banners` 表直接存 `image_pc` 和 `image_mobile` 兩個字串欄位，不用 JSON。

**理由**：每個 banner 固定只有兩張圖（PC + Mobile），不像產品多圖需要獨立表。

### 3. 前台渲染：Swiper.js（npm）+ picture 標籤
透過 npm 安裝 Swiper.js，在前台 Blade 中使用。每個 slide 使用 `<picture>` 標籤：
```html
<picture>
  <source media="(max-width: 768px)" srcset="mobile.jpg">
  <img src="pc.jpg" alt="..." loading="eager/lazy" fetchpriority="high/auto">
</picture>
```

第一張 slide：`loading="eager"` + `fetchpriority="high"`
其餘 slide：`loading="lazy"`

Swiper 配置：自動播放（5 秒）、導航箭頭、分頁圓點。

### 4. 後台 API
- `GET /api/banners` — 列表（sort_order 排序）
- `POST /api/banners` — 新增（含雙圖上傳）
- `PUT /api/banners/{banner}` — 更新（可選替換圖片）
- `PATCH /api/banners/{banner}/sort` — 更新排序
- `DELETE /api/banners/{banner}` — 刪除（含檔案清理）

### 5. 前台 Controller
`HomeController` 查詢 `is_active` 且按 `sort_order` 排序的 banners，傳入 view。

## Risks / Trade-offs

- **大圖上傳** → 1920px JPG 檔案可能較大，已設 quality 85 平衡品質與大小
- **無 fallback** → 若 banner 為空則首頁無輪播區塊，需前台處理空狀態
