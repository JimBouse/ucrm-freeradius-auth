<?php
require_once('config.php');
require_once('functions.php');
require_once('PHPMailer-master/src/Exception.php');
require_once('PHPMailer-master/src/PHPMailer.php');
require_once('PHPMailer-master/src/SMTP.php');

$fp = fopen('/var/log/unknownDevices_post.log', 'a+');


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



fwrite($fp, "\n".print_r($json, TRUE));

$body = "Unknown devices on ".$json['ident'].".<br>";
$body .= "IP of router: ".$json['wanIP']."<br>";
if ($_SERVER['REMOTE_ADDR'] !== $json['wanIP']) {
	$body .= "Note: The router may be behind NAT.  Request came from ".$_SERVER['REMOTE_ADDR']."<br>";
}
$body .= "<a href=\"http://".$json['wanIP'].":".$json['httpPort']."\">http://".$json['wanIP'].":".$json['httpPort']."</a><hr>";
$i = 0;
foreach ($json['data'] as $device) {
	$i++;
	$body .= "Device ".$i."<br>";
	if (empty($device['remoteId'])) {
		$macOnRecord = strtoupper($device['mac']);
	} else {
		$body .= "Note: Device is in bridge mode.  MAC address is of bridge device, not customer equipment.<br>";
		$macOnRecord = $device['remoteId'];
	}
	if (preg_match('/^(?:(?:[0-9a-f]{2}[\:]{1}){5}|(?:[0-9a-f]{2}[-]{1}){5}|(?:[0-9a-f]{2}){5})[0-9a-f]{2}$/i', $macOnRecord)) {
		$macOnRecord = strtoupper(implode(":", str_split(str_replace(".", "", preg_replace("/[^A-Za-z0-9 ]/", '', $macOnRecord)), 2)));
		$body .= "MAC: ".$macOnRecord."<br>";
	} else {
		$body .= "MAC (odd formatting, perhaps not MAC): ".$macOnRecord."<br>";
	}
	
	$body .= "IP: ".$device['leasedIP']."</br>";
	$body .= "Hostname: ".$device['hostname']."</br>";
	if (!empty($device['remoteId'])) {
		$body .= "Customer MAC: ".strtoupper($device['mac'])."<br>";
		$body .= "Remote ID: ".$device['remoteId']."<br>";
		$body .= "Circuit ID: ".$device['circuitId']."<br>";
	}
	$body .= "Interface on router: ".$device['interface']."<br>";
	$body .= "<br>";
}

	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
if (!empty($smtpHost)) {


	$mail = new PHPMailer;
	$mail->IsSMTP();
	if (!empty($smtpUser)) {
		$mail->SMTPAuth = true;
		$mail->Username   = $smtpUser;
		$mail->Password   = $smtpPass;
	} else {
		$mail->SMTPAuth = false;
	}
	$mail->Host = $smtpHost;
	$mail->Port = $smtpPort;
	$mail->setFrom($smtpFrom);
	$mail->addAddress($smtpTo);
	$mail->isHTML(true);
	$mail->Subject  = "Unknown Devices on ".$json['ident'];
	$mail->MsgHTML($body);
	if(!$mail->send()) {
		fwrite($fp, "\nFailure to send email.");
	} else {
		fwrite($fp, "\nSuccess sending email.");
	}
}

?>
