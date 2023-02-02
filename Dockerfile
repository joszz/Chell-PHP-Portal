# syntax=docker/dockerfile:1
FROM php:8.1-fpm-alpine

ENV PATH="${PATH}:/usr/bin"
ENV TZ=Europe/Amsterdam
ENV SERVERNAME=
ENV PORT=8094

WORKDIR /var/www/portal

RUN apk update && apk upgrade && \
	apk add --no-cache gettext nginx python3 python3-dev py3-pip linux-headers tzdata curl zlib-dev libzip-dev freetype-dev libjpeg-turbo-dev libpng-dev net-snmp-tools net-snmp-dev iputils wget unzip zip gcc pcre-dev ${PHPIZE_DEPS} && \
	docker-php-ext-configure snmp && docker-php-ext-install -j$(nproc) snmp && \
	docker-php-ext-configure pdo_mysql && docker-php-ext-install -j$(nproc) pdo_mysql && \
	docker-php-ext-configure sockets && docker-php-ext-install -j$(nproc) sockets && \
	docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install -j$(nproc) gd && \
	pecl install redis phalcon-5.1.4 && docker-php-ext-enable redis opcache phalcon && \
	# Install ADB
	wget https://dl.google.com/android/repository/platform-tools-latest-linux.zip && \
	unzip -p platform-tools-latest-linux.zip platform-tools/adb > adb && \
	rm platform-tools-latest-linux.zip && \
	# Use the default production configuration and change some settings
	mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
	sed -i 's#;session.cookie_secure =#session.cookie_secure = 1#' /usr/local/etc/php/php.ini && \
	sed -i 's#session.cookie_httponly =#session.cookie_httponly = 1#' /usr/local/etc/php/php.ini && \
	sed -i 's#session.cookie_samesite =#session.cookie_samesite = "Lax"#' /usr/local/etc/php/php.ini && \
	# Install python packages
	pip install python-miio vsure && \
	# Set timezone
	ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone && \
	# Cleanup 
	apk del py3-pip python3-dev linux-headers wget unzip zip gcc pcre-dev ${PHPIZE_DEPS}

COPY --chmod=0750 --chown=www-data:www-data . .
COPY ./docker/nginx-site.conf /etc/nginx/http.d/default.conf.template
COPY ./docker/entrypoint.sh /etc/entrypoint.sh
COPY --chmod=0744 ./docker/logclean.sh /etc/periodic/daily/logclean.sh

EXPOSE ${PORT}

ENTRYPOINT ["sh", "/etc/entrypoint.sh"]

HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:${PORT}/