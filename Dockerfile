# Dipsons — PHP 8.3 + Apache image (used for Railway; shared hosting just uses the files)
FROM php:8.3-apache

RUN apt-get update \
 && apt-get install -y --no-install-recommends libzip-dev \
 && docker-php-ext-install pdo_mysql zip \
 && a2enmod rewrite headers \
 && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf \
 && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
 && rm -rf /var/lib/apt/lists/*

COPY docker/php.ini /usr/local/etc/php/conf.d/dipsons.ini
COPY . /var/www/html/
RUN rm -f /var/www/html/config.php && chown -R www-data:www-data /var/www/html

CMD ["/var/www/html/docker/start.sh"]
