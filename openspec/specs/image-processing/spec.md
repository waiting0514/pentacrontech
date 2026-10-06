### Requirement: 圖片處理服務
系統 SHALL 提供一個 `ImageService` 類別，其中包含 `processProductImage` 方法。該方法接受 `UploadedFile` 和可選的資料夾名稱，使用 Intervention Image v3 將圖片處理為三個 1:1 的 JPG 版本（主圖 1000x1000、縮圖 500x500、微圖 150x150），採用包含（contain）策略並以品質 80 填充白色背景，然後將其儲存在公開磁碟的 `uploads/{folder}/{size}/` 路徑中。所有使用 ImageService 的 API 控制器 SHALL 以統一的 ApiResponse 格式回傳回應。

#### Scenario: 處理上傳的圖片
- **WHEN** 將圖片檔案傳遞給 `processProductImage` 時
- **THEN** 系統生成三個 JPG 檔案，分別為 1000x1000、500x500 和 150x150 像素，具有 1:1 長寬比及白色背景填充，並回傳包含 `main`、`thumb`、`tiny` 鍵值的陣列（對應相對檔案路徑），控制器則以統一的成功格式回傳結果

#### Scenario: 非方形圖片處理
- **WHEN** 處理非方形圖片（例如 1600x900）時
- **THEN** 圖片被包含（fit within square without cropping）並填充白色（#ffffff）背景以填滿方形

#### Scenario: 唯一檔案名稱
- **WHEN** 處理多張圖片時
- **THEN** 每張圖片使用日期和 UUID 模式獲得唯一的檔案名稱，以防止衝突

### Requirement: Banner 圖片處理
ImageService SHALL 提供 `processBannerImage` 方法，接受 UploadedFile 和類型（'pc' 或 'mobile'）。PC 圖片 SHALL 調整大小為 1920px 寬度並保持長寬比。Mobile 圖片 SHALL 調整大小為 800px 寬度並保持長寬比。兩者 SHALL 皆轉換為品質 85 的 JPG。

#### Scenario: 處理 PC Banner 圖片
- **WHEN** 以類型 'pc' 呼叫 `processBannerImage` 時
- **THEN** 圖片調整大小為 1920px 寬度（保持長寬比），並以品質 85 儲存為 JPG 於 `uploads/banners/pc/`

#### Scenario: 處理 Mobile Banner 圖片
- **WHEN** 以類型 'mobile' 呼叫 `processBannerImage` 時
- **THEN** 圖片調整大小為 800px 寬度（保持長寬比），並以品質 85 儲存為 JPG 於 `uploads/banners/mobile/`

#### Scenario: 圖片小於目標寬度
- **WHEN** 上傳的圖片小於目標寬度時
- **THEN** 圖片 NOT 放大，僅以品質 85 轉換為 JPG