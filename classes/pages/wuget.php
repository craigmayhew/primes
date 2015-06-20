<?php
namespace pages;
 
class wuget extends \pages{
  public function getContent(){
    header('Content-Type: application/json');
    
    // echo '{ "command": "message", "message": "Greetings from the planet Zib!" }'; exit();
    // echo '{ "command": "sleep" }'; exit();
    // echo '{ "command": "shutdown" }'; exit();
    mysql_connect('bigprimes.ci4p9q2trkqm.eu-west-1.rds.amazonaws.com', 'root', 'Juwv68XnUrXnBGzTfIib');

    // check the clientId that we've got
    $clientId = $_GET['clientId'];
    $q = "SELECT * FROM `bigprimes`.`client` WHERE `id` = '" . mysql_escape_string($clientId) . "';"; // for some reason mysql_real_escape_string is returning empty string?
    $res = mysql_query($q);
    if (mysql_num_rows($res) != 1) {
      return ' { "command": "message", "message": "The client ID you provided is not known. Check your config?" } ';
      exit();
    }

    $q = "SELECT * FROM `bigprimes`.`workUnit` WHERE `timeSent` IS NULL ORDER BY `generated` ASC LIMIT 1";
    $res = mysql_query($q);
    if (mysql_num_rows($res) != 1) {
      return ' { "command": "message", "message": "No work units availiable at the moment. Will retry..." } ';
      exit();
    }
    $unit = mysql_fetch_assoc($res);
    $q = "UPDATE `bigprimes`.`workUnit` SET `timeSent` = UNIX_TIMESTAMP() WHERE `id` = '" . $unit['id'] . "' LIMIT 1";
    mysql_query($q);
    $wu = array(
            'id'        => $unit['id'],
            'start'     => $unit['start'],
            'to'        => $unit['to'],
            'technique' => $unit['technique']
    );

    return json_encode($wu);
  }
}
