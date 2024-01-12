<?php
require_once("config.php");
require_once("functions.php");

$ucrmServices=ucrmGET("/clients/services");
foreach ($ucrmServices as $service) {
  $data = array();
  $data['eventName'] = "service.edit";
  $data['extraData']['entity'] = $service;
  $json = json_encode($data);
    
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "http://localhost/webhook.php");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($ch, CURLOPT_HEADER, FALSE);
  
  curl_setopt($ch, CURLOPT_POST, TRUE);
  
  curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
  
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Content-Type: application/json"
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
