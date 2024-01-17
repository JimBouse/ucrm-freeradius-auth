#!/bin/bash
clear;
printf '\n\nFreeRadius 3.2.3 + MariaDB (MySQL) Setup Script';
printf '\nJim Bouse - jim@brazoswifi.com - 2024-01-16\n';
printf '\nThis script is intended to be run on a brand new (unconfigured) Ubuntu 24.04 server.';
printf '\nIf this server has previous configuration, you may not want to use the script.';
printf '\n\n';

if [ "$(id -u)" != "0" ]; then
   printf "This script must be run as root, exiting.\n'sudo -i' should get you to root." 1>&2
   exit 1
fi

read -p "Do you want to proceed? y/n " -n 1 -r
echo;
if [[ $REPLY =~ ^[Yy]$ ]]
then
# Gathering information
read -p "What is the hostname or IP address of the UCRM Instance? " ucrmhost;
read -p "What is the API Key for UCRM? " ucrmkey;
read -p "Create a password for the 'radius' user for the mysql 'radius' database: " sqlpass;
# Installing packages 

 read -p "Install mariadb-server freeradius freeradius-mysql? y/n " -n 1 -r
 echo    # (optional) move to a new line
 if [[ $REPLY =~ ^[Yy]$ ]]
 then
  apt install mariadb-server freeradius freeradius-mysql -y
 fi

# Create database

 read -p "Create radius database? y/n " -n 1 -r
 echo    # (optional) move to a new line
 if [[ $REPLY =~ ^[Yy]$ ]]
 then
  mysql -e "CREATE DATABASE radius;";
 fi

# Importing Schema

 read -p "Import Schema.sql? y/n " -n 1 -r
 echo    # (optional) move to a new line
 if [[ $REPLY =~ ^[Yy]$ ]]
 then
  mysql radius < /etc/freeradius/3.0/mods-config/sql/main/mysql/schema.sql
 fi

# Creating radius user

 
# printf "\nMaking a copy of /etc/freeradius/3.0/mods-config/sql/main/mysql/setup.sql to setup.bak\n";
# cp /etc/freeradius/3.0/mods-config/sql/main/mysql/setup.sql /etc/freeradius/3.0/mods-config/sql/main/mysql/setup.bak
# printf "Copied...\n";
# sed -i "s/radpass/$sqlpass/g" /etc/freeradius/3.0/mods-config/sql/main/mysql/setup.sql;

 printf "Creating user radius with password $sqlpass\n";
# mysql radius < /etc/freeradius/3.0/mods-config/sql/main/mysql/setup.sql;
 mysql -e "CREATE USER 'radius'@'localhost' IDENTIFIED BY '$sqlpass';";
 mysql -e "grant all privileges on radius.* to 'radius'@'localhost';";
 mysql -e "SHOW GRANTS FOR 'radius'@'localhost';";

# Configuring Freeradius to use MySQL
 printf "Making a copy of /etc/freeradius/3.0/sites-available/default to default.bak\n";
 cp /etc/freeradius/3.0/sites-available/default /etc/freeradius/3.0/sites-available/default.bak
 printf "Copied...\n";

 printf "Making a copy of /etc/freeradius/3.0/sites-available/inner-tunnel to inner-tunnel.bak\n";
 cp /etc/freeradius/3.0/sites-available/inner-tunnel /etc/freeradius/3.0/sites-available/inner-tunnel.bak
 printf "Copied...\n";

 printf "Making a copy of /etc/freeradius/3.0/mods-available/sql to sql.bak\n";
 cp /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-available/sql.bak
 printf "Copied...\n";

 #sed -i "s/#[ \t]+read_clients.=.yes/read_clients = yes/g" /etc/freeradius/3.0/mods-available/sql
 printf "Downloading mods-available/sql from github.\n";
 wget https://raw.githubusercontent.com/JimBouse/ucrm-freeradius-auth/master/sql3.0 -O /etc/freeradius/3.0/mods-available/sql;
 sed -i "/password.=.*/c\password = $sqlpass" /etc/freeradius/3.0/mods-available/sql;

 printf "Configuring Freeradius to use MySQL\n";
 ln -s /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-enabled/sql

 sed -i "s/-sql/sql/g" /etc/freeradius/3.0/sites-available/default;
 sed -i "s/-sql/sql/g" /etc/freeradius/3.0/sites-available/inner-tunnel;
# sed -i "s/sql_user_name = \"%{User-Name}\"/sql_user_name = \"%{Stripped-User-Name}\"/g" /etc/freeradius/3.0/mods-config/sql/main/mysql/queries.conf

 service freeradius restart;

 printf "Downloading UCRM php files from github.\n";
 wget -O /var/www/html/webhook.php https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/php_files/webhook.php
 wget -O /var/www/html/functions.php https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/php_files/functions.php
 wget -O /var/www/html/service.edit.php https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/php_files/service.edit.php
 wget -O /var/www/html/full_update.php https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/php_files/full_update.php

 printf "Creating config.php.\n";

 sudo chown -R www-data:www-data /var/www/html/

 touch /var/log/webhook_request.log

 sudo chown www-data:www-data /var/log/webhook_request.log

fi
