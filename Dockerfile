# syntax=docker/dockerfile:1
FROM php:8.1-apache

ENV APACHE_DOCUMENT_ROOT /var/www/portal/public

# Install prerequisites
RUN curl -fsSL https://deb.nodesource.com/setup_lts.x
RUN apt-get update && apt-get install -y curl libz-dev libzip-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev snmpd snmp libsnmp-dev nodejs npm python3-pip iputils-ping adb hdparm
RUN docker-php-ext-configure snmp && docker-php-ext-install -j$(nproc) snmp
RUN docker-php-ext-configure pdo_mysql && docker-php-ext-install -j$(nproc) pdo_mysql
RUN docker-php-ext-configure opcache && docker-php-ext-install -j$(nproc) opcache
RUN docker-php-ext-configure sockets && docker-php-ext-install -j$(nproc) sockets

RUN pecl install redis && docker-php-ext-enable redis
RUN pecl install phalcon-5.0.3 && docker-php-ext-enable phalcon
RUN pecl install zip && docker-php-ext-enable zip
RUN curl -sS https://getcomposer.org/installer | php && chmod +x composer.phar && mv composer.phar /usr/local/bin/composer

RUN mkdir /var/www/portal
WORKDIR /var/www/portal
RUN chown -R www-data:www-data ./../
COPY . .

RUN npm install -g gulp
RUN npm install gulp
RUN gulp

RUN pip install python-miio
RUN pip install vsure
RUN composer install --no-dev

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Allow htaccess
RUN sed -i 's#AllowOverride [Nn]one#AllowOverride All#' /etc/apache2/apache2.conf

# Enable mod rewrite
RUN a2enmod rewrite

# Change Apache listening port
COPY ./my-ports.conf /etc/apache2/ports.conf
COPY ./my-000-default.conf /etc/apache2/sites-available/000-default.conf

# Change document root
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Cleanup
RUN rm -rf /var/www/portal/node_modules