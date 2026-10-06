# 部署說明文件

## 環境架構

- **伺服器**: Linode JP Tokyo 3（2GB RAM / 50GB Storage）
- **OS**: Ubuntu 24.04 LTS
- **Web Server**: Nginx
- **PHP**: 8.4-FPM
- **Database**: MySQL 8（直接安裝於主機，不使用 Docker）
- **CI/CD**: GitHub Actions（push to main 自動觸發）
- **Domain**: minzheco.com
- **SSL**: Let's Encrypt（Certbot）

---

## 服務規劃說明

原開發環境使用 Laravel Sail，包含 6 個 container（laravel、mysql、redis、meilisearch、mailpit、selenium）。

正式環境精簡為只安裝必要服務：

| 服務 | 決定 | 原因 |
|---|---|---|
| PHP + Nginx | ✅ 原生安裝 | 單一專案，不需 Docker 複雜度 |
| MySQL | ✅ 原生安裝 | 效能較好，備份簡單 |
| Redis | ❌ 暫不安裝 | Queue 和 Cache 皆未使用 |
| Meilisearch | ❌ 暫不安裝 | 搜尋功能尚未啟用 |
| Mailpit | ❌ 不安裝 | 僅供開發測試用 |
| Selenium | ❌ 不安裝 | 僅供自動化測試用 |

---

## 第一次部署流程

### Step 1｜設定 Linode 防火牆

到 Linode 後台 → Networking → Firewalls → Studio-Laravel-Srv → Rules，新增以下 Inbound Rules：

| Label | Protocol | Port | Sources | Action |
|---|---|---|---|---|
| SSH | TCP | 22 | All IPv4 / All IPv6 | Accept |
| HTTP | TCP | 80 | All IPv4 / All IPv6 | Accept |
| HTTPS | TCP | 443 | All IPv4 / All IPv6 | Accept |

新增後按 **Save Changes**。

### Step 2｜初始化伺服器

```bash
scp deploy/server-init.sh root@172.237.12.75:/tmp/
ssh root@172.237.12.75
bash /tmp/server-init.sh
```

安裝內容：Nginx、PHP 8.4-FPM + 擴充套件、MySQL 8、Node.js 20、Composer、deployer 使用者。

> ⚠️ **注意**：腳本會自動設定 deployer 的 sudo 權限讓 GitHub Actions 可以 reload PHP-FPM。如果之後有升級 PHP 版本，需要手動更新 sudoers 設定，否則自動部署最後一步會失敗：
> ```bash
> echo "deployer ALL=(ALL) NOPASSWD: /bin/systemctl reload php8.4-fpm" > /etc/sudoers.d/deployer
> chmod 440 /etc/sudoers.d/deployer
> ```
>
> 另外需要補上以下兩行，讓 deployer 可以在部署前修正 storage 目錄權限（否則 rsync 會因為 www-data 建立的快取檔案而失敗）：
> ```bash
> echo "deployer ALL=(ALL) NOPASSWD: /bin/chown -R deployer\:www-data /var/www/minzheco/storage" >> /etc/sudoers.d/deployer
> echo "deployer ALL=(ALL) NOPASSWD: /bin/chmod -R 775 /var/www/minzheco/storage" >> /etc/sudoers.d/deployer
> ```

### Step 3｜建立 MySQL 資料庫

```bash
sudo mysql
```

```sql
CREATE DATABASE minzheco CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'minzheco_user'@'localhost' IDENTIFIED BY '你的密碼';
GRANT ALL PRIVILEGES ON minzheco.* TO 'minzheco_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 4｜設定 SSH Keys

產生兩把 key：

```powershell
# GitHub Actions 部署用
ssh-keygen -t ed25519 -C "github-actions-deploy" -f "$env:USERPROFILE\.ssh\linode_minzheco"

# 個人登入伺服器用
ssh-keygen -t ed25519 -C "my-pc-minzheco" -f "$env:USERPROFILE\.ssh\linode_login"
```

將兩把公鑰加入伺服器：

```bash
mkdir -p /home/deployer/.ssh
chmod 700 /home/deployer/.ssh
echo "公鑰內容1" >> /home/deployer/.ssh/authorized_keys
echo "公鑰內容2" >> /home/deployer/.ssh/authorized_keys
chmod 600 /home/deployer/.ssh/authorized_keys
chown -R deployer:deployer /home/deployer/.ssh
```

### Step 5｜設定 GitHub Secrets

到 GitHub repo → Settings → Secrets and variables → Actions：

| Secret 名稱 | 值 |
|---|---|
| `LINODE_HOST` | `172.237.12.75` |
| `LINODE_USER` | `deployer` |
| `LINODE_PORT` | `22` |
| `LINODE_SSH_KEY` | `linode_minzheco` 私鑰全文 |

### Step 6｜設定 Nginx

```bash
sudo bash -c 'cat > /etc/nginx/sites-available/minzheco' << 'EOF'
# 貼上 deploy/nginx.conf 的內容
EOF
sudo ln -s /etc/nginx/sites-available/minzheco /etc/nginx/sites-enabled/minzheco
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

### Step 7｜Clone 專案並設定 .env

```bash
cd /var/www/minzheco
git clone https://你的TOKEN@github.com/waiting0514/prototype.git .
cp .env.example .env
nano .env
```

**.env 正式環境重要設定：**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://minzheco.com
APP_TIMEZONE=Asia/Taipei

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=minzheco
DB_USERNAME=minzheco_user
DB_PASSWORD=你的密碼

SESSION_DRIVER=database
CACHE_STORE=file
QUEUE_CONNECTION=database
SCOUT_DRIVER=database

OPENAI_API_KEY=你的key
PHOTOROOM_API_KEY=你的key
CLIPDROP_API_KEY=你的key
```

### Step 8｜初始化專案

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate
php artisan db:seed --class=LanguageSeeder
php artisan db:seed --class=ProjectSeeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan db:seed --class=DefaultCategorySeeder
php artisan storage:link
npm install && npm run build
```

> ⚠️ **注意**：不要跑 `db:seed`（全部），會帶入 ProductSeeder、NewsSeeder、BannerSeeder 等測試假資料。只跑上方列出的必要 Seeder 即可。
> 
> 預設 admin 帳號為 `admin@example.com` / `admin123`，**部署後請立即修改密碼**。

### Step 9｜設定目錄權限

```bash
chown -R deployer:www-data /var/www/minzheco
chmod -R 775 /var/www/minzheco
chmod -R 775 /var/www/minzheco/storage
chmod -R 775 /var/www/minzheco/bootstrap/cache
chown -R deployer:www-data /var/www/minzheco/storage/framework/cache
chmod -R 775 /var/www/minzheco/storage/framework/cache
```

> ⚠️ **注意**：之後若 GitHub Actions rsync 出現 `storage/framework/cache` 相關的 `Permission denied`，是因為 `www-data` 建立的快取檔案不允許 `deployer` 刪除，重新執行以下指令即可修正：
> ```bash
> chown -R deployer:www-data /var/www/minzheco/storage
> chmod -R 775 /var/www/minzheco/storage
> ```

### Step 10｜設定 HTTPS

DNS 生效後執行：

```bash
apt install certbot python3-certbot-nginx -y
certbot --nginx -d minzheco.com -d www.minzheco.com
```

### Step 11｜設定 Crontab（移交客戶後執行）

```bash
crontab -u deployer -e
```

加入：

```
* * * * * cd /var/www/minzheco && php artisan schedule:run >> /dev/null 2>&1
```

---

## 日常部署

push 到 `main` branch 後，GitHub Actions 自動執行：

1. 安裝 Composer 套件（無 dev）
2. Build 前端（`npm run build`）
3. rsync 同步到伺服器
4. 執行 `php artisan migrate --force`
5. 重建 config / route / view / event cache
6. reload PHP-FPM

也可在 GitHub Actions 頁面手動觸發（Run workflow 按鈕）。

---

## GitHub Actions Workflow 說明

Workflow 檔案位於 `.github/workflows/deploy.yml`，GitHub 會自動偵測此目錄下所有 `.yml` 檔案並在 Actions 頁面顯示，不需要額外設定。

Workflow 名稱由檔案內的 `name` 欄位決定，目前設定為 `Deploy to Linode`。

**觸發方式**：目前設定為手動觸發（`workflow_dispatch`），在 GitHub repo → Actions → Deploy to Linode → Run workflow 按鈕執行。

**Workflow 執行步驟說明**：

| 步驟 | 說明 |
|---|---|
| Checkout code | 取得最新程式碼 |
| Setup PHP | 在 Runner 安裝 PHP 8.4 |
| Install Composer dependencies | 安裝套件（排除 dev，正式環境用） |
| Setup Node.js | 在 Runner 安裝 Node.js 20 |
| Install npm dependencies & build | 安裝前端套件並 build |
| Setup SSH agent | 載入 GitHub Secret 的私鑰 |
| Add server to known_hosts | 將伺服器加入信任清單 |
| Sync files to server | rsync 同步檔案到伺服器（排除 .env、storage、快取等） |
| Run post-deploy commands | 執行 migrate、cache、reload PHP-FPM |

**rsync 排除清單說明**：

| 排除項目 | 原因 |
|---|---|
| `.git` / `.github` | 版控檔案不需上傳 |
| `node_modules` | 已在 Runner build，不需同步 |
| `tests` | 測試檔案不需上線 |
| `.env` | 正式環境 .env 放在伺服器，不能被覆蓋 |
| `storage/app` | 使用者上傳的圖片、檔案 |
| `storage/logs` | 伺服器 log |
| `storage/framework/cache` | Laravel 快取，runtime 自動產生 |
| `storage/framework/sessions` | 使用者 session |
| `storage/framework/views` | Blade 編譯快取，runtime 自動產生 |
| `deploy` / `*.sh` | 部署說明文件，不需上傳 |

**套用到其他專案時需修改的地方**：

- `deploy.yml` 中所有 `/var/www/minzheco` 改成新專案路徑
- GitHub Secrets 名稱建議加上專案名稱區分（如 `LINODE_PROJECTNAME_SSH_KEY`）
- PHP 版本視專案需求調整

---

## 遇到的問題與解法

### 問題 1｜PHP 版本不符

**症狀**: `composer install` 失敗，提示套件需要 PHP >= 8.4

**原因**: 開發環境用 Laravel Sail（PHP 8.5），`composer.lock` 鎖定了需要 PHP 8.4+ 的套件版本，但伺服器安裝的是 PHP 8.2。

**解法**: 將伺服器 PHP 升級至 8.4，同時更新 `nginx.conf` 中的 socket 路徑和 `deploy.yml` 中的 PHP 版本設定。

---

### 問題 2｜Nginx 設定檔語法錯誤

**症狀**: `nginx -t` 回報 `unexpected end of file` 在第 44 行

**原因**: 上傳的 `nginx.conf` 檔案結尾有 null bytes（`^@`）殘留

**解法**: 直接在伺服器用 `tee` 指令重新寫入設定檔內容

---

### 問題 3｜storage 目錄權限問題

**症狀**: 瀏覽器顯示 HTTP 500，Nginx log 顯示 `Permission denied` 無法寫入 `storage/logs/laravel.log`

**原因**: Nginx 以 `www-data` 使用者執行，但 `storage` 目錄屬於 `deployer`，沒有寫入權限

**解法**:
```bash
chown -R www-data:www-data /var/www/minzheco/storage
chown -R www-data:www-data /var/www/minzheco/bootstrap/cache
chmod -R 775 /var/www/minzheco/storage
```

---

### 問題 4｜GitHub Actions rsync Permission denied

**症狀**: rsync 上傳時大量 `Permission denied (13)` 錯誤

**原因**: `storage` 和 `bootstrap/cache` 目錄屬於 `www-data`，`deployer` 無法寫入

**解法**: 將所有者改為 `deployer`，群組設為 `www-data`，確保兩者都有寫入權限：
```bash
chown -R deployer:www-data /var/www/minzheco
chmod -R 775 /var/www/minzheco
```

---

### 問題 5｜GitHub Actions sudo 需要密碼

**症狀**: Post-deploy 步驟執行 `sudo systemctl reload php8.4-fpm` 失敗，提示 `a password is required`

**原因**: deployer 使用者沒有 NOPASSWD 權限執行 systemctl

**解法**:
```bash
echo "deployer ALL=(ALL) NOPASSWD: /bin/systemctl reload php8.4-fpm" > /etc/sudoers.d/deployer
chmod 440 /etc/sudoers.d/deployer
```

---

### 問題 6｜API 回傳 500 / spatie/laravel-permission 錯誤

**症狀**: `/api/banners`、`/api/news`、`/api/products` 全部回傳 500，log 顯示 `file_put_contents Permission denied`

**原因**: 兩個問題同時發生：
1. `storage/framework/cache` 目錄權限不足，spatie/laravel-permission 無法寫入快取
2. `RolesAndPermissionsSeeder` 尚未執行，權限資料不存在導致 `authorize()` 失敗

**解法**:
```bash
cd /var/www/minzheco

# 修正 cache 目錄權限
chown -R deployer:www-data storage/framework/cache
chmod -R 775 storage/framework/cache

# 補跑必要的 Seeder
php artisan db:seed --class=LanguageSeeder
php artisan db:seed --class=ProjectSeeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan db:seed --class=DefaultCategorySeeder

# 清除並重建快取
php artisan cache:clear
php artisan config:cache
```

---

### 問題 7｜Linode 防火牆未開放 Port

**症狀**: 瀏覽器顯示 `ERR_CONNECTION_TIMED_OUT`

**原因**: Linode Firewall（Studio-Laravel-Srv）預設未開放 HTTP(80) 和 HTTPS(443)

**解法**: 到 Linode 後台 → Networking → Firewalls → Studio-Laravel-Srv → Rules，新增 TCP Port 80 和 443 的 Inbound Rules。

---

## Mail 設定（待處理）

等客戶確認後設定 Gmail SMTP。

客戶設定應用程式密碼的參考教學：
https://www.ibest.com.tw/news-detail/gmail-smtp/

後台已有 SMTP 設定頁面，客戶取得應用程式密碼後直接在後台填入即可，不需修改伺服器設定。

---

## Crontab（待處理）

移交客戶後設定，用於每年自動重設 AI 使用額度（`usage:reset-yearly`）。

```bash
crontab -u deployer -e
# 加入：
* * * * * cd /var/www/minzheco && php artisan schedule:run >> /dev/null 2>&1
```

---

## 檔案結構

```
deploy/
├── server-init.sh   # 伺服器初始化腳本（只需執行一次）
├── nginx.conf       # Nginx 設定範本
└── README.md        # 本說明文件

.github/
└── workflows/
    └── deploy.yml   # GitHub Actions 自動部署設定
```
