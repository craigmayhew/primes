<?php
namespace pages;
 
class wuget extends \pages{
  public function getContent(){
    header('Content-Type: application/json');
    
    // echo '{ "command": "message", "message": "Nooooooo what are you doing?!" }'; exit();
    // echo '{ "command": "sleep" }'; exit();
    // echo '{ "command": "shutdown" }'; exit();
    mysql_connect('bigprimes.ci4p9q2trkqm.eu-west-1.rds.amazonaws.com', 'root', 'Juwv68XnUrXnBGzTfIib');

    // check the clientId that we've got
    $clientId = $_GET['clientId'];
    $q = "SELECT * FROM `bigprimes`.`client` WHERE `client_id` = '" . mysql_escape_string($clientId) . "';"; // for some reason mysql_real_escape_string is returning empty string?
    $res = mysql_query($q);
    if (mysql_num_rows($res) != 1) {
      return ' { "command": "message", "message": "The client ID you provided is not known. Check your config?" } ';
      exit();
    }


    $q = "SELECT * FROM `bigprimes`.`wu` WHERE `state` = 'Needs Validating' AND `sent_to_client` != '" . mysql_escape_string($clientId) . "' ORDER BY `generated` ASC LIMIT 1";
    $res = mysql_query($q);
    if (mysql_num_rows($res) == 1) {
      $unit = mysql_fetch_assoc($res);
      $q = "UPDATE `bigprimes`.`wu` SET `time_sent_validation` = UNIX_TIMESTAMP(), `state` = 'Validating', `validation_client` = '" . mysql_escape_string($clientId) . "' WHERE `wu_id` = '" . $unit['wu_id'] . "' LIMIT 1";
      $res = mysql_query($q);
      $wu = array(
        'id'        => $unit['wu_id'],
        'start'     => $unit['start'],
        'to'        => $unit['to'],
        'technique' => $unit['technique'],
        'message'   => "Validating WU " . $unit['wu_id'] . " done by " . $unit['sent_to_client']
      );
      return json_encode($wu);
    } else {
      $q = "SELECT * FROM `bigprimes`.`wu` WHERE `state` = 'New' ORDER BY `generated` ASC LIMIT 1";
      $res = mysql_query($q);
      if (mysql_num_rows($res) != 1) {
        return ' { "command": "message", "message": "No work units availiable at the moment. Will retry..." } ';
      }
      $unit = mysql_fetch_assoc($res);
      $q = "UPDATE `bigprimes`.`wu` SET `time_sent` = UNIX_TIMESTAMP(), `state` = 'Crunching', `sent_to_client` = '" . mysql_escape_string($clientId) . "' WHERE `wu_id` = '" . $unit['wu_id'] . "' LIMIT 1";
      mysql_query($q);
      $wu = array(
            'id'        => $unit['wu_id'],
            'start'     => $unit['start'],
            'to'        => $unit['to'],
            'technique' => $unit['technique'],
            'message'   => "First run of WU " . $unit['wu_id']
      );

      return json_encode($wu);
    }
  }
}
