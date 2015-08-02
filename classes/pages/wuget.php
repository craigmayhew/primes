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
    $res    = mysql_query($q);
    if (mysql_num_rows($res) != 1) {
      return ' { "command": "message", "message": "The client ID you provided is not known. Check your config?" } ';
      exit();
    }

    $client = mysql_fetch_assoc($res);

    $q = 
      "SELECT 
        wu.wu_id AS wu_id,
        wu.sent_to_client AS sent_to_client,
        wu.`start` AS `start`,
        wu.`to` AS `to`,
        wu.`size` AS `size`,
        wu.`technique` AS `technique`,
        `user`.`name` AS `name`
      FROM `bigprimes`.`wu` 
      LEFT JOIN `bigprimes`.`client` ON client.client_id=wu.sent_to_client
      LEFT JOIN `bigprimes`.`user` ON user.user_id=client.user
      WHERE 
        `state` = 'Needs Validating' 
        AND `sent_to_client` != '" . mysql_escape_string($clientId) . "' 
        AND sent_to_client NOT IN(SELECT c2.client_id FROM `bigprimes`.`client` AS c2 WHERE c2.user='".mysql_escape_string($client['user'])."')
      ORDER BY `generated` ASC
      LIMIT 1";
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
        'message'   => "Validating WU " . $unit['wu_id'] . " done by " . $unit['name'] . ". " . $unit['start'] . " - " . $unit['to'] . " (" . $unit['size'] . ", " . $unit['technique'] . ")."
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
            'message'   => "First run of WU " . $unit['wu_id'] . ". " . $unit['start'] . " - " . $unit['to'] . " (" . $unit['size'] . ", " . $unit['technique'] . ")."
      );

      return json_encode($wu);
    }
  }
}
