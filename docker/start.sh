#!/bin/sh
set -e
# Railway gives the port in $PORT
PORT="${PORT:-80}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
# storage may be a mounted volume — make sure folders exist and are writable
mkdir -p /var/www/html/storage/uploads /var/www/html/storage/sessions
printf 'Require all denied\n' > /var/www/html/storage/.htaccess
chown -R www-data:www-data /var/www/html/storage
exec apache2-foreground
