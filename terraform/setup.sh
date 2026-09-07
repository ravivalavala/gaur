#!/bin/bash
exec > >(tee /var/log/user-data.log|logger -t user-data -s 2>/dev/console) 2>&1

apt-get update -y
apt-get install -y software-properties-common
add-apt-repository -y ppa:ondrej/php
apt-get update -y

apt-get install -y nginx php8.2-fpm php8.2-mysql php8.2-xml php8.2-gd php8.2-mbstring php8.2-curl php8.2-zip unzip

cat > /etc/nginx/sites-available/wordpress <<EOF
server {
    listen 80 default_server;
    root /var/www/html;
    index index.php index.html;
    location / { try_files \$uri \$uri/ /index.php?\$args; }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }
}
EOF
ln -sf /etc/nginx/sites-available/wordpress /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

cd /tmp && curl -O https://wordpress.org/latest.tar.gz
tar -xzf latest.tar.gz && rm -rf /var/www/html/* && cp -r wordpress/* /var/www/html/

cat > /var/www/html/wp-config.php <<WP_CONFIG
<?php
define('DB_NAME', 'gaur_wp_prd');
define('DB_USER', 'wpadmin');
define('DB_PASSWORD', '${db_password}');
define('DB_HOST', '${db_host}');
\$table_prefix = 'wp_';
if ( ! defined( 'ABSPATH' ) ) define( 'ABSPATH', __DIR__ . '/' );
require_once ABSPATH . 'wp-settings.php';
WP_CONFIG

chown -R www-data:www-data /var/www/html
systemctl restart nginx php8.2-fpm