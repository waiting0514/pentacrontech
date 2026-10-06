# 專案待辦事項清單 (Project TODOs)

此文件用於記錄專案未來的開發任務、優化項目與合規調整。您可以隨時在此新增其他待辦事項。

---

## 🛡️ Cookie 合規與 GA 引入 (Cookie Compliance & GA Roadmap)

> [!NOTE]
> 以下待辦事項請於**「決定引入 Google Analytics (GA4)」**且有歐盟地區訪客需求時一併啟動實作。

### 🟩 階段一：規劃與隱私政策更新
- [ ] **更新隱私權政策 (Privacy Policy)**
  - 在網站上建立或更新隱私權政策頁面。
  - 明確揭露網站所使用的 Cookie（包括 Laravel 核心必要 Cookie，以及未來加入的 GA 追蹤 Cookie）與其用途。

### 🟦 階段二：Cookie 同意橫幅 (Consent Banner) 實作 (二選一)
- [ ] **方案 A：整合第三方同意管理平台 (CMP) (推薦：省時、合規性高)**
  - 挑選合適的 CMP 服務（如 *Cookiebot*, *Klaro*, *Osano*）。
  - 將 CMP 的 JavaScript 載入代碼放置於 HTML 的 `<head>` 中。
  - 設定 CMP 自動掃描並封鎖未授權的追蹤腳本。
- [ ] **方案 B：自行開發 Consent Banner**
  - 在前端（如 Vue 組件中）實作一個提示橫幅，提供「接受全部」與「拒絕」按鈕。
  - 使用 `localStorage.getItem('cookie_consent')` 判斷使用者的選擇狀態。
  - 寫入對應的同意值（例如 `cookie_consent: 'accepted'` 或 `cookie_consent: 'rejected'`）。

### 🟪 階段三：GA4 腳本載入控制
- [ ] **與同意狀態聯動 (Consent Mode) 實作**
  - **若使用自行開發方案**：
    - 確保只有在 `localStorage.getItem('cookie_consent') === 'accepted'` 時，才執行 GA 初始化程式碼。
  - **若使用 Google Consent Mode V2**：
    - 預設將 `ad_storage` 與 `analytics_storage` 設定為 `'denied'`。
    - 當使用者在橫幅中點擊同意時，透過 `gtag('consent', 'update', ...)` 將狀態更新為 `'granted'`。

### 💡 實作提醒與最佳實踐
* ❌ **絕對不要** 在使用者還沒點擊「同意」前就載入 GA 腳本。
* ❌ 橫幅的設計上，**「拒絕」按鈕必須與「接受」按鈕一樣明顯**（GDPR 合規要求，禁止使用視覺陷阱誤導用戶）。

---

## 📝 其他待辦事項 (Other Tasks)

*在此處新增您其他的專案待辦事項...*
- [ ] 範例任務：優化首頁加載速度
- [ ] 範例任務：重構 API 回傳格式
