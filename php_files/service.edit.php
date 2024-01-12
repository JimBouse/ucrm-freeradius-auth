<?php
// get MAC from attributes list.
foreach ($json['extraData']['entity']['attributes'] as $attrib) {
        if ($attrib['key'] == 'devicemac' && preg_match('/^(?:(?:[0-9a-f]{2}[\:]{1}){5}|(?:[0-9a-f]{2}[-]{1}){5}|(?:[0-9a-f]{2}){5})[0-9a-f]{2}$/i', $attrib['value'])) {
                $mac = $attrib['value'];
        } elseif ($attrib['key'] == 'devicemac' && !empty($attrib['value'])) {
                // not a MAC address.  Likely a static IP or Business Client
                $deviceMacValue = $attrib['value'];
        } elseif ($attrib['customAttributeId'] == 24 && !empty($attrib['value'])) {
                $rxsignal = $attrib['value'];
        }
}
foreach ($json['extraData']['entityBeforeEdit']['attributes'] as $attrib) {
        if ($attrib['key'] == 'devicemac' && preg_match('/^(?:(?:[0-9a-f]{2}[\:]{1}){5}|(?:[0-9a-f]{2}[-]{1}){5}|(?:[0-9a-f]{2}){5})[0-9a-f]{2}$/i', $attrib['value'])) {
                $oldmac = $attrib['value'];
        }
}
//$clientResult = ucrmGET('/clients/'.intval($json['extraData']['entity']['clientId']));
//fwrite($fp, "\n".print_r($clientResult, TRUE));
if ($json['extraData']['entity']['servicePlanType'] == 'Internet') {
        //fwrite($fp, "\n".$_SERVER['REMOTE_ADDR'].":\n".print_r($json, TRUE));
        if (isset($mac)) {
                // found a mac address.  Now to check on things.

                switch (intval($json['extraData']['entity']['status'])) {
                        case 0: // Prepared
                                fwrite($fp, "\nPrepared Service");

                                // break;
                        case 1: // Active
                                fwrite($fp, "\nActive Service");
                                if ($json['extraData']['entity']['hasOutage'] == 1 && empty($json['extraData']['entityBeforeEdit']['hasOutage'])) {
                                        // has outage
                                        fwrite($fp, "\n".intval($json['extraData']['entity']['id'])." Outage Alert - Has Outage, add to outage list");
                                } elseif (empty($json['extraData']['entity']['hasOutage']) && $json['extraData']['entityBeforeEdit']['hasOutage'] == 1) {
                                        // remove from outage list
                                        fwrite($fp, "\n".intval($json['extraData']['entity']['id'])." Outage Alert - Outage Cleared");
                                } else {

                                        // see if an existing profile (not this one) has the same MAC address.  Remove the mac address from the old profile if so.
                                        $nonActiveServices = ucrmGET("/clients/services?customAttributeId=2&customAttributeValue=".$mac);
                                        foreach ($nonActiveServices as $oldService) {
                                                if (intval($oldService['status']) !== 0 && intval($oldService['status']) !== 1) {
                                                        fwrite($fp, "\nAlready ENDED service for MAC ".$mac." detected.  Removing reference to MAC");
                                                        foreach ($oldService['attributes'] as $attrib) {
                                                                if ($attrib['key'] == "devicemac" && strtolower($attrib['value']) == strtolower($mac)) {
                                                                        $uArr = [];
                                                                        $uArr['attributes'][] = array('value' => "", 'customAttributeId' => 2);
                                                                        $uDelete = ucrmPATCH("clients/services/".$oldService['id'], json_encode($uArr));
                                                                        fwrite($fp, "\nRemoved MAC ".$mac." from service ID: ".$oldService['id']);
                                                                }
                                                        }
                                                }
                                        }



                                        $sql = "DELETE FROM radcheck WHERE username = '".$mac."'";
                                        fwrite($fp, "\n".$sql);
                                        $result=mysqli_query($link,$sql) or die(mysqli_error($link)." Q=".$sql);

                                        $sql = "REPLACE INTO radcheck(username, attribute, op, value) VALUES ('".$mac."', 'Auth-type', ':=', 'Accept')";
                                        fwrite($fp, "\n".$sql);
                                        $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);


                                        $sql = "DELETE FROM radusergroup WHERE username = '".$mac."'";
                                        fwrite($fp, "\n".$sql);
                                        $result=mysqli_query($link,$sql) or die(mysqli_error($link)." Q=".$sql);

                                        $sql = "INSERT INTO radusergroup(username, groupname, priority) VALUES ('".$mac."', '".$json['extraData']['entity']['servicePlanName']."', 1)";
                                        fwrite($fp, "\n".$sql);
                                        $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);

                                        $sql = "DELETE FROM userbillinfo WHERE username = '".$mac."'";
                                        fwrite($fp, "\n".$sql);
                                        $result=mysqli_query($link,$sql) or die(mysqli_error($link)." Q=".$sql);
                                        
                                        $sql = "INSERT INTO userbillinfo (username, planName) VALUES ('".$mac."', '".$json['extraData']['entity']['servicePlanName']."')";
                                        fwrite($fp, "\n".$sql);
                                        $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
                                        
                                        $sql = "DELETE FROM userinfo WHERE username = '".$mac."'";
                                        fwrite($fp, "\n".$sql);
                                        $result=mysqli_query($link,$sql) or die(mysqli_error($link)." Q=".$sql);
                                        
                                        $sql = "INSERT INTO userinfo (username, firstname, lastname) VALUES ('".$mac."', '".$json['extraData']['entity']['clientId']."', '".$json['extraData']['entity']['id']."')";
                                        fwrite($fp, "\n".$sql);
                                        $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);

                                        $radGroupReply_MikrotikRateLimit = 1;
                                        $radGroupReply_MikrotikAddressListPreparedService = 1;
                                        $radGroupReply_MikrotikAddressListActiveService = 1;
                                        $radGroupReply_MikrotikAddressListEndedService = 1;
                                        $radGroupReply_MikrotikAddressListSuspendedService = 1;
                                        $sql = "SELECT attribute, value FROM radgroupreply";
                                        $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
                                        while ($row = mysqli_fetch_assoc($result)) {
                                                if ($row['attribute'] == 'Mikrotik-Rate-Limit') {
                                                        if ($row['value'] == $json['extraData']['entity']['uploadSpeed']."M/".$json['extraData']['entity']['downloadSpeed']."M") {
                                                                $radGroupReply_MikrotikRateLimit = 0;
                                                        }
                                                }
                                                if ($row['attribute'] == 'Mikrotik-Address-List') {
                                                        if ($row['value'] == "Prepared_Service") {
                                                               $radGroupReply_MikrotikAddressListPreparedService = 0;
                                                        }
                                                        if ($row['value'] == "Active_Service") {
                                                               $radGroupReply_MikrotikAddressListActiveService = 0;
                                                        }
                                                        
                                                        if ($row['value'] == "Ended_Service") {
                                                               $radGroupReply_MikrotikAddressListEndedService = 0;
                                                        }
                                                        
                                                        if ($row['value'] == "Suspended_Service") {
                                                               $radGroupReply_MikrotikAddressListSuspendedService = 0;
                                                        }
                                                }
                                        }
                                        if ($radGroupReply_MikrotikRateLimit == 1) {
                                                $sql = "DELETE FROM radgroupreply WHERE groupname ='".$json['extraData']['entity']['servicePlanName']."' AND attribute = 'Mikrotik-Rate-Limit'";
                                                fwrite($fp, "\n".$sql);
                                                $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
                                                
                                                $sql = "INSERT INTO radgroupreply (groupname, attribute, op, value) VALUES ('".$json['extraData']['entity']['servicePlanName']."', 'Mikrotik-Rate-Limit', ':=', '".$json['extraData']['entity']['uploadSpeed']."M/".$json['extraData']['entity']['downloadSpeed']."M')";
                                                fwrite($fp, "\n".$sql);
                                                $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
                                        }

                                        if ($radGroupReply_MikrotikAddressListPreparedService == 1) {
                                                $sql = "DELETE FROM radgroupreply WHERE groupname ='Prepared_Service' AND attribute = 'Mikrotik-Address-List'";
                                                fwrite($fp, "\n".$sql);
                                                $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
                                                
                                                $sql = "INSERT INTO radgroupreply (groupname, attribute, op, value) VALUES ('Prepared_Service', 'Mikrotik-Address-List', ':=', 'Prepared_Service')";
                                                fwrite($fp, "\n".$sql);
                                                $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
                                        }
                                        
                                        if ($radGroupReply_MikrotikAddressListActiveService == 1) {
                                                $sql = "DELETE FROM radgroupreply WHERE groupname ='Active_Service' AND attribute = 'Mikrotik-Address-List'";
                                                fwrite($fp, "\n".$sql);
                                                $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
                                                
                                                $sql = "INSERT INTO radgroupreply (groupname, attribute, op, value) VALUES ('Active_Service', 'Mikrotik-Address-List', ':=', 'Active_Service')";
                                                fwrite($fp, "\n".$sql);
                                                $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
                                        }
                                        
                                        if ($radGroupReply_MikrotikAddressListEndedService == 1) {
                                                $sql = "DELETE FROM radgroupreply WHERE groupname ='Ended_Service' AND attribute = 'Mikrotik-Address-List'";
                                                fwrite($fp, "\n".$sql);
                                                $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
                                                
                                                $sql = "INSERT INTO radgroupreply (groupname, attribute, op, value) VALUES ('Ended_Service', 'Mikrotik-Address-List', ':=', 'Ended_Service')";
                                                fwrite($fp, "\n".$sql);
                                                $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
                                        }
                                        
                                        if ($radGroupReply_MikrotikAddressListSuspendedService == 1) {
                                                $sql = "DELETE FROM radgroupreply WHERE groupname ='Suspended_Service' AND attribute = 'Mikrotik-Address-List'";
                                                fwrite($fp, "\n".$sql);
                                                $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
                                                
                                                $sql = "INSERT INTO radgroupreply (groupname, attribute, op, value) VALUES ('Suspended_Service', 'Mikrotik-Address-List', ':=', 'Suspended_Service')";
                                                fwrite($fp, "\n".$sql);
                                                $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
                                        }

                                        
                                        if (intval($json['extraData']['entity']['trafficShapingOverrideEnabled']) == 1) {
                                                $download = $json['extraData']['entity']['downloadSpeedOverride'];
                                                $upload = $json['extraData']['entity']['uploadSpeedOverride'];
                                                $overrideEnabled = 1;
                                        } else {
                                                $download = $json['extraData']['entity']['downloadSpeed'];
                                                $upload = $json['extraData']['entity']['uploadSpeed'];
                                                $overrideEnabled = 0;
                                        }
                                }

                                break;
                        case 2: // Ended
                                fwrite($fp, "\nEnded");
                                // $sql = "DELETE FROM ucrm_services WHERE serviceId = '".san($json['extraData']['entity']['id'])."' OR mac = '".san(strtoupper(str_ireplace(":", "", $mac)))."'";
                                // fwrite($fp, "\n".$sql);
                                // $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
                                break;
                        case 3: // Suspended
                                fwrite($fp, "\nSuspended");

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
