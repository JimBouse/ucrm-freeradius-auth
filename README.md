# ucrm-freeradius

This is designed and tested on Ubuntu 24.04.  It will accept requests from mikrotik DHCP servers providing AUTH or denial based on MAC addresses being "active" in UCRM.

It looks for a custom attribute in UCRM called "devicemac" containing the MAC address of the client.  If you use a different custom attibute name, you can modify that in the service.edit.php file.
Make sure you have run sudo apt update && sudo apt upgrade;

# Type command bellow in a fresh ubuntu 24.04 server to get started and follow the prompts as they pop up.
# sudo -i
# wget https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/setup.sh -O setup.sh && chmod +x setup.sh && ./setup.sh

# After setup, log into UCRM and add a webhook pointing at your server.  http://your.radius.server.address/webhook.php
Webhook event types: service.activate, service.add, service.archive, service.edit, service.end, service.postpone, service.suspend, service.suspend_cancel

# You will need to add your mikrotik routers to the NAS table:
INSERT INTO nas (nasname, shortname, secret, description) VALUES ('1.2.3.4', 'Tower Router', 'somesecret', 'Tower router installed at 123 street');

# Run "php -f /var/www/html/full_update.php" to initiate the full sync.
After that, it should stay syncronized wtih any save of a service profile.

You may want to set a cronjob to run full_update.php nightly just to make sure.
