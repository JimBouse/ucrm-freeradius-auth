# ucrm-freeradius

This is designed and tested on Ubuntu 24.04.  It will accept requests from mikrotik DHCP servers providing AUTH or denial based on MAC addresses being "active" in UCRM.

This process shoud take about 20 minutes on a clean VM.

It looks for a custom attribute in UCRM called "devicemac" containing the MAC address of the client.  If you use a different custom attibute name, you can modify that in the service.edit.php file.
Make sure you have run sudo apt update && sudo apt upgrade before proceeding.

# Type command bellow in a fresh ubuntu 24.04 server to get started and follow the prompts as they pop up.
# sudo -i
# wget https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/setup.sh -O setup.sh && chmod +x setup.sh && ./setup.sh

# After setup, log into UCRM and add a webhook pointing at your server.  http://your.radius.server.address/webhook_UCRM.php
Webhook event types: service.activate, service.add, service.archive, service.edit, service.end, service.postpone, service.suspend, service.suspend_cancel
Note: Other billing providers could be supported in the future by modifying the webhook_Provider.php and full_update_Provider.php files.

# Run "php -f /var/www/html/full_update_UISP.php" to initiate the full sync.
The script will prompt you to set a cronjob to run full_update_UISP.php nightly just to make sure.
After that, it should stay syncronized wtih any save of a service profile.

# You will need to add your mikrotik routers to the NAS table:
Run the following when still logged in as root: 

mysql radius
INSERT INTO nas (nasname, shortname, secret, description) VALUES ('YOUR.MIKROTIK.ROUTER.IP', 'Tower Router', 'YOUR_SECRET', 'Tower router installed at 123 street');

# You will need to reboot the server to get everything working.  The FreeRadius service does not dynamically reload the NAS table.

# You need to add the RADIUS server into the mikrotik.
/radius add address=YOUR.RADIUS.SERVER.IP secret=YOUR_SECRET service=dhcp

# You need to enable RADIUS for the DHCP server
Open your DHCP server settings and set the "Use RADIUS" to Yes from No.

# You need to apply the scripts listed in the "mikrotik" directory here in the github.
