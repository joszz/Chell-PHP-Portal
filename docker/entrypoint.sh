#! /bin/bash

envsubst '${SERVERNAME}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf
envsubst '${CHELLPORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

nginx
crond
php-fpm