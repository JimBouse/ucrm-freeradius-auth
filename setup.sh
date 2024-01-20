#!/bin/bash
clear;
printf '\n\nFreeRadius 3.2.3 + MariaDB (MySQL) Setup Script';
printf '\nJim Bouse - jim@brazoswifi.com - 2024-01-17\n';
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

 read -p "Install mariadb-server freeradius freeradius-mysql apache2 php libapache2-mod-php php-mysql php-curl? y/n " -n 1 -r
 echo    # (optional) move to a new line
 if [[ $REPLY =~ ^[Yy]$ ]]
 then
  apt install mariadb-server freeradius freeradius-mysql apache2 php libapache2-mod-php php-mysql php-curl -y
 fi

 printf "Create radius database\n"
 mysql -e "CREATE DATABASE radius;";

 printf "Importing Schema\n"
 mysql radius < /etc/freeradius/3.0/mods-config/sql/main/mysql/schema.sql

 printf "Creating user radius with password $sqlpass\n";
# Documentation says to import this file but we want more permissions than the .sql provides.  
# mysql radius < /etc/freeradius/3.0/mods-config/sql/main/mysql/setup.sql;
# Manually adding user in the following steps
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
 
 printf "Making a copy of /etc/freeradius/3.0/policy.d/filter to filter.bak\n";
 cp /etc/freeradius/3.0/policy.d/filter /etc/freeradius/3.0/policy.d/filter.bak
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

 printf "Configuring Freeradius to look for ADSL-Agent-Remote-Id (Option 82). For devices in bridge mode.)\n";
 echo "" >> /etc/freeradius/3.0/policy.d/filter
 echo "#" >> /etc/freeradius/3.0/policy.d/filter
 echo "# Adding logic to handle Option 82 bridge mode" >> /etc/freeradius/3.0/policy.d/filter
 echo "#" >> /etc/freeradius/3.0/policy.d/filter
 echo "" >> /etc/freeradius/3.0/policy.d/filter
 echo "check_option_82 {" >> /etc/freeradius/3.0/policy.d/filter
 echo " if (&ADSL-Agent-Remote-Id) {" >> /etc/freeradius/3.0/policy.d/filter
 echo "  if(string:ADSL-Agent-Remote-Id =~ /([a-f0-9]{2})[-:]?([a-f0-9]{2})[-:]?([a-f0-9]{2})[-:]?([a-f0-9]{2})[-:]?([a-f0-9]{2})[-:]?([a-f0-9]{2})/i) {" >> /etc/freeradius/3.0/policy.d/filter
 echo "   update request {" >> /etc/freeradius/3.0/policy.d/filter
 echo "    User-Name := "%{toupper:%{1}:%{2}:%{3}:%{4}:%{5}:%{6}}"" >> /etc/freeradius/3.0/policy.d/filter
 echo "   }" >> /etc/freeradius/3.0/policy.d/filter
 echo "  }" >> /etc/freeradius/3.0/policy.d/filter
 echo " }" >> /etc/freeradius/3.0/policy.d/filter
 echo "}" >> /etc/freeradius/3.0/policy.d/filter

 printf "Enabling filter 'check_option_82' in /etc/freeradius/3.0/sites-available/default\n";
 sed -i "s/^\s*filter_username/        filter_username\n        #\n        # Added by UCRM-Freeradius-Auth script\n        #\n        check_option_82/g" /etc/freeradius/3.0/sites-available/default;
 
 printf "Enabling COA for freeradius\n";
 ln -s /etc/freeradius/3.0/sites-available/coa /etc/freeradius/3.0/sites-enabled/

 service freeradius restart;

 printf "Downloading UCRM php files from github.\n";
 wget -O /var/www/html/webhook.php https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/php_files/webhook.php
 wget -O /var/www/html/functions.php https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/php_files/functions.php
 wget -O /var/www/html/service.edit.php https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/php_files/service.edit.php
 wget -O /var/www/html/full_update.php https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/php_files/full_update.php

 echo "<?php" > /var/www/html/service.add.php;
 echo "include('service.edit.php');" >> /var/www/html/service.add.php;
 echo "?>"  >> /var/www/html/service.add.php;
 cp /var/www/html/service.add.php /var/www/html/service.archive.php
 cp /var/www/html/service.add.php /var/www/html/service.delete.php
 cp /var/www/html/service.add.php /var/www/html/service.end.php
 cp /var/www/html/service.add.php /var/www/html/service.postpone.php
 cp /var/www/html/service.add.php /var/www/html/service.suspend.php
 cp /var/www/html/service.add.php /var/www/html/service.suspend_cancel.php

 printf "Creating config.php.\n";
 echo "<?php" > /var/www/html/config.php;
 echo "\$db_host = 'localhost';" >> /var/www/html/config.php;
 echo "\$db_user = 'radius';" >> /var/www/html/config.php;
 echo "\$db_pass = '$sqlpass';" >> /var/www/html/config.php;
 echo "\$db = 'radius';" >> /var/www/html/config.php;
 echo "\$uispUrl = '$ucrmhost';" >> /var/www/html/config.php;
 echo "\$uispKey = '$ucrmkey';" >> /var/www/html/config.php;
 echo "?>"  >> /var/www/html/config.php;

 printf "Setting file ownerships\n"
 chown -R www-data:www-data /var/www/html/
 chown -R freerad:freerad /etc/freeradius/
 
 printf "Creaing log file for UCRM scripts at /var/log/webhook_request.log\n"
 touch /var/log/webhook_request.log
 sudo chown www-data:www-data /var/log/webhook_request.log

 echo    # (optional) move to a new line
 echo    # (optional) move to a new line
 read -p "Schedule full sync (php /var/www/html/full_update.php) for 5 AM each day? y/n " -n 1 -r
 echo    # (optional) move to a new line
 if [[ $REPLY =~ ^[Yy]$ ]]
 then
  crontab -l > crontab_new
  echo "0 5 * * * php /var/www/html/full_update.php" >> crontab_new
  crontab crontab_new
  rm crontab_new
 fi
 
 echo    # (optional) move to a new line
 read -p "Initiate full sync (php /var/www/html/full_update.php)? y/n " -n 1 -r
 echo    # (optional) move to a new line
 if [[ $REPLY =~ ^[Yy]$ ]]
 then
  php /var/www/html/full_update.php
 fi

fi
