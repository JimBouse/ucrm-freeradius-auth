<?php
require_once("config.php");
require_once("functions.php");

$ucrmServices=ucrmGET("/clients/services");
foreach ($ucrmServices as $service) {
  $url = 'http://localhost/webhook.php';
  
  $data = array();
  $data['eventName'] = "service.edit";
  $data['extraData]['entity'] = $service;
  
  $data_string = json_encode($data);
  $ch=curl_init($url);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, array("customer"=>$data_string));
  curl_setopt($ch, CURLOPT_HEADER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER,
      array(
          'Content-Type:application/json',
          'Content-Length: ' . strlen($data_string)
      )
  );
  
  $result = curl_exec($ch);
  curl_close($ch);
}
?>
