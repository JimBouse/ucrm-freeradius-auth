<?php
require_once('config.php');
require_once('functions.php');
require_once('PHPMailer-master/src/Exception.php');
require_once('PHPMailer-master/src/PHPMailer.php');
require_once('PHPMailer-master/src/SMTP.php');

$fp = fopen('/var/log/unknownDevices_post.log', 'a+');
//fwrite($fp, "\n".trim(file_get_contents("php://input")));


$link = mysqli_connect($db_host, $db_user, $db_pass, $db);
if (!$link) {
    die('Could not connect: ' . mysql_error());
}


//Make sure that it is a POST request.
if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
    throw new Exception('Request method must be POST!');
}

//Make sure that the content type of the POST request has been set to application/json
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if(strcasecmp($contentType, 'application/json') != 0){
    throw new Exception('Content type must be: application/json');
}

//Receive the RAW post data.
$content = trim(file_get_contents("php://input"));

//Attempt to decode the incoming RAW post data from JSON.
$json = json_decode($content, true);

//If json_decode failed, the JSON is invalid.
if(!is_array($json)){
    throw new Exception('Received content contained invalid JSON!');
}

//Process the JSON.



//fwrite($fp, "\n".print_r($json, TRUE));

$sql = "REPLACE INTO queue_name (`mac`, `queue`, `wanIP`, `www_port`, `ip`) VALUES ('".mysqli_real_escape_string($link, $json['data']['mac'])."', '".mysqli_real_escape_string($link, $json['data']['queue'])."', '".mysqli_real_escape_stri>
//fwrite($fp, "\n".$sql);
$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);

?>
