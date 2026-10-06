### Requirement: 產品圖片資料模型
系統 SHALL 將產品圖片存儲在 `product_images` 資料表中，包含 `product_id` (外鍵)、`paths` (包含 main/thumb/tiny 鍵值的 JSON)、`is_primary` (布林值) 和 `sort_order` (整數)。每個產品 MAY 擁有多張圖片。每個產品 SHALL 恰好有一張圖片被標記為主圖。

#### Scenario: 第一張圖片成為主圖
- **WHEN** 為沒有圖片的產品上傳第一張圖片時
- **THEN** 該圖片會自動被標記為 `is_primary = true`

#### Scenario: 刪除主圖
- **WHEN** 刪除主圖且存在其他圖片時
- **THEN** `sort_order` 排序中的下一張圖片將成為新的主圖

### Requirement: 產品圖片上傳 API
系統 SHALL 提供管理產品圖片的 API 接口：上傳 (POST，支援多檔案)、刪除 (DELETE，從儲存空間移除檔案)、設定主圖 (PUT) 以及重新排序 (PUT)。

#### Scenario: 上傳多張圖片
- **WHEN** 向 `/api/products/{product}/images` 發送帶有多個圖片檔案的 POST 請求時
- **THEN** 每張圖片都會透過 ImageService 處理並儲存為 ProductImage 紀錄，並回傳建立的紀錄

#### Scenario: 刪除圖片
- **WHEN** 向 `/api/products/{product}/images/{image}` 發送 DELETE 請求時
- **THEN** 圖片檔案（三種尺寸）將從儲存空間中刪除，且資料庫紀錄也會被移除

#### Scenario: 將圖片設定為主圖
- **WHEN** 向 `/api/products/{product}/images/{image}/primary` 發送 PUT 請求時
- **THEN** 指定的圖片被標記為主圖，且該產品的其他所有圖片都會取消標記

#### Scenario: 重新排序圖片
- **WHEN** 向 `/api/products/{product}/images/reorder` 發送帶有排序過的圖片 ID 陣列的 PUT 請求時
- **THEN** 每張圖片的 `sort_order` 都會依照陣列中的位置進行更新