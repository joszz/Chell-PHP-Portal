#! /bin/bash

envsubst '${SERVERNAME} ${PORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

nginx
crond
php-fpm