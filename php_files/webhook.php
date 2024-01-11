<?php
$fp = fopen('/var/log/webhook_request.log', 'a');
fwrite($fp, "\n\n---webhook.php request--- /var/www/html/webhook.php");
if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
    throw new Exception('Request method must be POST!');
}

//Make sure that the content type of the POST request has been set to application/json
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if(strcasecmp($contentType, 'application/json') != 0){
    // throw new Exception('Content type must be: application/json');
}

//Receive the RAW post data.
$content = trim(file_get_contents("php://input"));

//Attempt to decode the incoming RAW post data from JSON.
$json = json_decode($content, true);

//If json_decode failed, the JSON is invalid.
if(!is_array($json)){
    // throw new Exception('Received content contained invalid JSON!');
    // fwrite($fp, "\nBad JSON");
}
fwrite($fp, "\nJSON: ".print_r($json, TRUE));

?>
