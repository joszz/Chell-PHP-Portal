# syntax=docker/dockerfile:1
FROM php:8.1-apache

ENV APACHE_DOCUMENT_ROOT /var/www/portal/public

# Install prerequisites
RUN apt-get update && apt-get install -y python3 python3-pip curl libz-dev libzip-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev snmpd snmp libsnmp-dev iputils-ping adb wget zip git

WORKDIR /usr/local/lib/php/extensions/no-debug-non-zts-20210902
RUN wget https://github.com/phalcon/cphalcon/releases/download/v5.1.2/phalcon-php8.1-nts-ubuntu-gcc-x64.zip 
RUN unzip phalcon-php8.1-nts-ubuntu-gcc-x64.zip
RUN docker-php-ext-enable phalcon

RUN docker-php-ext-configure snmp && docker-php-ext-install -j$(nproc) snmp
RUN docker-php-ext-configure pdo_mysql && docker-php-ext-install -j$(nproc) pdo_mysql
RUN docker-php-ext-configure sockets && docker-php-ext-install -j$(nproc) sockets
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install -j$(nproc) gd

RUN pecl install redis zip && docker-php-ext-enable redis zip opcache 

RUN mkdir /var/www/portal
WORKDIR /var/www/portal
RUN chown -R www-data:www-data ./../
COPY . .

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Allow htaccess
RUN sed -i 's#AllowOverride [Nn]one#AllowOverride All#' /etc/apache2/apache2.conf

# Enable mod rewrite
RUN a2enmod rewrite

# Copy Apache configuration
COPY ./apache_conf/ports.conf /etc/apache2/ports.conf
COPY ./apache_conf/000-default.conf /etc/apache2/sites-available/000-default.conf

# Cleanup
RUN rm -rf /var/www/portal/apache_conf
RUN apt-get remove -y wget git && apt-get autoclean && apt-get autoremove -y

USER www-data
RUN pip install python-miio vsure

EXPOSE 8094