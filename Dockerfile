# syntax=docker/dockerfile:1
FROM php:8.1-apache

ENV APACHE_DOCUMENT_ROOT /var/www/portal/public
ENV PATH="${PATH}:/usr/local/bin"
ENV TZ=Europe/Amsterdam

WORKDIR /var/www/portal
COPY . .

# Install prerequisites
RUN apt-get update && \
	apt-get install -y python3 python3-pip curl libz-dev libzip-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev snmpd snmp libsnmp-dev iputils-ping wget unzip zip cron anacron && \
	docker-php-ext-configure snmp && docker-php-ext-install -j$(nproc) snmp && \
	docker-php-ext-configure pdo_mysql && docker-php-ext-install -j$(nproc) pdo_mysql && \
	docker-php-ext-configure sockets && docker-php-ext-install -j$(nproc) sockets && \
	docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install -j$(nproc) gd && \
	pecl install redis phalcon-5.1.4 && docker-php-ext-enable redis opcache phalcon && \
	# Install ADB
	wget https://dl.google.com/android/repository/platform-tools-latest-linux.zip && \
	unzip -p platform-tools-latest-linux.zip platform-tools/adb > adb && \
	chmod +x adb && \
	rm platform-tools-latest-linux.zip && \
	# set permissions
	chown -R www-data:www-data ./../ && \
	chmod -R 0700 ./ && \
	# Use the default production configuration and change some settings
	mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
	sed -i 's#;session.cookie_secure =#session.cookie_secure = 1#' /usr/local/etc/php/php.ini && \
	sed -i 's#session.cookie_httponly =#session.cookie_httponly = 1#' /usr/local/etc/php/php.ini && \
	sed -i 's#session.cookie_samesite =#session.cookie_samesite = "Lax"#' /usr/local/etc/php/php.ini && \
	sed -i 's#session.name = PHPSESSID#session.name = __SECURE-PHPSESSID#' /usr/local/etc/php/php.ini && \
	# Allow htaccess
	sed -i 's#AllowOverride [Nn]one#AllowOverride All#' /etc/apache2/apache2.conf && \
	# Allow Apache status from 127.0.0.1
	sed -i 's#Require local#Require ip 127.0.0.1#' /etc/apache2/mods-enabled/status.conf && \
	a2enmod rewrite && \
	# Install python packages
	pip install python-miio vsure && \
	# Set timezone
	ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone && \
	# Cleanup 
	apt-get remove -y python3-pip wget unzip zip && apt-get autoclean && apt-get autoremove -y

# Copy Apache configuration
COPY ./docker/ports.conf /etc/apache2/ports.conf
COPY ./docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Setup cron
COPY ./docker/logclean /etc/cron.daily/logclean
RUN chmod 0644 /etc/cron.daily/logclean && \
	chmod +x /etc/cron.daily/logclean && \
	sed -i 's/^exec /service cron start\n\nexec /' /usr/local/bin/apache2-foreground

# Cleanup
RUN rm -rf /var/www/portal/docker

EXPOSE 8094