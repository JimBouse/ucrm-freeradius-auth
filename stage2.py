import MySQLdb, urllib, subprocess

#User inputs sql loggin details
#username = raw_input("Put in the username you set for the sql database: ")
password = raw_input("Type the password you will use to login to radius@localhost user in the sql database: ")

db = MySQLdb.connect("localhost", "root")
cursor = db.cursor()
try:
	cursor.execute('create DATABASE radius')
	cursor.execute('GRANT ALL ON radius.* TO radius@localhost IDENTIFIED BY PASSWORD("'+password+'")')
	cursor.execute('FLUSH PRIVILEGES')
	db.commit()
	print "Successfully created RADIUS database."
except:
	db.rollback()
	print "Creation of RADIUS database or creating user failed."

	
urllib.urlretrieve ("https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/stage3.sh", "stage3.sh")
subprocess.call(['chmod', '+x', 'stage3.sh'])
	
print "Type: sudo su"
print "once logged in as root type ./stage3.sh"
