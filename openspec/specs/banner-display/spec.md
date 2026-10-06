## ADDED Requirements

### Requirement: 首頁輪播顯示
首頁 SHALL 使用 Swiper.js 輪播元件顯示啟用的 Banner，包含導航箭頭、分頁圓點，並設定為 5 秒自動播放。每張幻燈片 SHALL 使用 `<picture>` 標籤，分別提供 PC 和 Mobile 版本的圖片來源。

#### Scenario: 渲染 Banner 輪播
- **WHEN** 首頁載入且存在啟用的 Banner 時
- **THEN** 渲染全寬 Swiper 輪播，並依據 `sort_order` 排序顯示所有啟用的 Banner

#### Scenario: 無 Banner 存在
- **WHEN** 首頁載入且無任何啟用的 Banner 時
- **THEN** 不渲染輪播區塊

### Requirement: LCP 最佳化
第一張幻燈片 SHALL 在其圖片標籤上使用 `loading="eager"` 和 `fetchpriority="high"`。所有後續的幻燈片 SHALL 使用 `loading="lazy"`。

#### Scenario: 第一張幻燈片預先載入
- **WHEN** 輪播渲染時
- **THEN** 第一張幻燈片的 `<img>` 標籤具有 `loading="eager"` 和 `fetchpriority="high"`

#### Scenario: 其他幻燈片延遲載入
- **WHEN** 輪播渲染多張幻燈片時
- **THEN** 第一張之後的所有幻燈片，其 `<img>` 標籤皆具有 `loading="lazy"`

### Requirement: 響應式圖片傳遞
每張幻燈片 SHALL 使用 `<picture>` 元素，並包含用於手機版圖片的 `<source media="(max-width: 768px)">` 以及用於 PC 版圖片的 fallback `<img>`。

#### Scenario: 行動裝置檢視輪播
- **WHEN** 視窗寬度為 768px 或以下時
- **THEN** 顯示手機版圖片

#### Scenario: 桌面裝置檢視輪播
- **WHEN** 視窗寬度大於 768px 時
- **THEN** 顯示 PC 版圖片