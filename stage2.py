import MySQLdb, urllib, subprocess

#User inputs sql loggin details
#username = raw_input("Put in the username you set for the sql database: ")
password = raw_input("Type the password you will use to login to radius@localhost user in the sql database: ")

db = MySQLdb.connect("localhost", "root")
cursor = db.cursor()

createUser = """CREATE USER 'radius'@'localhost' IDENTIFIED BY '{password}'"""

try:
        cursor.execute('create DATABASE radius')
        cursor.execute(createUser)
        cursor.execute('GRANT ALL ON radius.* TO radius@localhost')
        cursor.execute('FLUSH PRIVILEGES')
        db.commit()
        print "Successfully created RADIUS database."
except (MySQLdb.Error, MySQLdb.Warning) as e:
        db.rollback()
        print "Creation of RADIUS database or creating user failed."
        print(e);


urllib.urlretrieve ("https://raw.githubusercontent.com/jimbouse/ucrm-freeradius-auth/master/stage3.sh", "stage3.sh")
subprocess.call(['chmod', '+x', 'stage3.sh'])

print "Created radius user and password."
print "Type: ./stage3.sh"
