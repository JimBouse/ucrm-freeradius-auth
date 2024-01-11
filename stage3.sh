#!/bin/bash

sudo apt-get install freeradius freeradius-mysql freeradius-utils

mysql -u root -p radius < /etc/freeradius/3.0/mods-config/sql/main/mysql/schema.sql

sudo ln -s /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-enabled/

wget https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/sql

sudo rm /etc/freeradius/3.0/mods-enabled/sql

sudo mv sql /etc/freeradius/3.0/mods-enabled/sql

sudo chgrp -h freerad /etc/freeradius/3.0/mods-available/sql

sudo chown -R freerad:freerad /etc/freeradius/3.0/mods-enabled/sql

systemctl restart freeradius.service

wget https://github.com/JimBouse/ucrm-freeradius-auth/raw/master/daloradius-1.1-2.zip

unzip daloradius-1.1-2.zip

mv daloradius-1.1-2/ daloradius

cd daloradius

read -p "Enter your MySQL password created earlier: " $sqlpasswd

mysql -u radius -p $sqlpasswd < contrib/db/fr2-mysql-daloradius-and-freeradius.sql 

mysql -u radius -p $sqlpasswd < contrib/db/mysql-daloradius.sql

cd ..

sudo mv daloradius /var/www/html/

wget -O /var/www/html/webhook.php https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/webhook.php

sudo chown -R www-data:www-data /var/www/html/

sudo chmod 664 /var/www/html/daloradius/library/daloradius.conf.php

wget https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/ucrmsetup.py

python ucrmsetup.py

