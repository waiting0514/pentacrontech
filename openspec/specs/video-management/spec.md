# video-management Specification

## Purpose
讓客戶在後台以 YouTube 網址新增、編輯、刪除影片，並管理名稱、簡介、SEO 欄位、排序與啟用狀態，且受 `video.manage` 權限控管。

## Requirements

### Requirement: 以 YouTube 網址建立影片
系統 SHALL 允許具備 `video.manage` 權限的使用者貼上 YouTube 網址與標題來新增影片。系統 SHALL 從網址解析出 11 碼的 YouTube 影片 ID 並儲存；網址本身不作為播放依據。

#### Scenario: 貼上標準觀看網址
- **WHEN** 使用者送出標題與 `https://www.youtube.com/watch?v=dQw4w9WgXcQ`
- **THEN** 系統 SHALL 建立影片，影片 ID 為 `dQw4w9WgXcQ`

#### Scenario: 貼上短網址或 Shorts 網址
- **WHEN** 使用者送出 `https://youtu.be/dQw4w9WgXcQ` 或 `https://www.youtube.com/shorts/dQw4w9WgXcQ`
- **THEN** 系統 SHALL 解析出相同影片 ID 並建立影片

#### Scenario: 網址含額外參數
- **WHEN** 使用者送出 `https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=30s&list=PL123`
- **THEN** 系統 SHALL 只擷取影片 ID，忽略其他參數

#### Scenario: 非 YouTube 或格式錯誤的網址
- **WHEN** 使用者送出非 YouTube 網址（例如 `https://example.com/video`）或無法解析出影片 ID 的網址
- **THEN** 系統 SHALL 拒絕並回傳驗證錯誤（HTTP 422），不建立任何資料

#### Scenario: 缺少必填欄位
- **WHEN** 使用者未提供 `zh-TW` 標題或未提供網址
- **THEN** 系統 SHALL 回傳驗證錯誤（HTTP 422）

#### Scenario: 重複的影片
- **WHEN** 使用者新增的影片 ID 已存在於其他影片
- **THEN** 系統 SHALL 拒絕並回傳驗證錯誤（HTTP 422）

### Requirement: 影片名稱、簡介與 SEO 欄位
系統 SHALL 讓使用者為每支影片輸入影片名稱（標題）、簡介、網址代稱（slug）、Meta Title、Meta Description 與 Meta Keywords，其中名稱、簡介與 Meta 欄位 SHALL 支援多語系，slug 為單一值。僅 `zh-TW` 標題為必填，其餘皆為選填。

#### Scenario: 填寫 SEO 欄位
- **WHEN** 使用者在新增或編輯影片時填寫 Meta Title、Meta Description、Meta Keywords
- **THEN** 系統 SHALL 保存各語系的值，並在編輯表單回填

#### Scenario: slug 留空自動產生
- **WHEN** 使用者未提供 slug
- **THEN** 系統 SHALL 依標題自動產生 SEO 友善的 slug（小寫英數與連字號），行為與新聞一致

#### Scenario: 使用者提供 slug
- **WHEN** 使用者提供 slug
- **THEN** 系統 SHALL 以標準化後的值儲存，且不得與其他影片衝突（衝突時自動附加數字後綴）

#### Scenario: SEO 欄位留空
- **WHEN** Meta Title、Meta Description 或 Meta Keywords 未填
- **THEN** 系統 SHALL 允許儲存；前台以標題與簡介回退顯示

### Requirement: 影片列表
系統 SHALL 在後台影片管理頁面列出所有影片，依排序值與建立順序排列，並顯示縮圖、標題、排序與啟用狀態。縮圖 SHALL 由影片 ID 推導，系統不儲存任何影片或縮圖檔案。

#### Scenario: 顯示列表
- **WHEN** 使用者進入影片管理頁面
- **THEN** 系統 SHALL 顯示所有影片的縮圖、當前語系標題、排序與啟用狀態

#### Scenario: 無影片
- **WHEN** 尚未建立任何影片
- **THEN** 系統 SHALL 顯示空狀態與新增按鈕

### Requirement: 編輯影片
系統 SHALL 允許使用者修改影片的標題、YouTube 網址、排序與啟用狀態。修改網址時 SHALL 重新解析並驗證影片 ID。

#### Scenario: 修改標題與狀態
- **WHEN** 使用者更新標題或切換啟用狀態並儲存
- **THEN** 系統 SHALL 保存變更並於列表反映

#### Scenario: 更換網址
- **WHEN** 使用者將網址改為另一支有效的 YouTube 影片
- **THEN** 系統 SHALL 更新影片 ID 與縮圖

#### Scenario: 更換為無效網址
- **WHEN** 使用者將網址改為無效值
- **THEN** 系統 SHALL 回傳驗證錯誤（HTTP 422），並保留原有資料

#### Scenario: 編輯時網址不變
- **WHEN** 使用者儲存時網址仍為該影片原本的網址
- **THEN** 系統 SHALL 不因「影片 ID 重複」而拒絕

### Requirement: 刪除影片
系統 SHALL 允許使用者刪除影片，刪除前 SHALL 顯示確認對話框。

#### Scenario: 確認刪除
- **WHEN** 使用者點擊刪除並在對話框中確認
- **THEN** 系統 SHALL 刪除該影片並自列表移除

#### Scenario: 取消刪除
- **WHEN** 使用者在確認對話框中取消
- **THEN** 系統 SHALL 不刪除任何資料

### Requirement: 影片排序與啟用狀態
系統 SHALL 為每支影片保存排序值與啟用狀態；新增時預設為啟用。排序與狀態 SHALL 對所有語系一致。

#### Scenario: 預設啟用
- **WHEN** 新增影片時未指定啟用狀態
- **THEN** 系統 SHALL 將影片設為啟用

#### Scenario: 停用影片
- **WHEN** 使用者將影片設為停用
- **THEN** 系統 SHALL 保留資料並標示為停用，供日後前台過濾使用

### Requirement: 影片管理權限
所有影片管理操作 SHALL 需要 `video.manage` 權限。`super-admin`、`manager` 與 `editor` 角色 SHALL 預設擁有此權限；後台選單 SHALL 僅對具備此權限的使用者顯示「影片管理」。

#### Scenario: 無權限的 API 呼叫
- **WHEN** 沒有 `video.manage` 權限的已登入使用者呼叫影片 API
- **THEN** 系統 SHALL 回傳 HTTP 403

#### Scenario: 未登入
- **WHEN** 未登入者呼叫影片 API
- **THEN** 系統 SHALL 回傳 HTTP 401

#### Scenario: 選單可見性
- **WHEN** 沒有 `video.manage` 權限的使用者進入後台
- **THEN** 選單 SHALL 不顯示「影片管理」，且直接輸入路徑 SHALL 被路由守衛擋下
