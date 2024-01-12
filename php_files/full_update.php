<?php
set_time_limit(600);
require_once("config.php");
require_once("functions.php");

echo "Fetching ALL services\n";
$ucrmServices=ucrmGET("/clients/services");
$services = count($ucrmServices);
$i = 0;
foreach ($ucrmServices as $service) {
  $i++;
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
  curl_close($ch);
  echo $i." of ".$services."\n";
}
?>
