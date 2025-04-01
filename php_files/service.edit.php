<?php
function checkRadGroupReply($groupname, $attribute, $op, $value) {
	global $fp, $link;
	$sql = "DELETE FROM radgroupreply WHERE groupname = '".$groupname."' AND attribute = '".$attribute."' AND (op <> '".$op."' OR value <> '".$value."')";
	fwrite($fp, "\n".$sql);
	$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);

	$sql = "INSERT INTO radgroupreply (groupname, attribute, op, value) (SELECT '".$groupname."', '".$attribute."', '".$op."', '".$value."' WHERE NOT EXISTS (SELECT true FROM radgroupreply WHERE groupname ='".$groupname."' AND attribute = '".$attribute."' AND op = '".$op."' AND value = '".$value."'))";
	fwrite($fp, "\n".$sql);
	$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);

	return;
}
$needsRadReplyFallThrough = 0;
$framedRouteArray = array();
// get MAC from attributes list.
foreach ($json['extraData']['entity']['attributes'] as $attrib) {
        if ($attrib['customAttributeId'] == $uispCustomAttribDeviceMac && preg_match('/^(?:(?:[0-9a-f]{2}[\:]{1}){5}|(?:[0-9a-f]{2}[-]{1}){5}|(?:[0-9a-f]{2}){5})[0-9a-f]{2}$/i', $attrib['value'])) {
                $mac = $attrib['value'];
        }

	if ($attrib['customAttributeId'] == $uispCustomAttribFramedRoute && !empty($attrib['value'])) {
		$framedRouteArray = explode(",", $attrib['value']);
	}
}
if (array_key_exists('entityBeforeEdit', $json['extraData'])) {
	foreach ($json['extraData']['entityBeforeEdit']['attributes'] as $attrib) {
	        if ($attrib['customAttributeId'] == $uispCustomAttribDeviceMac && preg_match('/^(?:(?:[0-9a-f]{2}[\:]{1}){5}|(?:[0-9a-f]{2}[-]{1}){5}|(?:[0-9a-f]{2}){5})[0-9a-f]{2}$/i', $attrib['value'])) {
	                $oldmac = $attrib['value'];
	        }
	}
}

// trim any whitespace from Framed-Route array.
$framedRouteArray = array_map('trim', $framedRouteArray);

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
		checkRadGroupReply($json['extraData']['entity']['servicePlanName'], 'Mikrotik-Rate-Limit', '=', $json['extraData']['entity']['uploadSpeed']."M/".$json['extraData']['entity']['downloadSpeed']."M");
		checkRadGroupReply($json['extraData']['entity']['servicePlanName'], 'Fall-Through', '=', 'Yes');
		
		checkRadGroupReply('Service_Prepared', 'Mikrotik-Address-List', ':=', 'Service_Prepared');
		checkRadGroupReply('Service_Prepared', 'Session-Timeout', ':=', '60');
		checkRadGroupReply('Service_Prepared', 'Fall-Through', '=', 'Yes');

		checkRadGroupReply('Service_Active', 'Mikrotik-Address-List', '+=', 'Service_Active');
		checkRadGroupReply('Service_Active', 'Session-Timeout', ':=', '21600');
		checkRadGroupReply('Service_Active', 'Fall-Through', '=', 'Yes');

		checkRadGroupReply('Service_Ended', 'Mikrotik-Address-List', ':=', 'Service_Ended');
		checkRadGroupReply('Service_Ended', 'Framed-Pool', ':=', 'Service_Ended_IP_Pool');
		checkRadGroupReply('Service_Ended', 'Session-Timeout', ':=', '60');
		checkRadGroupReply('Service_Ended', 'Fall-Through', '=', 'Yes');

		checkRadGroupReply('Service_Suspended', 'Mikrotik-Address-List', ':=', 'Service_Suspended');
		checkRadGroupReply('Service_Suspended', 'Session-Timeout', ':=', '60');
		checkRadGroupReply('Service_Suspended', 'Fall-Through', '=', 'Yes');

		checkRadGroupReply('Service_Unknown_Device', 'Mikrotik-Address-List', ':=', 'Service_Unknown_Device');
		checkRadGroupReply('Service_Unknown_Device', 'Session-Timeout', ':=', '60');
		checkRadGroupReply('Service_Unknown_Device', 'Fall-Through', '=', 'Yes');



		// this section is executed if a speed override is applied.
		if (intval($json['extraData']['entity']['trafficShapingOverrideEnabled']) == 1) {
			$needsRadReplyFallThrough = 1;
			$download = $json['extraData']['entity']['downloadSpeedOverride'];
			$upload = $json['extraData']['entity']['uploadSpeedOverride'];
			fwrite($fp, "\nApplying speed override ".$download."/".$upload." to ".$mac);

			$sql = "INSERT INTO radreply (username, attribute, op, value) (SELECT '".$mac."', 'Mikrotik-Rate-Limit', '=', '".$upload."M/".$download."M' WHERE NOT EXISTS(SELECT username FROM radreply WHERE username = '".$mac."' AND attribute = 'Mikrotik-Rate-Limit'))";
			fwrite($fp, "\n".$sql);
			$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);

			$sql = "UPDATE radreply SET value = '".$upload."M/".$download."M' WHERE username = '".$mac."' AND attribute = 'Mikrotik-Rate-Limit' AND value <> '".$upload."M/".$download."M'";
			fwrite($fp, "\n".$sql);
			$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);

//			$sql = "INSERT INTO radreply (username, attribute, op, value) (SELECT '".$mac."', 'Fall-Through', '=', 'Yes' WHERE NOT EXISTS(SELECT username FROM radreply WHERE username = '".$mac."' AND attribute = 'Fall-Through'))";
//			fwrite($fp, "\n".$sql);
//			$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
		} else {
			fwrite($fp, "\nClearing speed override\n".$sql);
			$sql = "DELETE FROM radreply WHERE username = '".$mac."' AND attribute = 'Mikrotik-Rate-Limit'";
			fwrite($fp, "\n".$sql);
			$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
		}

		if (count($framedRouteArray) > 0) {
			$needsRadReplyFallThrough = 1;
			// insert framed route for certain MAC address
			foreach ($framedRouteArray as $route) {
				//$sql = "INSERT INTO radreply (username, attribute, op, value) (SELECT '".$mac."', 'Framed-Route', '=', '".trim($route)."') WHERE NOT EXISTS(SELECT username FROM radreply WHERE username = '".$mac."' AND attribute = 'Framed-Route' AND value = '".$route."')";
				$sql = "INSERT INTO radreply (username, attribute, op, value) (SELECT '".$mac."', 'Framed-Route', '=', '".$route."' WHERE NOT EXISTS(SELECT username FROM radreply WHERE username = '".$mac."' AND attribute = 'Framed-Route' AND value = '".$route."'))";
				fwrite($fp, "\n".$sql);
	                        $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
			}
			// remove any framed routes that are no longer applied to MAC address
			$sql = "DELETE FROM radreply WHERE username = '".$mac."' AND attribute = 'Framed-Route' AND value NOT IN ('".implode("','", $framedRouteArray)."')";
			fwrite($fp, "\n".$sql);
                        $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);

		} else {
			$sql = "DELETE FROM radreply WHERE username = '".$mac."' AND attribute = 'Framed-Route'";
			fwrite($fp, "\n".$sql);
                        $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
		}

		if ($needsRadReplyFallThrough == 1) {
			// add Fall-Through attribute
			$sql = "INSERT INTO radreply (username, attribute, op, value) (SELECT '".$mac."', 'Fall-Through', '=', 'Yes' WHERE NOT EXISTS(SELECT username FROM radreply WHERE username = '".$mac."' AND attribute = 'Fall-Through'))";

                        fwrite($fp, "\n".$sql);
                        $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
		} else {
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
						$nonActiveServices = ucrmGET("/clients/services?customAttributeId=".$uispCustomAttribDeviceMac."&customAttributeValue=".$mac);
						foreach ($nonActiveServices as $oldService) {
							if (intval($oldService['status']) !== 0 && intval($oldService['status']) !== 1) {
								fwrite($fp, "\nAlready ENDED service for MAC ".$mac." detected.  Removing reference to MAC");
								foreach ($oldService['attributes'] as $attrib) {
									if (strtolower($attrib['key']) == "devicemac" && strtolower($attrib['value']) == strtolower($mac)) {
										$uArr = [];
										$uArr['attributes'][] = array('value' => "", 'customAttributeId' => $uispCustomAttribDeviceMac);
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
