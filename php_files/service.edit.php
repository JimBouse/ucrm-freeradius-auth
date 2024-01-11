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
//$clientResult = ucrmGET('clients/'.intval($json['extraData']['entity']['clientId']));

if ($json['extraData']['entity']['servicePlanType'] == 'Internet') {
        // fwrite($fp, "\n".$_SERVER['REMOTE_ADDR'].":\n".print_r($json, TRUE));
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
                                        $nonActiveServices = ucrmGET("clients/services?customAttributeId=2&customAttributeValue=".$mac);
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

                                        if (intval($json['extraData']['entity']['trafficShapingOverrideEnabled']) == 1) {
                                                $download = $json['extraData']['entity']['downloadSpeedOverride'];
                                                $upload = $json['extraData']['entity']['uploadSpeedOverride'];
                                                $overrideEnabled = 1;
                                        } else {
                                                $download = $json['extraData']['entity']['downloadSpeed'];
                                                $upload = $json['extraData']['entity']['uploadSpeed'];
                                                $overrideEnabled = 0;
                                        }

                                        $sql = "SELECT value FROM radcheck WHERE username = '".$mac."'";
                                        $result=mysqli_query($link,$sql) or die(mysqli_error($link)." Q=".$sql);
                                        while ($row = mysqli_fetch_assoc($result)) {
                                                $updateRadCheck = "UPDATE radcheck SET value = 'Accept' WHERE username = '".$mac."'";
                                        }
                                        if (isset($updateRadCheck)) {
                                                $sql = $updateRadCheck;
                                        } else {
                                                $sql = "REPLACE INTO radcheck(username, attribute, op, value) VALUES ('".$mac."', 'Auth-type', ':=', 'Accept')";
                                        }
                                        fwrite($fp, "\n".$sql);
                                        $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);

                                        $sql = "REPLACE INTO radusergroup(username, groupname, priority) VALUES ('".$mac."', '".$json['extraData']['entity']['servicePlanName']."', 1)";
                                        fwrite($fp, "\n".$sql);
                                        $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);

                                        $sql = "REPLACE INTO userbillinfo(username, planName) VALUES ('".$mac."', '".$json['extraData']['entity']['servicePlanName']."')";
                                        fwrite($fp, "\n".$sql);
                                        $result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);

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
?>