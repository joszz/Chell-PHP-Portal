#!/bin/sh

envsubst '${SERVERNAME} ${PORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

nginx
crond
php-fpm

php /var/www/portal/app/StartContainer.php