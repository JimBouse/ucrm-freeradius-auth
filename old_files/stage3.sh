#!/bin/bash

sudo apt-get install freeradius freeradius-mysql freeradius-utils

mysql -u root radius < /etc/freeradius/3.0/mods-config/sql/main/mysql/schema.sql

sudo ln -s /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-enabled/

wget https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/sql

sudo rm /etc/freeradius/3.0/mods-enabled/sql

read -p "Enter your MySQL radius password: " sqlpass; 

sed -i "/password.=.*/c\password = $sqlpass" sql;

sudo mv sql /etc/freeradius/3.0/mods-enabled/sql

sudo chgrp -h freerad /etc/freeradius/3.0/mods-available/sql

sudo chown -R freerad:freerad /etc/freeradius/3.0/mods-enabled/sql

systemctl restart freeradius.service

wget https://github.com/JimBouse/ucrm-freeradius-auth/raw/master/daloradius-1.1-2.zip

unzip daloradius-1.1-2.zip

mv daloradius-1.1-2/ daloradius

cd daloradius

mysql -u root radius < contrib/db/fr2-mysql-daloradius-and-freeradius.sql 

mysql -u root radius < contrib/db/mysql-daloradius.sql

mysql -u root radius -e "ALTER TABLE radacct ADD acctupdatetime datetime NULL default NULL AFTER acctstarttime"
mysql -u root radius -e "ALTER TABLE radacct ADD acctinterval int(12) default NULL AFTER acctstoptime"
mysql -u root radius -e "ALTER TABLE radacct ADD framedipv6address varchar(32) default NULL AFTER framedipaddress"
mysql -u root radius -e "ALTER TABLE radacct ADD framedipv6prefix varchar(32) default NULL AFTER framedipaddress"
mysql -u root radius -e "ALTER TABLE radacct ADD framedinterfaceid varchar(32) default NULL AFTER framedipaddress"
mysql -u root radius -e "ALTER TABLE radacct ADD delegatedipv6prefix varchar(32) default NULL AFTER framedipaddress"


cd ..

mv daloradius /var/www/html/
sed -i '/CONFIG_DB_USER/d' /var/www/html/daloradius/library/daloradius.conf.php
sed -i '/CONFIG_DB_PASS/d' /var/www/html/daloradius/library/daloradius.conf.php


wget -O /var/www/html/webhook.php https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/php_files/webhook.php
wget -O /var/www/html/functions.php https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/php_files/functions.php
wget -O /var/www/html/service.edit.php https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/php_files/service.edit.php
wget -O /var/www/html/full_update.php https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/php_files/full_update.php

sudo chown -R www-data:www-data /var/www/html/

touch /var/log/webhook_request.log

sudo chown www-data:www-data /var/log/webhook_request.log

sudo chmod 664 /var/www/html/daloradius/library/daloradius.conf.php

wget https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/stage4.py

python stage4.py
