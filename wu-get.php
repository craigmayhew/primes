<?php
// echo '{ "command": "message", "message": "Greetings from the planet Zib!" }'; exit();
// echo '{ "command": "sleep" }'; exit();
// echo '{ "command": "shutdown" }'; exit();
$dbPass = '';
mysql_connect('localhost', 'hp', $dbPass);
// if the work unit count is a bit low, make some more...
$q = "SELECT * FROM `hp`.`workUnit` WHERE `timeSent` IS NULL LIMIT 1";
$res = mysql_query($q);
$unit = mysql_fetch_assoc($res);
$q = "UPDATE `hp`.`workUnit` SET `timeSent` = UNIX_TIMESTAMP() WHERE `id` = '" . $unit['id'] . "' LIMIT 1";
mysql_query($q);
$wu = array(
	'id'        => $unit['id'],
	'start'     => $unit['start'],
	'to'        => $unit['to'],
	'technique' => $unit['technique']
);
header('Content-Type: application/json');
echo json_encode($wu);
