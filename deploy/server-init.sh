#!/bin/bash
# =============================================================================
# Linode 伺服器初始化腳本
# 適用: Ubuntu 22.04 / 24.04
# 用途: 安裝 Nginx + PHP 8.4-FPM + MySQL 8 + Node.js + Composer
# 執行方式: sudo bash server-init.sh
# 注意: 此腳本只需執行一次，不含任何專案相關設定
# =============================================================================

set -e

DEPLOY_USER="deployer"
PHP_VERSION="8.4"

echo "=============================="
echo " 開始初始化伺服器"
echo "=============================="

# ------------------------------------------------------------------------------
# 1. 系統更新
# ------------------------------------------------------------------------------
echo "[1/8] 更新系統套件..."
apt update && apt upgrade -y

# ------------------------------------------------------------------------------
# 2. 安裝基本工具
# ------------------------------------------------------------------------------
echo "[2/8] 安裝基本工具..."
apt install -y curl git unzip zip supervisor ufw

# ------------------------------------------------------------------------------
# 3. 安裝 Nginx
# ------------------------------------------------------------------------------
echo "[3/8] 安裝 Nginx..."
apt install -y nginx
systemctl enable nginx

# ------------------------------------------------------------------------------
# 4. 安裝 PHP 8.4 + 擴充套件
# ------------------------------------------------------------------------------
echo "[4/8] 安裝 PHP ${PHP_VERSION}..."
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y \
    php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-cli \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-zip \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-redis \
    php${PHP_VERSION}-opcache

systemctl enable php${PHP_VERSION}-fpm

# ------------------------------------------------------------------------------
# 5. 安裝 MySQL 8
# ------------------------------------------------------------------------------
echo "[5/8] 安裝 MySQL..."
apt install -y mysql-server
systemctl enable mysql

# 基本安全設定(移除 anonymous users / test db)
mysql -e "DELETE FROM mysql.user WHERE User='';"
mysql -e "DROP DATABASE IF EXISTS test;"
mysql -e "FLUSH PRIVILEGES;"
echo ">>> MySQL 安裝完成，資料庫與使用者將由 project-init.sh 建立"

# ------------------------------------------------------------------------------
# 6. 安裝 Node.js 22 (for npm run build)
# ------------------------------------------------------------------------------
echo "[6/8] 安裝 Node.js 22..."
curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
apt install -y nodejs

# ------------------------------------------------------------------------------
# 7. 安裝 Composer
# ------------------------------------------------------------------------------
echo "[7/8] 安裝 Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ------------------------------------------------------------------------------
# 8. 建立 deployer 使用者
# ------------------------------------------------------------------------------
echo "[8/8] 建立 deployer 使用者..."
if ! id "$DEPLOY_USER" &>/dev/null; then
    adduser --disabled-password --gecos "" $DEPLOY_USER
    echo ">>> 使用者 $DEPLOY_USER 已建立"
fi

# 建立 SSH 目錄供 GitHub Actions 使用
mkdir -p /home/$DEPLOY_USER/.ssh
chmod 700 /home/$DEPLOY_USER/.ssh
touch /home/$DEPLOY_USER/.ssh/authorized_keys
chmod 600 /home/$DEPLOY_USER/.ssh/authorized_keys
chown -R $DEPLOY_USER:$DEPLOY_USER /home/$DEPLOY_USER/.ssh

# deployer sudo 權限（不需密碼）
cat > /etc/sudoers.d/deployer << EOF
$DEPLOY_USER ALL=(ALL) NOPASSWD: /bin/systemctl reload php${PHP_VERSION}-fpm
EOF
chmod 440 /etc/sudoers.d/deployer

# ------------------------------------------------------------------------------
# 9. 設定防火牆
# ------------------------------------------------------------------------------
echo "[UFW] 設定防火牆..."
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

# ------------------------------------------------------------------------------
# 完成
# ------------------------------------------------------------------------------
echo ""
echo "=============================="
echo " 伺服器初始化完成！"
echo "=============================="
echo ""
echo "接下來請執行："
echo "  1. 將個人 SSH 公鑰加入: /home/$DEPLOY_USER/.ssh/authorized_keys"
echo "  2. 設定 Nginx (參考 deploy/nginx.conf)"
echo "  3. 執行 project-init.sh 初始化各專案"
echo ""
