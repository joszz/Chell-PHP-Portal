#! /bin/bash

# Run as:
# docker run -it --user "www-data:www-data" <containername/containerid> /var/www/portal/vsure.sh

USERNAME=""
PASSWORD=""

read -p "Verisure username: " USERNAME
read -p "Verisure password: " -s PASSWORD

vsure $USERNAME $PASSWORD mfa
