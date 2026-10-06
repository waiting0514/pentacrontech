## Why

首頁目前沒有輪播圖（Banner/Carousel），缺少視覺焦點與行銷推廣位置。需要一個支援 PC/Mobile 雙圖、SEO 優化（LCP）、後台管理的輪播圖系統。

## What Changes

- 新增 `banners` 資料表（title, link_url, image_pc, image_mobile, sort_order, is_active）
- 擴充 `ImageService` 新增 `processBannerImage` 方法（非 1:1，PC 1920px / Mobile 800px，WebP，Quality 85）
- 新增 `Banner` Model 及 `BannerController` API（CRUD + 排序）
- 前台首頁使用 Swiper.js 渲染輪播圖，搭配 `<picture>` 標籤實作響應式圖片
- LCP 優化：第一張 eager load + fetchpriority="high"，其餘 lazy load
- 後台新增 `BannerManager.vue` 管理元件

## Capabilities

### New Capabilities
- `banner`: 輪播圖 CRUD 管理、PC/Mobile 雙圖上傳、排序、啟用/停用
- `banner-display`: 前台 Swiper.js 輪播渲染、picture 標籤響應式、LCP 優化

### Modified Capabilities
- `image-processing`: ImageService 新增 processBannerImage 方法（非正方形裁切）

## Impact

- 資料庫：新增 `banners` 表
- 儲存：`uploads/banners/pc/` 和 `uploads/banners/mobile/`
- API：新增 `/api/banners` 路由
- 前端依賴：新增 Swiper.js（CDN 或 npm）
- 前台：修改 `home.blade.php` 加入輪播區塊
- 後台：Dashboard 新增 Banner 管理 tab
