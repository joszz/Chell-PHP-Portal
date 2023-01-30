#! /bin/bash

envsubst '${SERVERNAME} ${CHELLPORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

nginx
crond
php-fpm