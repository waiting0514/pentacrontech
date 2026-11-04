# video-frontend Specification

## Purpose
在前台提供獨立的影片列表與詳情頁，內嵌 YouTube 播放並套用每支影片的 SEO 設定，同時納入頁尾連結與 sitemap。

## Requirements

### Requirement: 前台影片列表頁
系統 SHALL 提供獨立的影片列表頁（各語系路徑，如 `/videos`），只顯示啟用的影片，依排序值與建立順序排列並分頁。每個項目 SHALL 顯示縮圖、影片名稱與簡介摘要，並連到該影片的詳情頁。

#### Scenario: 顯示啟用影片
- **WHEN** 訪客開啟影片列表頁
- **THEN** 系統 SHALL 只列出啟用的影片，以當前語系顯示名稱；該語系無值時 SHALL 回退預設語系

#### Scenario: 停用影片不顯示
- **WHEN** 影片被設為停用
- **THEN** 該影片 SHALL 不出現在列表

#### Scenario: 沒有影片
- **WHEN** 沒有任何啟用的影片
- **THEN** 系統 SHALL 顯示空狀態訊息而非錯誤

#### Scenario: 分頁
- **WHEN** 啟用影片數量超過每頁數量
- **THEN** 系統 SHALL 顯示分頁導覽

#### Scenario: 列表頁 SEO
- **WHEN** 列表頁輸出
- **THEN** 系統 SHALL 設定該語系的頁面標題、描述與 canonical，並納入語系切換與 hreflang 慣例

### Requirement: 前台影片詳情頁
系統 SHALL 為每支啟用影片提供詳情頁（`/videos/{id}-{slug}`），內嵌 YouTube 播放器並顯示名稱與簡介。播放器 SHALL 使用隱私加強網域嵌入並延遲載入，不得自動播放。

#### Scenario: 正常顯示
- **WHEN** 訪客開啟啟用影片的詳情頁
- **THEN** 系統 SHALL 顯示內嵌播放器、名稱與簡介，播放器不自動播放

#### Scenario: slug 不符時導向
- **WHEN** 訪客以只有 ID 或過期 slug 的網址開啟
- **THEN** 系統 SHALL 301 導向正確的 `{id}-{slug}` 網址

#### Scenario: 停用或不存在的影片
- **WHEN** 訪客開啟停用或不存在的影片
- **THEN** 系統 SHALL 回傳 404

### Requirement: 影片詳情頁 SEO
詳情頁 SHALL 使用該影片的 Meta Title、Meta Description、Meta Keywords 輸出對應標籤；欄位未填時 SHALL 以影片名稱與簡介回退。頁面 SHALL 輸出 `VideoObject` 結構化資料，並以 YouTube 縮圖作為 Open Graph 圖片。

#### Scenario: 自訂 SEO 欄位
- **WHEN** 影片已填寫 Meta Title、Description、Keywords
- **THEN** 頁面 `<title>`、description、keywords 與 Open Graph 標籤 SHALL 使用這些值

#### Scenario: SEO 欄位回退
- **WHEN** 影片未填 Meta Title 與 Meta Description
- **THEN** 頁面 SHALL 以影片名稱與簡介摘要作為標題與描述

#### Scenario: 結構化資料
- **WHEN** 詳情頁輸出
- **THEN** 頁面 SHALL 包含 `VideoObject` JSON-LD，含名稱、描述、縮圖與嵌入網址

### Requirement: 影片頁的可發現性
系統 SHALL 在網站頁尾提供影片列表連結，且 sitemap SHALL 收錄影片列表頁與所有啟用影片的詳情頁。

#### Scenario: 頁尾連結
- **WHEN** 訪客檢視任一前台頁面頁尾
- **THEN** 頁尾 SHALL 含連到影片列表頁的連結

#### Scenario: sitemap 收錄
- **WHEN** 產生 sitemap
- **THEN** 啟用影片的詳情頁 SHALL 被收錄，停用影片 SHALL 不收錄
