# ucrm-freeradius v1.5

# Type command bellow in a fresh ubuntu 18.04 server to get started and follow the prompts as they pop up.
# sudo -i
# wget https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/setup.sh && chmod +x setup.sh && ./setup.sh

After setup, log into UCRM and add a webhook pointing at your server.  http://your.radius.server.address/webhook.php

Then run "php /var/www/html/full_update.php" to initiate the full sync.
After that, it should stay syncronized wtih any save of a service profile.
