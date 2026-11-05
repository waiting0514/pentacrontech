# product-attachments Specification

## Purpose
TBD - created by archiving change add-product-pdf-attachments. Update Purpose after archive.

## Requirements

### Requirement: 產品附件的資料結構
系統 SHALL 允許一個產品擁有多份 PDF 附件。每份附件 SHALL 有多語系標題，並可在每個語系各自擁有一個 PDF 檔案；同一份附件在同一語系 SHALL 最多一個檔案。每份附件 SHALL 至少有一個語系的檔案。

#### Scenario: 同一附件的中英文檔案
- **WHEN** 使用者為一份附件分別上傳 `zh-TW` 與 `en` 的 PDF
- **THEN** 系統 SHALL 保存兩個各自獨立的檔案，並記錄各自的原檔名與檔案大小

#### Scenario: 沒有任何檔案
- **WHEN** 使用者建立附件但未提供任何語系的檔案
- **THEN** 系統 SHALL 拒絕並回傳驗證錯誤（HTTP 422），不建立附件

#### Scenario: 預設語系標題必填
- **WHEN** 使用者建立附件但未填預設語系（`zh-TW`）標題
- **THEN** 系統 SHALL 回傳驗證錯誤（HTTP 422）；其他語系標題為選填

### Requirement: PDF 上傳驗證與限制
系統 SHALL 只接受 PDF 檔案：驗證內容類型為 `application/pdf`，而非僅檢查副檔名。單檔大小上限預設 10 MB，每個產品附件數量上限預設 10 份；兩者 SHALL 可透過設定調整而不需修改程式碼。

#### Scenario: 上傳有效 PDF
- **WHEN** 使用者上傳不超過上限的有效 PDF
- **THEN** 系統 SHALL 接受並儲存

#### Scenario: 非 PDF 檔案
- **WHEN** 使用者上傳非 PDF 檔案（含副檔名改為 `.pdf` 的其他類型）
- **THEN** 系統 SHALL 拒絕並回傳驗證錯誤（HTTP 422）

#### Scenario: 超過大小上限
- **WHEN** 使用者上傳超過設定大小上限的 PDF
- **THEN** 系統 SHALL 拒絕並回傳驗證錯誤（HTTP 422），訊息說明上限

#### Scenario: 調整上限
- **WHEN** 管理者修改大小上限設定
- **THEN** 系統 SHALL 以新上限驗證後續上傳，無需修改程式碼

#### Scenario: 超過附件數量
- **WHEN** 產品已達附件數量上限，使用者再新增附件
- **THEN** 系統 SHALL 拒絕並回傳驗證錯誤（HTTP 422）

### Requirement: 後台附件管理
系統 SHALL 讓具備 `product.update` 權限的使用者在產品編輯頁新增、編輯與刪除附件；具備 `product.view` 者可檢視附件清單。編輯時 SHALL 可修改標題、替換或新增某語系的檔案、移除某語系的檔案，並調整排序。

#### Scenario: 新增附件
- **WHEN** 使用者填寫各語系標題並上傳檔案後送出
- **THEN** 系統 SHALL 建立附件並於附件清單顯示各語系標題與各語系檔案資訊

#### Scenario: 替換某語系的檔案
- **WHEN** 使用者為已有 `en` 檔案的附件上傳新的 `en` PDF
- **THEN** 系統 SHALL 以新檔案取代並刪除舊的實體檔案

#### Scenario: 移除某語系的檔案
- **WHEN** 使用者移除附件的其中一個語系檔案，且附件仍有其他語系檔案
- **THEN** 系統 SHALL 刪除該語系檔案與其實體檔案

#### Scenario: 不可移除最後一個檔案
- **WHEN** 使用者嘗試移除附件唯一剩下的檔案
- **THEN** 系統 SHALL 拒絕並回傳驗證錯誤（HTTP 422）；若要移除應刪除整份附件

#### Scenario: 刪除附件
- **WHEN** 使用者確認刪除一份附件
- **THEN** 系統 SHALL 刪除附件、其所有語系檔案紀錄與實體檔案

#### Scenario: 權限不足
- **WHEN** 沒有 `product.update` 權限的使用者嘗試新增、修改或刪除附件
- **THEN** 系統 SHALL 回傳 HTTP 403；未登入者 SHALL 回傳 HTTP 401

#### Scenario: 附件不屬於該產品
- **WHEN** 使用者以產品 A 的路徑操作產品 B 的附件
- **THEN** 系統 SHALL 拒絕（HTTP 404 或 403），不得影響該附件

### Requirement: 檔案儲存與清理
系統 SHALL 將 PDF 以隨機檔名存放於公開儲存空間，原檔名另存於資料庫；SHALL NOT 使用使用者提供的檔名作為儲存路徑。刪除產品時，其所有附件的實體檔案 SHALL 一併刪除。

#### Scenario: 隨機檔名
- **WHEN** 使用者上傳名為 `../../evil.pdf` 或含中文的檔案
- **THEN** 系統 SHALL 以隨機檔名儲存，儲存路徑不含使用者提供的檔名，原檔名僅作顯示

#### Scenario: 刪除產品
- **WHEN** 使用者刪除擁有附件的產品
- **THEN** 系統 SHALL 刪除該產品所有附件的實體檔案，不留下孤兒檔案

### Requirement: 前台公開下載
系統 SHALL 在產品詳情頁顯示「下載資料」區塊，列出該產品的附件，並依當前語系提供對應語系的 PDF；該語系沒有檔案時 SHALL 回退預設語系的檔案。檔案 SHALL 可由訪客直接下載，不需登入。

#### Scenario: 當前語系有檔案
- **WHEN** 訪客以英文瀏覽有 `en` 檔案的附件
- **THEN** 系統 SHALL 顯示英文標題並連到英文 PDF，並顯示檔案大小

#### Scenario: 語系回退
- **WHEN** 訪客以英文瀏覽只有 `zh-TW` 檔案的附件
- **THEN** 系統 SHALL 顯示並連到 `zh-TW` 檔案，且標示該檔案的語系

#### Scenario: 標題回退
- **WHEN** 附件缺少當前語系標題
- **THEN** 系統 SHALL 使用預設語系標題

#### Scenario: 沒有附件
- **WHEN** 產品沒有任何附件
- **THEN** 系統 SHALL 不顯示「下載資料」區塊

#### Scenario: 附件順序
- **WHEN** 產品有多份附件
- **THEN** 系統 SHALL 依排序值與建立順序顯示
