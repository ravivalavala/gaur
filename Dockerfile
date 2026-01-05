FROM wordpress:6.9-php8.2-apache

# Enable mod_rewrite and allow .htaccess
RUN a2enmod rewrite && \
    sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
