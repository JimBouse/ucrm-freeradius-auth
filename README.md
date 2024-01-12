# ucrm-freeradius v1.5

This is designed and tested on Ubuntu 18.04.  It will create a DALORADIUS server that is setup to accept requests from mikrotik DHCP servers providing AUTH or denial based on MAC addresses being "active" in UCRM.

It looks for a custom attribute in UCRM called "devicemac" containing the MAC address of the client.  If you use a different custom attibute name, you can modify that in the service.edit.php file.

# Type command bellow in a fresh ubuntu 18.04 server to get started and follow the prompts as they pop up.
# sudo -i
# wget https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/setup.sh && chmod +x setup.sh && ./setup.sh

After setup, log into UCRM and add a webhook pointing at your server.  http://your.radius.server.address/webhook.php
Webhook event types: service.activate, service.add, service.archive, service.edit, service.end, service.postpone, service.suspend, service.suspend_cancel

Then run "php /var/www/html/full_update.php" to initiate the full sync.
After that, it should stay syncronized wtih any save of a service profile.

You may want to set a cronjob to run full_update.php nightly just to make sure.
