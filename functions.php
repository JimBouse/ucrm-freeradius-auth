<?php
function ucrmDELETE($url, $return_type = "array") {
    global $uispKey, $uispUrl;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://".$uispUrl."/crm/api/v1.0".$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          "X-Auth-App-Key: ".$uispKey
        ));
        $response = curl_exec($ch);
        if (!curl_errno($ch)) {
                if ($return_type == 'array') {
                        return json_decode($response, true);
                } else {
                        return $response;
                }
        } else {
                return false;
        }

        curl_close($ch);
}

function ucrmGET($url, $return_type = "array") {
    global $uispKey, $uispUrl;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://".$uispUrl."/crm/api/v1.0".$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        // curl_setopt($ch, CURLOPT_VERBOSE, TRUE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          "X-Auth-App-Key: ".$uispKey
        ));

        $response = curl_exec($ch);
        if (!curl_errno($ch)) {
                if ($return_type == 'array') {
                        return json_decode($response, true);
                } else {
                        return $response;
                }
        } else {
                error_log(curl_errno($ch));
                return false;
        }

        curl_close($ch);
}


function ucrmPATCH($url, $json, $return_type = "array") {
    global $uispKey, $uispUrl;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://".$uispUrl."/crm/api/v1.0".$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          "X-Auth-App-Key: ".$uispKey
        ));
        $response = curl_exec($ch);
        if (!curl_errno($ch)) {
                if ($return_type == 'array') {
                        return json_decode($response, true);
                } else {
                        return $response;
                }
        } else {
                return false;
        }

        curl_close($ch);
}

function ucrmPOST($url, $json, $return_type = "array") {
    global $uispKey, $uispUrl;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://".$uispUrl."/crm/api/v1.0".$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          "X-Auth-App-Key: ".$uispKey
        ));
        $response = curl_exec($ch);
        if (!curl_errno($ch)) {
                if ($return_type == 'array') {
                        return json_decode($response, true);
                } else {
                        return $response;
                }
        } else {
                return false;
        }

        curl_close($ch);
}

// UNMS functions

function unmsDELETE($url, $return_type = "array") {
    global $uispKey, $uispUrl;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://".$uispUrl."/nms/api/v2.1".$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          "x-auth-token: ".$uispKey
        ));
        $response = curl_exec($ch);
        if (!curl_errno($ch)) {
                if ($return_type == 'array') {
                        return json_decode($response, true);
                } else {
                        return $response;
                }
        } else {
                return false;
        }

        curl_close($ch);
}

function unmsGET($url, $return_type = "array") {
    global $uispKey, $uispUrl;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://".$uispUrl."/nms/api/v2.1".$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          "x-auth-token: ".$uispKey
        ));

        $response = curl_exec($ch);
        if (!curl_errno($ch)) {
                if ($return_type == 'array') {
                        return json_decode($response, true);
                } else {
                        return $response;
                }
        } else {
                return false;
        }

        curl_close($ch);
}


function unmsPATCH($url, $json, $return_type = "array") {
    global $uispKey, $uispUrl;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://".$uispUrl."/nms/api/v2.1".$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          "x-auth-token: ".$uispKey
        ));
        $response = curl_exec($ch);
        if (!curl_errno($ch)) {
                if ($return_type == 'array') {
                        return json_decode($response, true);
                } else {
                        return $response;
                }
        } else {
                return false;
        }

        curl_close($ch);
}

function unmsPOST($url, $json, $return_type = "array") {
    global $uispKey, $uispUrl;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://".$uispUrl."/nms/api/v2.1".$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          "x-auth-token: ".$uispKey
        ));
        $response = curl_exec($ch);
        if (!curl_errno($ch)) {
                if ($return_type == 'array') {
                        return json_decode($response, true);
                } else {
                        return $response;
                }
        } else {
                return false;
        }

        curl_close($ch);
}

function unmsPUT($url, $json, $return_type = "array") {
    global $uispKey, $uispUrl;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://".$uispUrl."/nms/api/v2.1".$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          "x-auth-token: ".$uispKey
        ));
        $response = curl_exec($ch);
        if (!curl_errno($ch)) {
                if ($return_type == 'array') {
                        return json_decode($response, true);
                } else {
                        return $response;
                }
        } else {
                return false;
        }

        curl_close($ch);
}

?>
