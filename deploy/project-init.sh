#!/bin/bash
# =============================================================================
# 專案初始化腳本
# 用途: Clone 專案、設定權限、執行 migration 和 seeder
# 執行方式: bash project-init.sh
# 注意: 請先完成 server-init.sh 並設定好 .env 後再執行
# =============================================================================

set -e

APP_DIR="/var/www/minzheco"
GITHUB_REPO="https://你的TOKEN@github.com/waiting0514/prototype.git"
DB_NAME="minzheco"
DB_USER="minzheco_user"
DB_PASS="你的資料庫密碼"
GITHUB_ACTIONS_PUBKEY="ssh-ed25519 AAAA填入此專案的GitHub Actions公鑰 github-actions-deploy"

echo "=============================="
echo " 開始初始化專案"
echo "=============================="

# ------------------------------------------------------------------------------
# 0. 建立應用目錄
# ------------------------------------------------------------------------------
mkdir -p $APP_DIR
chown -R deployer:deployer $APP_DIR
chmod -R 755 $APP_DIR

cd $APP_DIR

# ------------------------------------------------------------------------------
# 1. 加入 GitHub Actions SSH Key
# ------------------------------------------------------------------------------
echo "[1/8] 加入 GitHub Actions SSH Key..."
echo "$GITHUB_ACTIONS_PUBKEY" >> /home/deployer/.ssh/authorized_keys
echo ">>> GitHub Actions 公鑰已加入"

# ------------------------------------------------------------------------------
# 2. 建立 MySQL 資料庫與使用者
# ------------------------------------------------------------------------------
echo "[2/8] 建立 MySQL 資料庫..."
mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
echo ">>> 資料庫 ${DB_NAME} 與使用者 ${DB_USER} 建立完成"

# ------------------------------------------------------------------------------
# 2. Clone 專案
# ------------------------------------------------------------------------------
echo "[3/8] Clone 專案..."
if [ -f "$APP_DIR/artisan" ]; then
    echo ">>> 專案已存在，跳過 clone"
else
    git clone $GITHUB_REPO .
fi

# ------------------------------------------------------------------------------
# 2. 設定 .env
# ------------------------------------------------------------------------------
echo "[4/8] 設定 .env..."
if [ ! -f "$APP_DIR/.env" ]; then
    cp .env.example .env
    echo ">>> .env 已建立，請編輯填入正式環境設定："
    echo "    nano $APP_DIR/.env"
    echo ""
    echo "重要設定項目："
    echo "  APP_ENV=production"
    echo "  APP_DEBUG=false"
    echo "  APP_URL=https://你的domain"
    echo "  DB_DATABASE / DB_USERNAME / DB_PASSWORD"
    echo "  OPENAI_API_KEY / PHOTOROOM_API_KEY / CLIPDROP_API_KEY"
    echo ""
    read -p "設定完成後按 Enter 繼續..."
else
    echo ">>> .env 已存在，跳過"
fi

# ------------------------------------------------------------------------------
# 3. 安裝套件並初始化
# ------------------------------------------------------------------------------
echo "[5/8] 安裝 Composer 套件..."
composer install --no-dev --optimize-autoloader

echo "產生 APP_KEY..."
php artisan key:generate

# ------------------------------------------------------------------------------
# 4. 執行 Migration 與必要 Seeder
# ------------------------------------------------------------------------------
echo "[6/8] 執行 Migration..."
php artisan migrate --force

echo "執行必要 Seeder..."
php artisan db:seed --class=LanguageSeeder
php artisan db:seed --class=ProjectSeeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan db:seed --class=DefaultCategorySeeder

# ------------------------------------------------------------------------------
# 5. Build 前端
# ------------------------------------------------------------------------------
echo "[7/8] Build 前端..."
npm install && npm run build

# ------------------------------------------------------------------------------
# 6. 設定目錄權限
# ------------------------------------------------------------------------------
echo "[8/8] 設定目錄權限..."
php artisan storage:link || true
chown -R deployer:www-data $APP_DIR
chmod -R 775 $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache
chown -R deployer:www-data $APP_DIR/storage/framework/cache
chmod -R 775 $APP_DIR/storage/framework/cache

# ------------------------------------------------------------------------------
# 完成
# ------------------------------------------------------------------------------
echo ""
echo "=============================="
echo " 專案初始化完成！"
echo "=============================="
echo ""
echo "預設 admin 帳號: admin@example.com / admin123"
echo "⚠️  請立即登入後台修改密碼！"
echo ""
echo "接下來請執行："
echo "  1. 設定 HTTPS: certbot --nginx -d 你的domain"
echo "  2. 設定 Crontab（移交客戶後）: crontab -u deployer -e"
echo ""
