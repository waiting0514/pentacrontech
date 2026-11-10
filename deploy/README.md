# 部署說明文件

> ## ⚠️ 部署前必改：每個站一份設定檔，路徑要一致
>
> 部署腳本不再寫死站台資料，**每個站的設定集中在 `deploy/project.conf`**（由 `project.conf.example` 複製而來，含資料庫密碼，已被 `.gitignore` 排除，不要 commit）。
> 同一台伺服器放多個站時，每個站各自一份設定檔，同一支腳本重複使用（見下方「新增另一個站」）。
>
> 下列位置的**網域與資料夾必須一致**，否則部署會失敗或寫到錯的資料夾：
>
> | 位置 | 要設定的內容 |
> |---|---|
> | `deploy/project.conf` | `DOMAIN`、`APP_DIR`、`GITHUB_REPO`、`DB_NAME`、`DB_USER`、`DB_PASS`、`GITHUB_ACTIONS_PUBKEY`、`PHP_VERSION`、`SEED_CLASS` |
> | `.github/workflows/deploy.yml` | 最上方 `env.APP_DIR`（需等於 `project.conf` 的 `APP_DIR`）；`sudo systemctl reload php8.4-fpm` 的 PHP 版本 |
> | `deploy/server-init.sh` | `PHP_VERSION`（需與 `project.conf`、`deploy.yml` 一致） |
> | 伺服器上的 `.env` | `APP_URL`、`DB_CONNECTION=mysql`、`DB_DATABASE`、`DB_USERNAME`、`DB_PASSWORD`（需與 `project.conf` 一致，腳本會檢查） |
> | 伺服器上的 crontab | `cd <APP_DIR>` |
> | 本文件 | 下方指令中的 `/var/www/minzheco`、`minzheco.com`、IP `172.237.12.75` 是範例，請替換成實際值 |
>
> Nginx 設定由 `project-init.sh` 依 `nginx.conf.template` 自動產生，不需要手動修改範本。
> `/etc/sudoers.d/deployer` 內的 storage 權限也由 `project-init.sh` 依 `APP_DIR` 追加；專案初始化後才改路徑時，記得同步修改該檔案（見 Step 2 注意事項）。

## 環境架構

- **伺服器**: Linode JP Tokyo 3（2GB RAM / 50GB Storage）
- **OS**: Ubuntu 24.04 LTS
- **Web Server**: Nginx
- **PHP**: 8.4-FPM
- **Database**: MySQL 8（直接安裝於主機，不使用 Docker）
- **CI/CD**: GitHub Actions（手動觸發；先跑測試，通過才部署）
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

安裝內容：Nginx、PHP 8.4-FPM + 擴充套件、MySQL 8、Node.js 22、Composer、deployer 使用者。

> ⚠️ **注意**：deployer 需要兩組免密碼 sudo 權限，都寫在 `/etc/sudoers.d/deployer`：
>
> 1. `server-init.sh` 設定：reload PHP-FPM（部署最後一步）。
> 2. `project-init.sh` 依 `APP_DIR` 設定：`chown` / `chmod` storage 目錄（部署時 rsync 前的「Fix storage permissions」步驟）。
>
> 完成後內容應類似：
> ```
> deployer ALL=(ALL) NOPASSWD: /bin/systemctl reload php8.4-fpm
> deployer ALL=(ALL) NOPASSWD: /usr/bin/chown -R deployer\:www-data /var/www/minzheco/storage
> deployer ALL=(ALL) NOPASSWD: /usr/bin/chmod -R 775 /var/www/minzheco/storage
> ```
>
> 之後**升級 PHP 版本**或**更改部署資料夾**時，要用 `sudo visudo -f /etc/sudoers.d/deployer` 修改對應那一行。不要用 `echo ... > /etc/sudoers.d/deployer` 覆寫，會把其他行一起刪掉，導致部署失敗。

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

說明：

- **只需要這 4 個**，`deploy.yml` 沒有引用其他 Secrets。資料庫密碼、`APP_KEY`、OpenAI 等 API key 都放在**伺服器的 `.env`**，不是 GitHub Secrets（rsync 也不會覆蓋 `.env`）。
- **Secrets 屬於單一 repo，不會跨 repo 共用。** 新專案的 repo 要重新建立這 4 個（名稱維持相同即可，workflow 不用改）。
- 同一台 Linode 的多個專案，`LINODE_HOST`、`LINODE_USER`、`LINODE_PORT` 通常相同。
- `LINODE_SSH_KEY` 可以重用既有的部署私鑰（對應公鑰已在伺服器 `deployer` 的 `authorized_keys`），但建議**每個專案各產生一把**，一把外洩才不會影響其他專案。新的公鑰要填入 `project-init.sh` 的 `GITHUB_ACTIONS_PUBKEY`。
- 貼私鑰時要包含 `-----BEGIN ... -----` 與 `-----END ... -----` 兩行，結尾保留換行，否則可能出現 `error in libcrypto`。
- 同一台伺服器放多個專案時，`/etc/sudoers.d/deployer` 需要包含每個專案的 storage 路徑；`project-init.sh` 每跑一次會追加該專案的設定，不會覆蓋其他專案。

### Step 6｜設定 Nginx

**使用 `project-init.sh` 時不需要手動做**，腳本最後一步會依 `nginx.conf.template` 產生 `/etc/nginx/sites-available/<DOMAIN>`、建立 `sites-enabled` 連結並 reload（已存在的設定不會被覆蓋，也不會動到其他站）。

手動設定時，把範本的 `__DOMAIN__`、`__APP_DIR__`、`__PHP_VERSION__` 換成實際值：

```bash
DOMAIN=minzheco.com; APP_DIR=/var/www/minzheco; PHP_VERSION=8.4
sed -e '/^#/d' -e "s#__DOMAIN__#$DOMAIN#g" -e "s#__APP_DIR__#$APP_DIR#g" -e "s#__PHP_VERSION__#$PHP_VERSION#g" \
  deploy/nginx.conf.template | sudo tee /etc/nginx/sites-available/$DOMAIN > /dev/null
sudo ln -sfn /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/$DOMAIN
sudo nginx -t && sudo systemctl reload nginx
```

> ⚠️ 只有在**確定不會影響其他站**時才移除 `sites-enabled/default`。多站伺服器請先 `ls /etc/nginx/sites-enabled/` 確認每個站的 `server_name` 都不同，不要刪除其他站的連結。

### Step 7｜Clone 專案並設定 .env

> 💡 Step 3、6、7、8、9 也可以改用 `deploy/project-init.sh` 一次完成：複製 `project.conf.example` 為 `project.conf` 並填好，再以 root 執行 `bash project-init.sh`（或 `bash project-init.sh /path/to/site.conf` 指定設定檔）。

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
APP_LOCALE=zh-TW

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
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder --force
php artisan storage:link
npm ci && npm run build
```

`ProductionSeeder` 只建立系統必要資料：語言、全站設定（`site_settings`）、admin 帳號、角色權限、預設分類（未分類）。

> ⚠️ **注意**：
> - 不要跑 `db:seed`（全部）。那會帶入示範產品、新聞、輪播圖等假資料。
> - 在 `APP_ENV=production` 下，`UserSeeder`、`RolesAndPermissionsSeeder` 不會建立 `test`、`manager` 這兩個示範帳號。
> - 正式環境的 artisan 指令會要求確認，在腳本裡要加 `--force`。
>
> 預設 admin 帳號為 **`admin` / `admin123`**（登入時輸入帳號，不是 email），**部署後請立即修改密碼**。

### Step 9｜設定目錄權限

```bash
chown -R deployer:www-data /var/www/minzheco
chmod -R 775 /var/www/minzheco
chmod -R 775 /var/www/minzheco/storage
chmod -R 775 /var/www/minzheco/bootstrap/cache
chown -R deployer:www-data /var/www/minzheco/storage/framework/cache
chmod -R 775 /var/www/minzheco/storage/framework/cache

# 讓 GitHub Actions 部署前能自動修正 storage 權限（使用 project-init.sh 時已自動完成）
sudo visudo -f /etc/sudoers.d/deployer
# 加入以下兩行（路徑須與 deploy.yml 的 APP_DIR 一致）：
# deployer ALL=(ALL) NOPASSWD: /usr/bin/chown -R deployer\:www-data /var/www/minzheco/storage
# deployer ALL=(ALL) NOPASSWD: /usr/bin/chmod -R 775 /var/www/minzheco/storage
```

> ⚠️ **注意**：
> - `www-data` 建立的快取檔案，`deployer` 不能刪除，會讓 rsync 出現 `Permission denied`。
> - workflow 的「Fix storage permissions」步驟會在每次 rsync 前自動修正這個問題。
> - 如果這個步驟出現 `sudo: a password is required`,代表上面兩行 sudoers 沒設好,或路徑與 `APP_DIR` 不一致。

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

## 新增另一個站（同一台伺服器）

同一台 Linode 可以放多個 Laravel 站，每個站各自擁有：專案資料夾、資料庫與帳號、`.env`、Nginx 站台檔、HTTPS 憑證、GitHub repo 與 Secrets。共用的是 Nginx、PHP-FPM、MySQL 與 `deployer` 使用者。**不需要再跑 `server-init.sh`。**

1. **DNS**：新網域的 `@` 與 `www` A 記錄指到本機 IP。
2. **準備設定檔**（在本機）：
   ```bash
   cp deploy/project.conf.example deploy/新站.local.conf
   # 填入 DOMAIN、APP_DIR、GITHUB_REPO、DB_NAME、DB_USER、DB_PASS、GITHUB_ACTIONS_PUBKEY
   ```
   `*.local.conf` 已被 `.gitignore` 排除。資料庫名稱、帳號不可與其他站重複；密碼建議 32 字元英數字（`openssl rand -hex 16`）。
3. **上傳腳本與設定檔**：
   ```bash
   scp deploy/project-init.sh deploy/nginx.conf.template deploy/新站.local.conf root@伺服器IP:/tmp/
   ```
4. **在伺服器執行**：
   ```bash
   bash /tmp/project-init.sh /tmp/新站.local.conf
   ```
   腳本會建立資料夾與資料庫、clone、檢查 `.env`、migrate、seed、build、設定權限與 sudoers，並產生啟用 Nginx 站台。**會先檢查是否有其他已啟用的站台使用同一個網域**，有就中止；`nginx -t` 失敗會自動撤銷這個站台，不影響其他站。
   第一次會停下來讓你編輯 `.env`（`DB_CONNECTION=mysql`、`DB_*`、`APP_URL`），改完按 Enter。
5. **HTTPS**（DNS 生效後）：`certbot --nginx -d 新網域 -d www.新網域`
6. **新站的 GitHub repo**：建立 4 個 Secrets（見 Step 5）、把 `deploy.yml` 的 `APP_DIR` 改成與設定檔相同，再到 Actions 手動跑一次部署驗證。
7. **用完刪除** `/tmp` 內的設定檔（含密碼）：`rm /tmp/project-init.sh /tmp/新站.local.conf`

注意事項：

- 重複執行 `project-init.sh` 是安全的：已存在的資料庫、專案、`.env`、Nginx 設定、sudoers 行都會跳過；`APP_KEY` 已存在也不會重新產生（重新產生會讓已加密的資料無法解密）。
- Nginx 站台檔一旦存在就**不會被覆蓋**，因為 certbot 會把 HTTPS 設定寫進去。要重新產生請先自行備份並刪除該檔。
- 所有站共用同一個 PHP 版本與 `www-data`，站與站之間沒有系統層級隔離；若站台屬於互不信任的客戶，請改用獨立系統使用者與 PHP-FPM pool。
- 2GB 記憶體的主機站數多時要留意 PHP-FPM `pm.max_children` 與 MySQL 記憶體。

---

## 日常部署

到 GitHub repo → Actions → **Deploy to Linode** → **Run workflow** 手動觸發，執行內容：

1. **測試 job**：用 MySQL service 跑 `php artisan test`。失敗就不會部署。
2. **部署 job**（測試通過後）：
   1. 安裝 Composer 套件（無 dev）
   2. Build 前端（`npm run build`）
   3. 修正伺服器 storage 權限
   4. rsync 同步到伺服器
   5. 執行 `php artisan migrate --force`
   6. 重建 config / route / view / event cache
   7. reload PHP-FPM

> 想改成 push 到 `main` 自動部署，在 `deploy.yml` 的 `on:` 加上：
> ```yaml
> on:
>   workflow_dispatch:
>   push:
>     branches: [main]
> ```

---

## GitHub Actions Workflow 說明

Workflow 檔案位於 `.github/workflows/deploy.yml`，GitHub 會自動偵測此目錄下所有 `.yml` 檔案並在 Actions 頁面顯示，不需要額外設定。

Workflow 名稱由檔案內的 `name` 欄位決定，目前設定為 `Deploy to Linode`。

**觸發方式**：目前設定為手動觸發（`workflow_dispatch`），在 GitHub repo → Actions → Deploy to Linode → Run workflow 按鈕執行。

**Workflow 執行步驟說明**：

共兩個 job，`deploy` 設定 `needs: test`，測試通過才會執行；並以 `concurrency` 確保同一時間只有一個部署在跑。

`test` job：

| 步驟 | 說明 |
|---|---|
| MySQL service | 啟動 MySQL 8.4，建立 `testing` 資料庫 |
| Setup PHP / Install Composer dependencies | 安裝 PHP 8.4 與套件（含 dev） |
| Setup Node.js / Build frontend | Node.js 22，build 前端（前台頁面測試需要 Vite manifest） |
| Prepare environment | 複製 `.env.example` 並產生 APP_KEY |
| Run tests | `php artisan test` |

`deploy` job：

| 步驟 | 說明 |
|---|---|
| Checkout code | 取得最新程式碼 |
| Setup PHP | 在 Runner 安裝 PHP 8.4 |
| Install Composer dependencies | 安裝套件（排除 dev，正式環境用） |
| Setup Node.js | 在 Runner 安裝 Node.js 22 |
| Install npm dependencies & build | 安裝前端套件並 build |
| Setup SSH agent | 載入 GitHub Secret 的私鑰 |
| Add server to known_hosts | 將伺服器加入信任清單 |
| Fix storage permissions | 以 sudo 修正 `storage` 擁有者與權限，避免 rsync 失敗 |
| Sync files to server | rsync 同步檔案到伺服器（排除 .env、storage、快取等） |
| Run post-deploy commands | 執行 migrate、cache、reload PHP-FPM |

**rsync 排除清單說明**：

| 排除項目 | 原因 |
|---|---|
| `.git` / `.github` | 版控檔案不需上傳 |
| `node_modules` | 已在 Runner build，不需同步 |
| `tests` | 測試檔案不需上線 |
| `.env` | 正式環境 .env 放在伺服器，不能被覆蓋 |
| `public/storage` | `storage:link` 建立的連結，不在 git 裡；不排除的話 `--delete` 會刪掉它，造成圖片暫時 404 |
| `storage/app` | 使用者上傳的圖片、檔案 |
| `storage/logs` | 伺服器 log |
| `storage/framework/cache` | Laravel 快取，runtime 自動產生 |
| `storage/framework/sessions` | 使用者 session |
| `storage/framework/views` | Blade 編譯快取，runtime 自動產生 |
| `deploy` / `*.sh` | 部署說明文件，不需上傳 |

**套用到其他專案時需修改的地方**：

- 部署資料夾、網域、資料庫名稱：見本文件最上方「⚠️ 部署前必改」的完整清單
- GitHub Secrets 名稱：每個專案各自一個 repo 時，名稱維持 `LINODE_*` 即可；只有在多個專案共用同一個 repo 或使用組織層級 Secrets 時，才需要加上專案名稱區分（如 `LINODE_PROJECTNAME_SSH_KEY`），並同步修改 workflow
- PHP 版本視專案需求調整（`deploy.yml`、`server-init.sh`、`project.conf`、sudoers 要一致）

---

## 遇到的問題與解法

### 問題 1｜PHP 版本不符

**症狀**: `composer install` 失敗，提示套件需要 PHP >= 8.4

**原因**: 開發環境用 Laravel Sail（PHP 8.5），`composer.lock` 鎖定了需要 PHP 8.4+ 的套件版本，但伺服器安裝的是 PHP 8.2。

**解法**: 將伺服器 PHP 升級至 8.4，同時更新 Nginx 站台設定中的 socket 路徑（`nginx.conf.template` 的 `__PHP_VERSION__`）和 `deploy.yml` 中的 PHP 版本設定。

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
php artisan db:seed --class=ProductionSeeder --force

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
├── server-init.sh        # 伺服器初始化腳本（只需執行一次，重跑不會清掉其他站的 sudoers）
├── project-init.sh       # 專案初始化腳本（建 DB、clone、seed、權限、sudoers、Nginx 站台）；多站共用
├── project.conf.example  # 站台設定範本（複製為 project.conf 後填寫）
├── project.conf          # 此站台的實際設定（含密碼，已被 .gitignore 排除，不進 git）
├── nginx.conf.template   # Nginx 站台範本（__DOMAIN__ / __APP_DIR__ / __PHP_VERSION__）
└── README.md             # 本說明文件

.github/
└── workflows/
    └── deploy.yml   # GitHub Actions 自動部署設定
```
