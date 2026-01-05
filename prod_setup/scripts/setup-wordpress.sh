#!/bin/bash
# Shared WordPress installation script for EC2

# Update system
apt update -y && apt upgrade -y

# Install Nginx, PHP, and extensions
apt install -y nginx php-fpm php-mysql php-curl php-xml php-mbstring unzip curl git

# Configure PHP-FPM for low memory
sed -i 's/pm = .*/pm = ondemand/' /etc/php/8.2/fpm/pool.d/www.conf
sed -i 's/pm.max_children = .*/pm.max_children = 3/' /etc/php/8.2/fpm/pool.d/www.conf
sed -i 's/pm.max_requests = .*/pm.max_requests = 300/' /etc/php/8.2/fpm/pool.d/www.conf
systemctl restart php8.2-fpm

# Create web root
mkdir -p /var/www/html
cd /var/www/

# Download and install WordPress
curl -O https://wordpress.org/latest.zip
unzip latest.zip
mv wordpress/* html/
rm -rf wordpress latest.zip
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

# Configure Nginx
cat > /etc/nginx/sites-available/wordpress <<EOF
server {
    listen 80;
    server_name _;
    root /var/www/html;

    index index.php index.html index.htm;

    location / {
        try_files \$uri \$uri/ /index.php?\$args;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

ln -s /etc/nginx/sites-available/wordpress /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default
systemctl restart nginx

# Configure WordPress wp-config.php using environment variables
cat > /var/www/html/wp-config.php <<EOF
<?php
define('DB_NAME', '$DB_NAME');
define('DB_USER', '$DB_USER');
define('DB_PASSWORD', '$DB_PASSWORD');
define('DB_HOST', '$DB_HOST');
define('DB_CHARSET', '$DB_CHARSET');
define('DB_COLLATE', '$DB_COLLATE');
define('WP_MEMORY_LIMIT', '$WP_MEMORY_LIMIT');
define('WP_DEBUG', $WP_DEBUG);
define('WP_HOME', '$WP_HOME');
define('WP_SITEURL', '$WP_SITEURL');

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}
require_once ABSPATH . 'wp-settings.php';
EOF

chown www-data:www-data /var/www/html/wp-config.php

# Firewall
ufw allow 'Nginx Full'
ufw --force enable

# Install WP-CLI
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
mv wp-cli.phar /usr/local/bin/wp

# Optional: Install caching plugin via WP-CLI
sudo -u www-data wp plugin install litepeed-cache --activate --allow-root
