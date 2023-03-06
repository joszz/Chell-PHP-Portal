#!/bin/sh

echo "Creating Nginx conf"
envsubst '${SERVERNAME} ${PORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

php -f /var/www/portal/app/StartContainer.php

echo "Starting Nginx"
nginx

echo "Starting crond"
crond

echo "Starting PHP FPM"
php-fpm