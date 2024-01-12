<?php
require_once('config.php');
require_once('functions.php');

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


$fp = fopen('/var/log/webhook_request.log', 'a');

// fwrite($fp, "\n".print_r($json, TRUE));

switch ($json['eventName']) {
    case 'client.add':
        fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
		include('client.add.php');
        break;
    case 'client.archive':
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
        include('client.archive.php');
        break;
    case 'client.delete':
        fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
        include('client.delete.php');
        break;
    case 'client.edit':
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
        include('client.edit.php');
        break;
    case 'client.invite':
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
        // include('client.invite.php');
        break;
	case 'credit_card.add':
		break;
	case 'credit_card.delete':
		break;
	case 'credit_card.edit':
		break;
    case 'invoice.add':
		// include('invoice.add.php');
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." - ".$json['extraData']['entity']['clientId']." - ".$json['extraData']['entity']['number']." ID: ".$json['extraData']['entity']['id']);
		// fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']."\n".print_r($json, TRUE));
        break;
    case 'invoice.add_draft':
		// include('invoice.add.php');
		// fwrite($fp, "\n--setup_intent.created - No Action");
        break;
    case 'invoice.edit':
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
		include('invoice.edit.php');
		// fwrite($fp, "\n--setup_intent.created - No Action");
        break;
    case 'invoice.draft_approved':
		// include('invoice.add.php');
		// fwrite($fp, "\n--setup_intent.created - No Action");
        break;
    case 'invoice.delete':
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
		include('invoice.delete.php');
		// include('invoice.add.php');
		// fwrite($fp, "\n--setup_intent.created - No Action");
        break;
    case 'invoice.near_due':
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
		include('invoice.near_due.php');
		// fwrite($fp, "\n--setup_intent.created - No Action");
        break;
    case 'invoice.overdue':
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
		include('invoice.overdue.php');
		// fwrite($fp, "\n--setup_intent.created - No Action");
        break;
    case 'payment.add':
		// include('invoice.delete.php');
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." - Client: ".$json['extraData']['entity']['clientId']." ID: ".$json['extraData']['entity']['id']);
		include('payment.add.php');
		
        break;
    case 'payment.edit':
		// include('invoice.delete.php');
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
		include('payment.edit.php');
		// fwrite($fp, "\n--setup_intent.created - No Action");
		
        break;

    case 'payment.unmatch':
		// include('invoice.delete.php');
		// include('invoice.add.php');
		// fwrite($fp, "\n--setup_intent.created - No Action");
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
        break;
	case 'payment_method.attached':
		// include('payment_method.attached.php');
        break;
    case 'service.add':
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." - ".$json['extraData']['entity']['clientId']." ID: ".$json['extraData']['entity']['id']);
		// fwrite($fp, "\n--webhook.php\n".$_SERVER['REMOTE_ADDR'].":\n".print_r($json, TRUE));
		// include('service.add.php');
        break;
    case 'service.archive':
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
		include('service.archive.php');
        break;
    case 'service.delete':
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
		// include('payment_method.detached.php');
        break;
    case 'service.edit':
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
		// fwrite($fp, "\n--webhook.php\n".$_SERVER['REMOTE_ADDR'].":\n".print_r($json, TRUE));
		include('service.edit.php');
        break;
    case 'service.end': 
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
		// fwrite($fp, "\n--webhook.php\n".$_SERVER['REMOTE_ADDR'].":\n".print_r($json, TRUE));
		include('service.end.php');
        break;
    case 'service.postpone': 
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
		include('service.postpone.php');
        break;
    case 'service.suspend':
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
		// fwrite($fp, "\n--webhook.php\n".$_SERVER['REMOTE_ADDR'].":\n".print_r($json, TRUE));
		include('service.suspend.php');
        break;
    case 'service.suspend_cancel':
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
		include('service.suspend_cancel.php');
        break;
    
	case 'test':
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
		// include('payment_method.detached.php');
        break;
    case 'ticket.add':
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
		include('ticket.add.php');
        break;
    case 'ticket.comment':
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
		include('ticket.comment.php');
        break;
    case 'ticket.delete':
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
		// include('payment_method.detached.php');
        break;
    
	case 'ticket.edit':
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
		include('ticket.edit.php');
        break;
    
	case 'ticket.status_change':
		fwrite($fp, "\n".date("Y-m-d H:i:s")." - Webhook: ".$json['eventName']." ID: ".$json['extraData']['entity']['id']);
		include('ticket.status_change.php');
        break;
    
	default:
		fwrite($fp, "\n--Unknown Webhook");
		fwrite($fp, "\n--webhook.php\n".$_SERVER['REMOTE_ADDR'].":\n".print_r($json, TRUE));
}

fclose($fp);
?>
