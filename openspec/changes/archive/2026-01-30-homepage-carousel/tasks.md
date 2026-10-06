## 1. 依賴安裝

- [x] 1.1 npm install swiper

## 2. 資料庫

- [x] 2.1 建立 `create_banners_table` migration（title, link_url, image_pc, image_mobile, sort_order, is_active）

## 3. ImageService

- [x] 3.1 擴充 `ImageService` 新增 `processBannerImage` 方法（JPG, PC 1920px / Mobile 800px, quality 85, maintain aspect ratio）
- [x] 3.2 新增 `deleteBannerImages` 方法（刪除 PC + Mobile 檔案）

## 4. Model & Controller

- [x] 4.1 建立 `Banner` Model（fillable, casts）
- [x] 4.2 建立 `BannerController` API（index, store, update, updateSort, destroy）
- [x] 4.3 註冊 `/api/banners` 路由

## 5. 前端後台

- [x] 5.1 建立 `BannerManager.vue`（列表：PC 縮圖、sort_order、is_active toggle、編輯/刪除；表單 modal：title、link_url、雙圖上傳含預覽）
- [x] 5.2 在 Dashboard.vue 新增 Banner 管理 tab

## 6. 前台

- [x] 6.1 修改 HomeController 查詢 active banners 傳入 view
- [x] 6.2 修改 home.blade.php 加入 Swiper 輪播（picture 標籤、LCP 優化、autoplay、navigation、pagination）
- [x] 6.3 在前台 layout 引入 Swiper CSS/JS（npm）
