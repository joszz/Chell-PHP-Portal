#! /bin/bash

# Run as:
# docker exec -it <containername/containerid> /var/www/portal/vsure.sh

USERNAME=""
PASSWORD=""

read -p "Verisure username: " USERNAME
read -p "Verisure password: " -s PASSWORD

vsure $USERNAME $PASSWORD mfa
