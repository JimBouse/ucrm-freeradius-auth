DROP TRIGGER IF EXISTS  `add_unknown_devices`;
DELIMITER //
CREATE TRIGGER `add_unknown_devices` AFTER INSERT ON `radpostauth` 
FOR EACH ROW 
BEGIN
   IF NEW.reply = 'Access-Reject' THEN
     INSERT INTO radcheck (username, attribute, op, value) (SELECT NEW.username, 'Auth-type', ':=', 'Accept' WHERE NOT EXISTS(SELECT username FROM radcheck WHERE username = NEW.username));
     INSERT INTO radusergroup(username, groupname, priority) VALUES (NEW.username, 'Service_Unknown_Device', 1);
   END IF;
END//
DELIMITER ;
