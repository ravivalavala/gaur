#!/bin/bash
set -e

# Enable mod_rewrite
a2enmod rewrite

# Allow .htaccess overrides
sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Restart Apache
apache2ctl restart

# Start original WordPress entrypoint
docker-entrypoint.sh apache2-foreground
