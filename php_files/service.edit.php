<?php
$customAttribKeyForMAC = 0;
// get MAC from attributes list.
foreach ($json['extraData']['entity']['attributes'] as $attrib) {
        if (strtolower($attrib['key']) == 'devicemac' && preg_match('/^(?:(?:[0-9a-f]{2}[\:]{1}){5}|(?:[0-9a-f]{2}[-]{1}){5}|(?:[0-9a-f]{2}){5})[0-9a-f]{2}$/i', $attrib['value'])) {
                $mac = $attrib['value'];
				$customAttribKeyForMAC = $attrib['customAttributeId'];
        } elseif (strtolower($attrib['key']) == 'devicemac' && !empty($attrib['value'])) {
                // not a MAC address.  Likely a static IP or Business Client
                $deviceMacValue = $attrib['value'];
        } 
}
if (array_key_exists('entityBeforeEdit', $json['extraData'])) {
	foreach ($json['extraData']['entityBeforeEdit']['attributes'] as $attrib) {
	        if ($attrib['key'] == 'devicemac' && preg_match('/^(?:(?:[0-9a-f]{2}[\:]{1}){5}|(?:[0-9a-f]{2}[-]{1}){5}|(?:[0-9a-f]{2}){5})[0-9a-f]{2}$/i', $attrib['value'])) {
	                $oldmac = $attrib['value'];
	        }
	}
}

if ($json['extraData']['entity']['servicePlanType'] == 'Internet') {

        if (isset($mac)) {
		// found a mac address.  Now to check on things.

		$sql = "INSERT INTO radcheck (username, attribute, op, value) (SELECT '".$mac."', 'Auth-type', ':=', 'Accept' WHERE NOT EXISTS(SELECT username FROM radcheck WHERE username = '".$mac."'))";
		fwrite($fp, "\n".$sql);
		$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);


		$sql = "DELETE FROM radusergroup WHERE username = '".$mac."'";
		fwrite($fp, "\n".$sql);
		$result=mysqli_query($link,$sql) or die(mysqli_error($link)." Q=".$sql);

		$sql = "INSERT INTO radusergroup(username, groupname, priority) VALUES ('".$mac."', '".$json['extraData']['entity']['servicePlanName']."', 1)";
		fwrite($fp, "\n".$sql);
		$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);

		// Make sure radgroupreply table is setup correctly.

		$sql = "DELETE FROM radgroupreply WHERE groupname ='".$json['extraData']['entity']['servicePlanName']."' AND attribute = 'Mikrotik-Rate-Limit' AND value <> '".$json['extraData']['entity']['servicePlanName']."', 'Mikrotik-Rate-Limit', '=', '".$json['extraData']['entity']['uploadSpeed']."M/".$json['extraData']['entity']['downloadSpeed']."M'";
		fwrite($fp, "\n".$sql);
		$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
		$sql = "INSERT INTO radgroupreply (
  				groupname, 
      				attribute, 
	  			op, 
      				value
	  			) 
  			 VALUES (
      				'".$json['extraData']['entity']['servicePlanName']."', 
	  			'Mikrotik-Rate-Limit', 
      				'=', 
	  			'".$json['extraData']['entity']['uploadSpeed']."M/".$json['extraData']['entity']['downloadSpeed']."M'
      				)
      			 WHERE NOT EXISTS(
	  			SELECT FROM radgroupreply WHERE 
      					groupname ='".$json['extraData']['entity']['servicePlanName']."' AND 
	   				attribute = 'Mikrotik-Rate-Limit' AND 
					op = '=' AND
					value = '".$json['extraData']['entity']['servicePlanName']."'
     				)
	 		";
		fwrite($fp, "\n".$sql);
		$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
die;
		$radGroupReply_MikrotikRateLimit = 1;
		$radGroupReply_MikrotikAddressListPreparedService = 1;
		$radGroupReply_MikrotikAddressListActiveService = 1;
		$radGroupReply_MikrotikAddressListEndedService = 1;
		$radGroupReply_MikrotikAddressListSuspendedService = 1;
		$radGroupReply_MikrotikAddressListUnknownDeviceService = 1;
		$sql = "SELECT groupname, attribute, op, value FROM radgroupreply";
		$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
		
		while ($row = mysqli_fetch_assoc($result)) {
			if ($row['attribute'] == 'Mikrotik-Rate-Limit') {
				if ($row['value'] !== $json['extraData']['entity']['uploadSpeed']."M/".$json['extraData']['entity']['downloadSpeed']."M") {
					
					
					
				}
			}
			if ($row['attribute'] == 'Mikrotik-Address-List') {
				if ($row['value'] == "Service_Prepared") {
							   $radGroupReply_MikrotikAddressListPreparedService = 0;
						}
						if ($row['value'] == "Service_Active") {
							   $radGroupReply_MikrotikAddressListActiveService = 0;
						}
						
						if ($row['value'] == "Service_Ended") {
							   $radGroupReply_MikrotikAddressListEndedService = 0;
						}
						
						if ($row['value'] == "Service_Suspended") {
							   $radGroupReply_MikrotikAddressListSuspendedService = 0;
						}
						if ($row['value'] == "Service_Unknown_Device") {
							   $radGroupReply_MikrotikAddressListUnknownDeviceService = 0;
						}
				}
		}
		if ($radGroupReply_MikrotikRateLimit == 1) {
				$sql = "DELETE FROM radgroupreply WHERE groupname ='".$json['extraData']['entity']['servicePlanName']."' AND attribute = 'Mikrotik-Rate-Limit'";
				fwrite($fp, "\n".$sql);
				$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
				
				$sql = "INSERT INTO radgroupreply (groupname, attribute, op, value) VALUES ('".$json['extraData']['entity']['servicePlanName']."', 'Mikrotik-Rate-Limit', '=', '".$json['extraData']['entity']['uploadSpeed']."M/".$json['extraData']['entity']['downloadSpeed']."M'),('".$json['extraData']['entity']['servicePlanName']."', 'Fall-Through', '=', 'Yes')";
				fwrite($fp, "\n".$sql);
				$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
		}

		if ($radGroupReply_MikrotikAddressListPreparedService == 1) {
				$sql = "DELETE FROM radgroupreply WHERE groupname ='Service_Prepared' AND attribute = 'Mikrotik-Address-List'";
				fwrite($fp, "\n".$sql);
				$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
				
				$sql = "INSERT INTO radgroupreply (groupname, attribute, op, value) VALUES ('Service_Prepared', 'Mikrotik-Address-List', ':=', 'Service_Prepared'),('Service_Prepared', 'Session-Timeout', ':=', '60'),('Service_Prepared', 'Fall-Through', '=', 'Yes')";
				fwrite($fp, "\n".$sql);
				$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
		}
		
		if ($radGroupReply_MikrotikAddressListActiveService == 1) {
				$sql = "DELETE FROM radgroupreply WHERE groupname ='Service_Active' AND attribute = 'Mikrotik-Address-List'";
				fwrite($fp, "\n".$sql);
				$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
				
				$sql = "INSERT INTO radgroupreply (groupname, attribute, op, value) VALUES ('Service_Active', 'Mikrotik-Address-List', '+=', 'Service_Active'),('Service_Active', 'Session-Timeout', ':=', '21600'),('Service_Active', 'Fall-Through', '=', 'Yes')";
				fwrite($fp, "\n".$sql);
				$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
		}
		
		if ($radGroupReply_MikrotikAddressListEndedService == 1) {
				$sql = "DELETE FROM radgroupreply WHERE groupname ='Service_Ended' AND attribute = 'Mikrotik-Address-List'";
				fwrite($fp, "\n".$sql);
				$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
				
				$sql = "INSERT INTO radgroupreply (groupname, attribute, op, value) VALUES ('Service_Ended', 'Mikrotik-Address-List', ':=', 'Service_Ended'),('Service_Ended', 'Framed-Pool', ':=', 'Service_Ended_IP_Pool'),('Service_Ended', 'Session-Timeout', ':=', '60'),('Service_Ended', 'Fall-Through', '=', 'Yes')";
				fwrite($fp, "\n".$sql);
				$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
		}
		
		if ($radGroupReply_MikrotikAddressListSuspendedService == 1) {
				$sql = "DELETE FROM radgroupreply WHERE groupname ='Service_Suspended' AND attribute = 'Mikrotik-Address-List'";
				fwrite($fp, "\n".$sql);
				$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
				
				$sql = "INSERT INTO radgroupreply (groupname, attribute, op, value) VALUES ('Service_Suspended', 'Mikrotik-Address-List', ':=', 'Service_Suspended'),('Service_Suspended', 'Session-Timeout', ':=', '60'),('Service_Suspended', 'Fall-Through', '=', 'Yes')";
				fwrite($fp, "\n".$sql);
				$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
		}

				
		if ($radGroupReply_MikrotikAddressListUnknownDeviceService == 1) {
				$sql = "DELETE FROM radgroupreply WHERE groupname ='Service_Unknown_Device' AND attribute = 'Mikrotik-Address-List'";
				fwrite($fp, "\n".$sql);
				$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
				
				$sql = "INSERT INTO radgroupreply (groupname, attribute, op, value) VALUES ('Service_Unknown_Device', 'Mikrotik-Address-List', ':=', 'Service_Unknown_Device'),('Service_Unknown_Device', 'Session-Timeout', ':=', '60'),('Service_Unknown_Device', 'Fall-Through', '=', 'Yes')";
				fwrite($fp, "\n".$sql);
				$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
		}

		// this section is executed if a speed override is applied.
		if (intval($json['extraData']['entity']['trafficShapingOverrideEnabled']) == 1) {
				$download = $json['extraData']['entity']['downloadSpeedOverride'];
				$upload = $json['extraData']['entity']['uploadSpeedOverride'];
				fwrite($fp, "\nApplying speed override ".$download."/".$upload." to ".$mac);
				
				$sql = "INSERT INTO radreply (username, attribute, op, value) (SELECT '".$mac."', 'Mikrotik-Rate-Limit', '=', '".$upload."M/".$download."M' WHERE NOT EXISTS(SELECT username FROM radreply WHERE username = '".$mac."' AND attribute = 'Mikrotik-Rate-Limit'))";
				fwrite($fp, "\n".$sql);
				$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
				
				$sql = "UPDATE radreply SET value = '".$upload."M/".$download."M' WHERE username = '".$mac."' AND attribute = 'Mikrotik-Rate-Limit' AND value <> '".$upload."M/".$download."M'";
				fwrite($fp, "\n".$sql);
				$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
				
				$sql = "INSERT INTO radreply (username, attribute, op, value) (SELECT '".$mac."', 'Fall-Through', '=', 'Yes' WHERE NOT EXISTS(SELECT username FROM radreply WHERE username = '".$mac."' AND attribute = 'Fall-Through'))";
				fwrite($fp, "\n".$sql);
				$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
				
		} else {
				fwrite($fp, "\nCleariing speed override".$sql);
				$sql = "DELETE FROM radreply WHERE username = '".$mac."' AND attribute = 'Mikrotik-Rate-Limit'";
				fwrite($fp, "\n".$sql);
				$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
				
				$sql = "DELETE FROM radreply WHERE username = '".$mac."' AND attribute = 'Fall-Through'";
				fwrite($fp, "\n".$sql);
				$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
				
		}

		switch (intval($json['extraData']['entity']['status'])) {
				case 0: // Prepared
					fwrite($fp, "\nPrepared Service");
					$sql = "INSERT INTO radusergroup(username, groupname, priority) VALUES ('".$mac."', 'Service_Prepared', 1)";
                                        fwrite($fp, "\n".$sql);
                                        $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);


						break;
				case 1: // Active
						fwrite($fp, "\nActive Service");
						
						// see if an existing profile (not this one) has the same MAC address.  Remove the mac address from the old profile if so.
						$nonActiveServices = ucrmGET("/clients/services?customAttributeId=".$customAttribKeyForMAC."&customAttributeValue=".$mac);
						foreach ($nonActiveServices as $oldService) {
							if (intval($oldService['status']) !== 0 && intval($oldService['status']) !== 1) {
								fwrite($fp, "\nAlready ENDED service for MAC ".$mac." detected.  Removing reference to MAC");
								foreach ($oldService['attributes'] as $attrib) {
									if (strtolower($attrib['key']) == "devicemac" && strtolower($attrib['value']) == strtolower($mac)) {
										$uArr = [];
										$uArr['attributes'][] = array('value' => "", 'customAttributeId' => $customAttribKeyForMAC);
										$uDelete = ucrmPATCH("clients/services/".$oldService['id'], json_encode($uArr));
										fwrite($fp, "\nRemoved MAC ".$mac." from service ID: ".$oldService['id']);
									}
								}
							}
						}

						$sql = "INSERT INTO radusergroup(username, groupname, priority) VALUES ('".$mac."', 'Service_Active', 1)";
						fwrite($fp, "\n".$sql);
						$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);



								
						break;
				case 2: // Ended
						fwrite($fp, "\nEnded");
						$sql = "INSERT INTO radusergroup(username, groupname, priority) VALUES ('".$mac."', 'Service_Ended', 1)";
						fwrite($fp, "\n".$sql);
						$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
						
						break;
				case 3: // Suspended
						fwrite($fp, "\nSuspended");
						$sql = "INSERT INTO radusergroup(username, groupname, priority) VALUES ('".$mac."', 'Service_Suspended', 1)";
						fwrite($fp, "\n".$sql);
						$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);

						break;
				case 4: // Prepared blocked
						fwrite($fp, "\nPrepared Blocked");

						break;
				case 5: // Obsolete
						fwrite($fp, "\nObsolete");
						
						break;
				case 6: // Deferred
						fwrite($fp, "\nDeferred");
						// fwrite($fp, "\n".print_r($json, TRUE));

						break;
				case 7: // Quoted


						break;
		}
	}
} else {
        fwrite($fp, "\n".intval($json['extraData']['entity']['id'])." Not an internet service");
}
fwrite($fp, "\n");
?>
