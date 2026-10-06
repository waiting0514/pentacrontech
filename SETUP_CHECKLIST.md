# AI Agency Backend - 最終檢查清單

## ✅ 系統已完成

執行以下命令確認系統正常運作:

### 1. 檢查服務狀態

```bash
# 確認 Docker 容器運行中
./vendor/bin/sail ps

# 應該看到: laravel.test, mysql, redis 等容器
```

### 2. 檢查資料庫

```bash
# 確認所有 Migrations 已執行
./vendor/bin/sail artisan migrate:status

# 應該看到所有 migration 都是 "Ran"
```

### 3. 建立測試使用者

```bash
./vendor/bin/sail artisan db:seed --class=UserSeeder
```

輸出應該顯示:
```
Test users created successfully!
Test User: test@example.com / password
Admin: admin@example.com / admin123
```

### 4. 啟動前端

```bash
./vendor/bin/sail npm run dev
```

應該看到:
```
VITE v5.x.x  ready in xxx ms
➜  Local:   http://localhost:5173/
➜  Network: use --host to expose
```

### 5. 測試登入

1. 開啟瀏覽器: **http://localhost**
2. 應該自動跳轉到登入頁面
3. 輸入測試帳號:
   - Email: `test@example.com`
   - Password: `password`
4. 點擊「登入」
5. 應該成功跳轉到 Dashboard

### 6. 測試 API

在 Dashboard 中:

**測試產品管理**:
- 點擊「新增產品」
- 填寫產品資訊
- 點擊「儲存」
- 確認產品出現在列表中

**測試 AI 助手**:
- 切換到「AI 工具」Tab
- 在 AI 助手輸入: "推薦一款產品"
- 確認 AI 有回應

**測試使用量監控**:
- 切換到「使用量監控」Tab
- 確認顯示當月使用量資訊

---

## 🔧 如果遇到問題

### 問題 1: 登入後 API 回傳 500 錯誤

**檢查**:
```bash
# 查看 Laravel 日誌
./vendor/bin/sail artisan tail

# 或
Get-Content storage/logs/laravel.log -Tail 50
```

**常見原因**:
- API 金鑰未設定 (檢查 `.env`)
- 資料表不存在 (執行 `migrate`)

### 問題 2: 前端無法連接 API

**檢查**:
1. Vite 是否正在運行
2. 瀏覽器 Console 是否有 CORS 錯誤
3. Token 是否正確儲存在 localStorage

### 問題 3: 無法建立測試使用者

**可能原因**: `activity_log` 資料表不存在

**解決**:
```bash
# 發布 Activity Log migration
./vendor/bin/sail artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"

# 執行 migration
./vendor/bin/sail artisan migrate
```

---

## 📋 最終確認

- [ ] Docker 容器運行中
- [ ] 所有 Migrations 已執行
- [ ] 測試使用者已建立
- [ ] Vite 開發伺服器運行中
- [ ] 可以成功登入
- [ ] 產品管理功能正常
- [ ] AI 工具可以使用
- [ ] 使用量監控顯示正常

---

## 🎉 全部完成!

如果以上所有項目都打勾,恭喜您!系統已經完全準備好了。

**開始使用**: http://localhost

**測試帳號**: test@example.com / password

享受您的 AI Agency Backend! 🚀
