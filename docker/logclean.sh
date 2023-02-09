#! /bin/sh

find /var/www/portal/app/logs/ -name "*.log" -mtime +30 -exec rm -rf {} \;