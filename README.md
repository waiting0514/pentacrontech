# Pentacron Tech

單一公司使用的 CMS，內建 AI 功能（文字生成、翻譯、圖片辨識、去背、情境圖）。

- **後端**：Laravel 12、PHP 8.2+（Sail 容器內為 PHP 8.5）、MySQL 8.4、Redis、Meilisearch
- **前端**：前台為 Blade；後台為 Vue 3 SPA（Composition API、Pinia、Element Plus），以 Vite 7 + Tailwind CSS 4 建置
- **認證**：Laravel Sanctum（Bearer token）；權限：Spatie Permission
- **開發環境**：Laravel Sail（Docker Compose）

> 系統為**單租戶**：全站設定（SEO、聯絡資訊、SMTP、驗證碼、維護模式、AI 年度預算）都存在 `site_settings` 資料表的唯一一筆資料，程式中以 `SiteSetting::current()` 取得。

---

## 目錄

1. [環境需求](#1-環境需求)
2. [從零開始建置（Windows）](#2-從零開始建置windows)
3. [日常開發](#3-日常開發)
4. [執行測試](#4-執行測試)
5. [設定說明（.env）](#5-設定說明env)
6. [常用指令速查](#6-常用指令速查)
7. [專案結構](#7-專案結構)
8. [常見問題排除](#8-常見問題排除)

---

## 1. 環境需求

以下以 **Windows 10/11** 為例。

| 軟體 | 用途 | 說明 |
|---|---|---|
| Docker Desktop | 執行 PHP、MySQL、Redis 等所有服務 | 必須安裝，且要先啟動 |
| WSL 2 | Docker Desktop 的執行後端 | 安裝 Docker Desktop 時一併啟用 |
| Git | 取得原始碼 | 建議使用 Git for Windows（內附 Git Bash） |
| 終端機 | 下指令 | PowerShell 或 Git Bash 皆可 |

**不需要**在 Windows 上安裝 PHP、Composer、Node.js，這些都在容器裡執行。

> 如果你的電腦已有 PHP，版本也不用符合專案需求；`composer.lock` 內有套件需要 PHP 8.3、8.4，請一律使用下面的 Docker 方式安裝依賴，不要在本機執行 `composer install`。

### 1.1 安裝 Docker Desktop

1. 到 <https://www.docker.com/products/docker-desktop/> 下載並安裝 Docker Desktop for Windows。
2. 安裝時勾選 **Use WSL 2 instead of Hyper-V**。
3. 安裝完成後重新開機，啟動 Docker Desktop，等左下角狀態顯示 **Engine running**（綠色）。
4. 在終端機確認：

   ```powershell
   docker --version
   docker compose version
   ```

### 1.2 確認連接埠沒有被佔用

專案會使用下列連接埠，若已被其他程式佔用，請先關閉該程式，或在 `.env` 改成其他埠（見[常見問題](#8-常見問題排除)）。

| 連接埠 | 服務 |
|---|---|
| 80 | 網站（`APP_PORT`） |
| 5173 | Vite 開發伺服器（`VITE_PORT`） |
| 3306 | MySQL（`FORWARD_DB_PORT`） |
| 6379 | Redis（`FORWARD_REDIS_PORT`） |
| 7700 | Meilisearch（`FORWARD_MEILISEARCH_PORT`） |
| 1025 / 8025 | Mailpit SMTP / 網頁介面 |

---

## 2. 從零開始建置（Windows）

下列指令都在**專案根目錄**執行。PowerShell 與 Git Bash 的差異會另外標示。

### 步驟 1：取得原始碼

```powershell
git clone <專案網址> pentacrontech
cd pentacrontech
```

### 步驟 2：建立 `.env`

```powershell
copy .env.example .env
```

（Git Bash：`cp .env.example .env`）

### 步驟 3：用 Docker 安裝 PHP 依賴（建立 `vendor/`）

此時還沒有 `vendor/`，所以 Sail 本身還不能用，先借用一個臨時的 Composer 容器來安裝：

PowerShell：

```powershell
docker run --rm -v "${PWD}:/app" -w /app laravelsail/php84-composer:latest composer install --ignore-platform-reqs --no-interaction
```

Git Bash：

```bash
MSYS_NO_PATHCONV=1 docker run --rm -v "$(pwd -W):/app" -w /app laravelsail/php84-composer:latest composer install --ignore-platform-reqs --no-interaction
```

第一次執行需要下載映像檔，會花幾分鐘。完成後專案目錄會出現 `vendor/`。

### 步驟 4：修改 `.env`

用編輯器開啟 `.env`，把預設的 sqlite 設定改成 Sail 的 MySQL。下表是**必改項目**：

```ini
APP_LOCALE=zh-TW

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

REDIS_HOST=redis

MAIL_HOST=mailpit
MAIL_PORT=1025

# Docker 容器內使用者對應（Windows 固定寫 1000）
WWWUSER=1000
WWWGROUP=1000
```

注意：

- `.env.example` 內 `SCOUT_DRIVER` 出現兩次（`database` 與 `meilisearch`），後面的會生效，也就是 `meilisearch`。
- `MEILISEARCH_HOST` 預設已是 `http://meilisearch:7700`，不用改。
- `APP_LOCALE=zh-TW`：預設語系為繁體中文；部分頁面與測試依賴這個值。
- `DB_DATABASE` 等值只在**第一次建立 MySQL 容器**時生效。如果之後才改，必須刪除 MySQL volume 重建（見[常見問題](#8-常見問題排除)）。

### 步驟 5：啟動所有容器

```powershell
docker compose up -d
```

第一次會建置 `sail-8.5/app` 映像檔並下載 MySQL、Redis、Meilisearch、Mailpit、Selenium，需要幾分鐘。完成後確認：

```powershell
docker compose ps
```

應該看到 `laravel.test`、`mysql`、`redis`、`meilisearch`、`mailpit`、`selenium` 都是 `Up`（mysql 等會顯示 `healthy`）。

> 看到 `WWWUSER` / `DB_PASSWORD` variable is not set 的警告，代表步驟 4 的 `.env` 沒設好。

**之後的指令說明**：本文件統一用 `docker compose exec laravel.test <指令>` 在容器內執行指令，這在 PowerShell、CMD、Git Bash 都能用。為了少打字，PowerShell 可以先定義別名（只對目前這個視窗有效）：

```powershell
function sail { docker compose exec laravel.test @args }
# 之後可以寫：sail php artisan about
```

如果你用 **Git Bash**，也可以直接使用 `./vendor/bin/sail <指令>`（它是 bash 腳本，PowerShell 不能直接執行）。

### 步驟 6：產生應用程式金鑰

```powershell
docker compose exec laravel.test php artisan key:generate
```

### 步驟 7：建立資料表並匯入初始資料

```powershell
docker compose exec laravel.test php artisan migrate:fresh --seed
```

這個指令會**清空**資料庫後重建。Seeder 會建立：語言、全站設定（`site_settings` 唯一一筆）、使用者與角色權限、預設分類（未分類）、示範產品分類與產品、新聞、輪播圖。

### 步驟 8：建立圖片儲存連結

上傳的圖片存在 `storage/app/public`，需要連結到 `public/storage`：

```powershell
docker compose exec laravel.test php artisan storage:link
```

### 步驟 9：安裝前端依賴並建置

```powershell
docker compose exec laravel.test npm install
docker compose exec laravel.test npm run build
```

`npm run build` 會產生 `public/build/manifest.json`。**沒有這個檔案，網站與部分測試會出現 `Vite manifest not found` 錯誤。**

### 步驟 10：開啟網站

| 位置 | 網址 |
|---|---|
| 前台 | <http://localhost> |
| 後台（Vue SPA） | <http://localhost/admin> |
| Mailpit（收測試信） | <http://localhost:8025> |
| Meilisearch | <http://localhost:7700> |

測試帳號（`db:seed` 之後）：

| 角色 | 帳號 | 密碼 |
|---|---|---|
| 管理員 | `admin` | `admin123` |
| 一般使用者 | `test` | `password` |

> 登入使用的是**帳號（username）**，不是 email。上線前務必更換預設密碼。

### 步驟 11（選用）：設定 AI 金鑰

AI 功能需要在 `.env` 填入第三方服務金鑰，沒填也能使用其他功能，只是 AI 功能會失敗：

```ini
OPENAI_API_KEY=sk-...
PHOTOROOM_API_KEY=...
CLIPDROP_API_KEY=...
```

改完 `.env` 後執行 `docker compose exec laravel.test php artisan config:clear`。AI 年度預算（TWD）與開關在後台「設定」頁調整；各功能的固定單價在 `config/ai_pricing.php`。

---

## 3. 日常開發

### 3.1 每次開機後

1. 啟動 Docker Desktop，等待 Engine running。
2. 啟動容器：

   ```powershell
   docker compose up -d
   ```

### 3.2 前端熱更新

另開一個終端機視窗，持續執行：

```powershell
docker compose exec laravel.test npm run dev
```

Vite 會在 `http://localhost:5173` 提供熱更新。修改 `resources/js` 或 `resources/css` 後瀏覽器會自動更新。要部署或跑測試前，記得再執行一次 `npm run build`。

### 3.3 後端

修改 PHP 檔案會即時生效（程式碼掛載在容器內）。如果修改了路由、設定或快取相關檔案卻沒反應：

```powershell
docker compose exec laravel.test php artisan optimize:clear
```

### 3.4 查看日誌

```powershell
docker compose exec laravel.test php artisan pail
# 或直接看檔案：storage/logs/laravel.log
```

### 3.5 關閉

```powershell
docker compose stop      # 暫停容器，資料保留
docker compose down      # 移除容器，資料（volume）仍保留
docker compose down -v   # 連同資料庫等 volume 全部刪除（資料會消失）
```

### 3.6 排程

AI 年度用量每年 1 月 1 日歸零，由排程 `usage:reset-yearly` 執行（定義在 `routes/console.php`）。本機開發不需要處理；正式環境需要讓 Laravel 排程每分鐘執行一次：

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

也可以手動歸零：

```powershell
docker compose exec laravel.test php artisan usage:reset-yearly
```

---

## 4. 執行測試

測試使用名為 `testing` 的 MySQL 資料庫（`phpunit.xml` 設定，容器初始化時自動建立），不會影響開發資料。

```powershell
# 全部測試
docker compose exec laravel.test php artisan test

# 指定測試
docker compose exec laravel.test php artisan test --filter=SiteSettingSmtpTest
```

執行前請先完成 `npm run build`（前台頁面測試需要 Vite manifest）。目前預期結果：**37 個測試全數通過**。

程式碼格式化：

```powershell
docker compose exec laravel.test ./vendor/bin/pint
```

---

## 5. 設定說明（.env）

| 變數 | 說明 |
|---|---|
| `APP_URL` | 網站網址，本機為 `http://localhost` |
| `APP_LOCALE` | 預設語系，建議 `zh-TW` |
| `APP_TIMEZONE` | 時區，預設 `Asia/Taipei` |
| `DB_*` | 資料庫連線，Sail 內為 `mysql` 主機 |
| `REDIS_HOST` | Redis 主機，Sail 內為 `redis` |
| `QUEUE_CONNECTION` / `CACHE_STORE` / `SESSION_DRIVER` | 預設都使用 `database` |
| `SCOUT_DRIVER` / `MEILISEARCH_HOST` | 全文搜尋，使用 Meilisearch |
| `MAIL_*` | 本機使用 Mailpit；正式環境的寄信設定可在後台「設定」頁填寫 SMTP，會優先於 `.env` |
| `OPENAI_API_KEY` 等 | AI 服務金鑰 |
| `WWWUSER` / `WWWGROUP` | 容器內使用者 ID，Windows 填 `1000` |
| `APP_PORT` / `VITE_PORT` / `FORWARD_*_PORT` | 對外連接埠，可用來避開衝突 |

後台「設定」頁（寫入 `site_settings`）可調整：網站名稱與 Logo、SEO 資訊、聯絡資訊、維護模式、SMTP、驗證碼（reCAPTCHA / Cloudflare Turnstile）、AI 開關與年度預算。SMTP 密碼與驗證碼密鑰以加密方式儲存，API 不會回傳明文。

---

## 6. 常用指令速查

以下省略前綴 `docker compose exec laravel.test`，實際使用時請加上。

```powershell
# Composer
composer install
composer require 套件名稱

# NPM
npm install
npm run build
npm run dev

# 資料庫
php artisan migrate
php artisan migrate:fresh --seed     # 重建並匯入初始資料（會清空資料）
php artisan db:seed

# 其他
php artisan tinker
php artisan optimize:clear
php artisan route:list
php artisan test
./vendor/bin/pint
```

進入容器的互動式 shell：

```powershell
docker compose exec laravel.test bash
```

---

## 7. 專案結構

```
app/
├── Http/Controllers/Api/        # 後台 REST API
├── Http/Controllers/Frontend/   # 前台頁面（Blade）
├── Models/                      # Eloquent 模型（SiteSetting、Product、News…）
├── Observers/                   # 模型事件（自動產生 slug）
├── Services/                    # 商業邏輯：AI、圖片、RAG、SMTP、驗證碼、Slug
└── Traits/ApiResponse.php       # 統一的 API 回應格式

config/ai_pricing.php            # AI 功能固定單價（TWD）
database/migrations/             # 資料表（新專案，已合併為每表一個 create）
database/seeders/                # 初始資料

resources/
├── js/admin/                    # 後台 SPA（views、Pinia store、router）
├── js/services/                 # axios API 服務層
├── js/components/               # 共用元件
└── views/                       # 前台 Blade 範本

routes/api.php                   # API 路由（Sanctum）
routes/web.php                   # 前台路由；/admin/* 交給後台 SPA
tests/Feature/                   # 功能測試
compose.yaml                     # Sail 的 Docker 服務定義
```

### 重要約定

- **API 回應格式**：`{"success": true, "code": 200, "message": "...", "data": {...}}`
- **多語系欄位**：產品與新聞的 `name`、`description` 等以 JSON 儲存，如 `{"zh-TW": "...", "en": "..."}`（使用 `spatie/laravel-translatable`）。
- **圖片路徑**：資料庫存相對路徑（`uploads/products/thumb/xxx.jpg`），輸出時用 `asset('storage/' . $path)` 轉成完整網址。
- **全站設定**：`SiteSetting::current()`；API 為 `GET/PUT /api/settings`、`POST /api/settings/test-smtp`。
- **AI 預算**：`AiCostControlService` 在每次呼叫 AI 前檢查年度預算，並寫入 `ai_usage_logs`。

---

## 8. 常見問題排除

### `./vendor/bin/sail : 無法辨識…`（PowerShell）
還沒有 `vendor/`（請先做步驟 3），或是 PowerShell 不能直接執行 bash 腳本。請改用 `docker compose exec laravel.test <指令>`，或使用 Git Bash。

### `composer install` 出現 `ext-fileinfo` 缺少、`requires php >=8.4`
你在 Windows 本機直接執行了 Composer，而本機 PHP 版本或擴充不符。請改用步驟 3 的 Docker 指令。

### `Cannot connect to the Docker daemon` / `error during connect`
Docker Desktop 沒有啟動。啟動後等到 Engine running 再執行指令。

### `The "WWWUSER" / "DB_PASSWORD" variable is not set`
`.env` 缺少步驟 4 的設定。補上後重新執行 `docker compose up -d`。

### `SQLSTATE[HY000] [1045] Access denied`、`Unknown database`
MySQL volume 是用舊的（空白）帳密建立的，之後才改 `.env` 不會生效。重建 MySQL（**會刪除資料庫內所有資料**）：

```powershell
docker compose down
docker volume rm pentacrontech_sail-mysql
docker compose up -d
docker compose exec laravel.test php artisan migrate:fresh --seed
```

volume 名稱是「專案資料夾名稱_sail-mysql」，用 `docker volume ls` 確認。

### `Vite manifest not found at: public/build/manifest.json`
還沒建置前端。執行 `docker compose exec laravel.test npm run build`。

### 連接埠被佔用（`port is already allocated`）
在 `.env` 加上其他埠，例如：

```ini
APP_PORT=8080
FORWARD_DB_PORT=3307
```

然後 `docker compose up -d`。網站網址就變成 `http://localhost:8080`，記得同步修改 `APP_URL`。

### 圖片上傳後看不到
確認有執行 `php artisan storage:link`，並且 `public/storage` 存在。

### 修改 `.env` 後沒有生效
```powershell
docker compose exec laravel.test php artisan config:clear
```

### 容器內 `npm install` 很慢或失敗
Windows 的專案資料夾掛載進容器本來就較慢。可以把專案放在 WSL 2 的檔案系統內（例如 `\\wsl$\Ubuntu\home\<user>\pentacrontech`）開發，速度會明顯提升。

### Windows 換行字元造成 shell script 錯誤
若在容器內看到 `/bin/sh^M: bad interpreter` 之類的錯誤，是 Git 把 `.sh` 檔轉成 CRLF 了。執行：

```powershell
git config core.autocrlf input
```

然後重新 clone，或對該檔案轉回 LF。
