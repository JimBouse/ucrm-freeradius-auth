<?php
require_once('config.php');

$link = mysqli_connect($db_host, $db_user, $db_pass, $db);
if (!$link) {
    die('Could not connect: ' . mysql_error());
}

if (isset($_GET['mac'])) {
	$sql = "SELECT * FROM queue_name WHERE mac = '".mysqli_real_escape_string($link, $_GET['mac'])."' LIMIT 1";
	$result = mysqli_query($link,$sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
	while ($row = mysqli_fetch_assoc($result)) {
		print json_encode($row);
	}
}
?>
