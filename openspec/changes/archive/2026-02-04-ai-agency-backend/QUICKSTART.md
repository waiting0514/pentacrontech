# AI Agency Backend - 快速啟動指南

## 🚀 快速開始

### 1. 啟動 Docker 環境

```bash
# 啟動 Laravel Sail
./vendor/bin/sail up -d
```

### 2. 執行資料庫遷移

```bash
# 執行 migrations
./vendor/bin/sail artisan migrate

# (選填) 建立測試資料
./vendor/bin/sail artisan tinker
>>> $user = User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'monthly_budget_usd' => 50.00
]);
```

### 3. 配置 API 金鑰

編輯 `.env` 檔案:

```env
# OpenAI (必須)
OPENAI_API_KEY=sk-your-actual-openai-key

# Photoroom (選填)
PHOTOROOM_API_KEY=your-photoroom-key

# Clipdrop (選填)
CLIPDROP_API_KEY=your-clipdrop-key

# Scout
SCOUT_DRIVER=database
```

### 4. 啟動前端開發伺服器

```bash
# 啟動 Vite
./vendor/bin/sail npm run dev
```

### 5. 訪問應用程式

開啟瀏覽器:
- **後台管理**: http://localhost/dashboard
- **API 文件**: http://localhost/api (需配置)

---

## 📋 檢查清單

在開始使用前,請確認:

- [x] Docker 已安裝並運行
- [x] Laravel Sail 容器已啟動
- [x] 資料庫 migrations 已執行
- [x] `.env` 已配置 API 金鑰
- [x] Vite 開發伺服器已啟動
- [ ] 已建立測試使用者帳號

---

## 🔧 常見問題排除

### Vite 錯誤: "Failed to parse source"

**已解決!** `vite.config.js` 已加入 Vue 插件。

如果仍有問題:
```bash
# 清除快取並重新安裝
./vendor/bin/sail npm clean-install
./vendor/bin/sail npm run dev
```

### API 回傳 401 Unauthorized

**原因**: 缺少認證 Token

**解決方案**:
1. 使用 Laravel Sanctum 生成 Token
2. 在 `localStorage` 儲存 Token:
   ```javascript
   localStorage.setItem('api_token', 'your-token-here');
   ```

### 圖片上傳失敗

```bash
# 建立 storage link
./vendor/bin/sail artisan storage:link

# 設定權限
./vendor/bin/sail exec laravel.test chmod -R 775 storage
```

### Scout 搜尋無結果

```bash
# 重建索引
./vendor/bin/sail artisan scout:import "App\Models\Product"
```

---

## 📚 相關文件

- [前端使用說明](./FRONTEND_GUIDE.md)
- [實作計畫](file:///C:/Users/User/.gemini/antigravity/brain/0938269b-a6c9-4314-afe8-733a348b828b/implementation_plan.md)
- [完整總結](file:///C:/Users/User/.gemini/antigravity/brain/0938269b-a6c9-4314-afe8-733a348b828b/walkthrough.md)

---

## 🎯 下一步

1. **建立測試產品**:
   - 訪問 http://localhost/dashboard
   - 切換到「產品管理」Tab
   - 點擊「新增產品」

2. **測試 AI 功能**:
   - 切換到「AI 工具」Tab
   - 使用 AI 助手進行對話
   - 上傳產品圖片測試 Vision Parse

3. **監控使用量**:
   - 切換到「使用量監控」Tab
   - 查看 API 呼叫記錄和成本

---

## 💡 開發提示

### 熱重載

Vite 支援熱重載,修改 Vue 元件後會自動更新瀏覽器。

### 除錯

開啟瀏覽器 DevTools:
- **Console**: 查看 JavaScript 錯誤
- **Network**: 檢查 API 請求
- **Vue DevTools**: 安裝 Vue.js devtools 擴充功能

### 生產環境建置

```bash
# 建置前端資源
./vendor/bin/sail npm run build

# 優化 Laravel
./vendor/bin/sail artisan optimize
```

---

## 🆘 需要協助?

查看詳細文件或聯繫技術支援。
