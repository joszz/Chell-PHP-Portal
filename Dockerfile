# syntax=docker/dockerfile:1
FROM php:8.1-apache

ENV APACHE_DOCUMENT_ROOT /var/www/portal/public

RUN curl -fsSL https://deb.nodesource.com/setup_lts.x
RUN apt-get update && apt-get install -y curl libz-dev libzip-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev snmpd snmp libsnmp-dev nodejs npm
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j$(nproc) gd
RUN docker-php-ext-configure snmp \
	&& docker-php-ext-install -j$(nproc) snmp
RUN docker-php-ext-configure pdo_mysql \
	&& docker-php-ext-install -j$(nproc) pdo_mysql
RUN pecl install phalcon-5.0.0RC4 && docker-php-ext-enable phalcon
RUN pecl install zip && docker-php-ext-enable zip
RUN curl -sS https://getcomposer.org/installer | php \
	&& chmod +x composer.phar && mv composer.phar /usr/local/bin/composer

RUN mkdir /var/www/portal
WORKDIR /var/www/portal
COPY . .

RUN npm install -g gulp
RUN npm install gulp
RUN gulp

RUN composer install

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Allow htaccess
RUN sed -i 's#AllowOverride [Nn]one#AllowOverride All#' /etc/apache2/apache2.conf

# Enable mod rewrite
RUN a2enmod rewrite

# Change document root
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN chown -R www-data:www-data ./