FROM wordpress:6.9-php8.2-apache

# Enable mod_rewrite and allow .htaccess
RUN a2enmod rewrite && \
    sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Allow plugin and theme ZIP uploads larger than PHP's default 2 MB limit.
RUN printf 'upload_max_filesize=64M\npost_max_size=64M\nmemory_limit=256M\nmax_execution_time=300\n' \
    > /usr/local/etc/php/conf.d/uploads.ini

# Route WordPress pretty URLs, including the WooCommerce shop archive, to index.php.
RUN printf '%s\n' \
    '# BEGIN WordPress' \
    '<IfModule mod_rewrite.c>' \
    'RewriteEngine On' \
    'RewriteBase /' \
    'RewriteRule ^index.php$ - [L]' \
    'RewriteCond %{REQUEST_FILENAME} !-f' \
    'RewriteCond %{REQUEST_FILENAME} !-d' \
    'RewriteRule . /index.php [L]' \
    '</IfModule>' \
    '# END WordPress' \
    > /var/www/html/.htaccess
