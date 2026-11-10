#!/bin/bash
# =============================================================================
# 專案初始化腳本（支援同一台伺服器部署多個站）
# 用途: 建立資料夾與資料庫、Clone 專案、設定 .env 檢查、migration、seeder、
#       build 前端、權限、sudoers、Nginx 站台設定
# 執行方式（以 root）:
#   bash project-init.sh                    # 讀取與腳本同資料夾的 project.conf
#   bash project-init.sh /path/to/site.conf # 指定設定檔（多站時每個站一份）
# 前置: 已執行 server-init.sh；project.conf 由 project.conf.example 複製後填寫
#       nginx.conf.template 需與本腳本放在同一個資料夾
# 可重複執行: 已存在的資料庫、專案、.env、Nginx 設定都會跳過，不會覆蓋
# =============================================================================

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONF_FILE="${1:-$SCRIPT_DIR/project.conf}"

if [ ! -f "$CONF_FILE" ]; then
    echo "❌ 找不到設定檔: $CONF_FILE"
    echo "   請先複製範本並填寫: cp $SCRIPT_DIR/project.conf.example $SCRIPT_DIR/project.conf"
    exit 1
fi

# shellcheck disable=SC1090
source "$CONF_FILE"

PHP_VERSION="${PHP_VERSION:-8.4}"
APP_DIR="${APP_DIR:-/var/www/$DOMAIN}"
NGINX_TEMPLATE="$SCRIPT_DIR/nginx.conf.template"
NGINX_SITE="/etc/nginx/sites-available/$DOMAIN"
NGINX_LINK="/etc/nginx/sites-enabled/$DOMAIN"

# ------------------------------------------------------------------------------
# 設定檢查：必填且不可是範本佔位值
# ------------------------------------------------------------------------------
CONF_ERRORS=""
for var in DOMAIN APP_DIR GITHUB_REPO DB_NAME DB_USER DB_PASS GITHUB_ACTIONS_PUBKEY; do
    value="${!var}"
    if [ -z "$value" ]; then
        CONF_ERRORS="$CONF_ERRORS\n  - $var 未設定"
    fi
done
[ "$DOMAIN" = "example.com" ]        && CONF_ERRORS="$CONF_ERRORS\n  - DOMAIN 仍是範本值 example.com"
[ "$DB_PASS" = "請填隨機密碼" ]       && CONF_ERRORS="$CONF_ERRORS\n  - DB_PASS 仍是範本值"
case "$GITHUB_ACTIONS_PUBKEY" in *"AAAA..."*) CONF_ERRORS="$CONF_ERRORS\n  - GITHUB_ACTIONS_PUBKEY 仍是範本值" ;; esac
case "$GITHUB_REPO" in *"帳號"*) CONF_ERRORS="$CONF_ERRORS\n  - GITHUB_REPO 仍是範本值" ;; esac
if [ ! -f "$NGINX_TEMPLATE" ]; then
    CONF_ERRORS="$CONF_ERRORS\n  - 找不到 $NGINX_TEMPLATE（請與 nginx.conf.template 一併上傳到同一資料夾）"
fi
if [ -n "$CONF_ERRORS" ]; then
    echo "❌ 設定檔 $CONF_FILE 有問題："
    echo -e "$CONF_ERRORS"
    exit 1
fi

echo "=============================="
echo " 開始初始化專案: $DOMAIN"
echo "   資料夾: $APP_DIR"
echo "   資料庫: $DB_NAME / $DB_USER"
echo "=============================="

# ------------------------------------------------------------------------------
# 0. 建立應用目錄
# ------------------------------------------------------------------------------
mkdir -p "$APP_DIR"
chown -R deployer:deployer "$APP_DIR"
chmod -R 755 "$APP_DIR"

cd "$APP_DIR"

# ------------------------------------------------------------------------------
# 1. 加入 GitHub Actions SSH Key（已存在則略過）
# ------------------------------------------------------------------------------
echo "[1/9] 加入 GitHub Actions SSH Key..."
AUTH_KEYS="/home/deployer/.ssh/authorized_keys"
if grep -qF "$GITHUB_ACTIONS_PUBKEY" "$AUTH_KEYS" 2>/dev/null; then
    echo ">>> 公鑰已存在，略過"
else
    echo "$GITHUB_ACTIONS_PUBKEY" >> "$AUTH_KEYS"
    echo ">>> GitHub Actions 公鑰已加入"
fi

# ------------------------------------------------------------------------------
# 2. 建立 MySQL 資料庫與使用者
# ------------------------------------------------------------------------------
echo "[2/9] 建立 MySQL 資料庫..."
mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
echo ">>> 資料庫 ${DB_NAME} 與使用者 ${DB_USER} 建立完成"

# ------------------------------------------------------------------------------
# 3. Clone 專案
# ------------------------------------------------------------------------------
echo "[3/9] Clone 專案..."
if [ -f "$APP_DIR/artisan" ]; then
    echo ">>> 專案已存在，跳過 clone"
else
    git clone "$GITHUB_REPO" .
fi

# ------------------------------------------------------------------------------
# 4. 設定 .env
# ------------------------------------------------------------------------------
echo "[4/9] 設定 .env..."
if [ ! -f "$APP_DIR/.env" ]; then
    cp .env.example .env
    echo ">>> .env 已建立，請編輯填入正式環境設定："
    echo "    nano $APP_DIR/.env"
    echo ""
    echo "重要設定項目："
    echo "  APP_ENV=production"
    echo "  APP_DEBUG=false"
    echo "  APP_URL=https://$DOMAIN"
    echo "  DB_CONNECTION=mysql（.env.example 預設是 sqlite）"
    echo "  DB_DATABASE=$DB_NAME / DB_USERNAME=$DB_USER / DB_PASSWORD（行首不可有 #）"
    echo "  OPENAI_API_KEY / PHOTOROOM_API_KEY / CLIPDROP_API_KEY"
    echo ""
    read -p "設定完成後按 Enter 繼續..."
else
    echo ">>> .env 已存在，跳過"
fi

# 檢查 .env 的資料庫設定（沒改會在 migrate 時出現 could not find driver 或 Access denied）
env_value() { grep -E "^$1=" "$APP_DIR/.env" | tail -n 1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//'; }
ENV_ERRORS=""
[ "$(env_value DB_CONNECTION)" = "mysql" ]      || ENV_ERRORS="$ENV_ERRORS\n  - DB_CONNECTION 必須是 mysql（目前: $(env_value DB_CONNECTION)），且行首不能有 #"
[ "$(env_value DB_DATABASE)" = "$DB_NAME" ]     || ENV_ERRORS="$ENV_ERRORS\n  - DB_DATABASE 必須是 $DB_NAME（目前: $(env_value DB_DATABASE)）"
[ "$(env_value DB_USERNAME)" = "$DB_USER" ]     || ENV_ERRORS="$ENV_ERRORS\n  - DB_USERNAME 必須是 $DB_USER（目前: $(env_value DB_USERNAME)）"
[ "$(env_value DB_PASSWORD)" = "$DB_PASS" ]     || ENV_ERRORS="$ENV_ERRORS\n  - DB_PASSWORD 必須與設定檔的 DB_PASS 一致"
if [ -n "$ENV_ERRORS" ]; then
    echo ""
    echo "❌ $APP_DIR/.env 的資料庫設定不正確："
    echo -e "$ENV_ERRORS"
    echo ""
    echo "請編輯 .env 後重新執行本腳本： nano $APP_DIR/.env"
    exit 1
fi

# ------------------------------------------------------------------------------
# 5. 安裝套件並初始化
# ------------------------------------------------------------------------------
echo "[5/9] 安裝 Composer 套件..."
composer install --no-dev --optimize-autoloader

# 只在尚未產生 APP_KEY 時才產生。重複執行時重新產生會讓既有加密資料（如驗證碼金鑰）無法解密
if [ -z "$(env_value APP_KEY)" ]; then
    echo "產生 APP_KEY..."
    php artisan key:generate --force
else
    echo ">>> APP_KEY 已存在，略過（避免加密資料失效）"
fi

# ------------------------------------------------------------------------------
# 6. 執行 Migration 與必要 Seeder
# ------------------------------------------------------------------------------
echo "[6/9] 執行 Migration..."
php artisan migrate --force

if [ -n "$SEED_CLASS" ]; then
    echo "執行必要 Seeder（$SEED_CLASS，不含示範資料）..."
    php artisan db:seed --class="$SEED_CLASS" --force
else
    echo ">>> 未設定 SEED_CLASS，略過 Seeder"
fi

# ------------------------------------------------------------------------------
# 7. Build 前端
# ------------------------------------------------------------------------------
echo "[7/9] Build 前端..."
npm ci && npm run build

# ------------------------------------------------------------------------------
# 8. 設定目錄權限與 sudoers
# ------------------------------------------------------------------------------
echo "[8/9] 設定目錄權限..."
php artisan storage:link --force || true
chown -R deployer:www-data "$APP_DIR"
chmod -R 775 "$APP_DIR"
chmod -R 775 "$APP_DIR/storage"
chmod -R 775 "$APP_DIR/bootstrap/cache"
chown -R deployer:www-data "$APP_DIR/storage/framework/cache"
chmod -R 775 "$APP_DIR/storage/framework/cache"

# 讓 GitHub Actions 部署前可以修正 storage 權限（deploy.yml 的 Fix storage permissions 步驟）
# 多站共用同一個 sudoers 檔，各站只追加自己的兩行（以整行比對，已存在就不重複寫入）
SUDO_FILE="/etc/sudoers.d/deployer"
touch "$SUDO_FILE"
SUDO_CHOWN="deployer ALL=(ALL) NOPASSWD: /usr/bin/chown -R deployer\:www-data $APP_DIR/storage"
SUDO_CHMOD="deployer ALL=(ALL) NOPASSWD: /usr/bin/chmod -R 775 $APP_DIR/storage"
grep -qxF "$SUDO_CHOWN" "$SUDO_FILE" || echo "$SUDO_CHOWN" >> "$SUDO_FILE"
grep -qxF "$SUDO_CHMOD" "$SUDO_FILE" || echo "$SUDO_CHMOD" >> "$SUDO_FILE"
chmod 440 "$SUDO_FILE"
visudo -cf "$SUDO_FILE"

# ------------------------------------------------------------------------------
# 9. Nginx 站台設定（每個網域一個檔案，不動其他站）
# ------------------------------------------------------------------------------
echo "[9/9] 設定 Nginx..."
if [ -f "$NGINX_SITE" ]; then
    # 已存在就不覆蓋：certbot 申請 HTTPS 後會修改此檔，重新產生會讓憑證設定消失
    echo ">>> $NGINX_SITE 已存在，不覆蓋（保留 certbot 寫入的 HTTPS 設定）"
else
    # 檢查是否有其他已啟用的站台宣告了同一個網域（server_name 衝突會導致請求被錯誤的站接走）
    CONFLICTS=""
    for f in /etc/nginx/sites-enabled/*; do
        [ -e "$f" ] || continue
        [ "$(basename "$f")" = "$DOMAIN" ] && continue
        if grep -E "^\s*server_name\s" "$f" | grep -qE "(\s|^)(www\.)?${DOMAIN//./\\.}(\s|;)"; then
            CONFLICTS="$CONFLICTS $f"
        fi
    done
    if [ -n "$CONFLICTS" ]; then
        echo "❌ 下列已啟用的 Nginx 設定已經使用 $DOMAIN，請先處理後再執行："
        for f in $CONFLICTS; do echo "   $f"; done
        exit 1
    fi

    sed -e '/^#/d' \
        -e "s#__DOMAIN__#${DOMAIN}#g" \
        -e "s#__APP_DIR__#${APP_DIR}#g" \
        -e "s#__PHP_VERSION__#${PHP_VERSION}#g" \
        "$NGINX_TEMPLATE" > "$NGINX_SITE"
    ln -sfn "$NGINX_SITE" "$NGINX_LINK"

    if nginx -t; then
        systemctl reload nginx
        echo ">>> Nginx 站台 $DOMAIN 已啟用"
    else
        # 設定有誤時撤銷這個站台，不影響其他站
        rm -f "$NGINX_LINK"
        echo "❌ nginx -t 失敗，已撤銷 $DOMAIN 的站台連結。請檢查 $NGINX_SITE"
        exit 1
    fi
fi

# ------------------------------------------------------------------------------
# 完成
# ------------------------------------------------------------------------------
echo ""
echo "=============================="
echo " 專案初始化完成: $DOMAIN"
echo "=============================="
echo ""
echo "預設 admin 帳號: admin / admin123（以帳號登入，不是 email）"
echo "⚠️  請立即登入後台修改密碼！"
echo ""
echo "接下來請執行："
echo "  1. 確認網域 DNS（@ 與 www）已指到本機後，設定 HTTPS:"
echo "       certbot --nginx -d $DOMAIN -d www.$DOMAIN"
echo "  2. 該專案的 GitHub Secrets 與 deploy.yml 的 APP_DIR（$APP_DIR）"
echo "  3. 設定 Crontab（需要排程時）:"
echo "       crontab -u deployer -e"
echo "       * * * * * cd $APP_DIR && php artisan schedule:run >> /dev/null 2>&1"
echo ""
