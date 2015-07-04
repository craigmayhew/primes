<?php
namespace pages;

class wuresult extends \pages{
  public function getContent(){
    mysql_connect(getenv('bigprimesDBEndPoint'), getenv('bigprimesDBUser'), getenv('bigprimesDBPass'));

    $postedJSON = file_get_contents("php://input");

    //json decode so we can access the id for the filename
    $posted = json_decode($postedJSON, true);

    // so the result is either a return of a new unit, or the return of a validation
    // firstly, is this the first time the result has come back? And is the client claiming to have done the work the one that it was actually sent to?

    $query = 'SELECT * FROM `bigprimes`.`wu` 
              WHERE `wu_id` = "' . mysql_escape_string($posted['id']) . '"
              AND `state` = "Crunching"
              AND `sent_to_client` = "' . mysql_escape_string($posted['clientId']) . '"
              AND `time_sent` > UNIX_TIMESTAMP() - 1800
              LIMIT 1;';
    $res = mysql_query($query);
    

    if (mysql_num_rows($res) == 1) {
      //aws library
      $aws = new \awsS3();
      //write file to s3
      $s3Path = $posted['id'] . '-first.json';
      $return   = $aws->saveDataToS3($postedJSON, $s3Path);
      // update DB
      $q = 'UPDATE `bigprimes`.`wu` SET
        `state` = "Needs Validating",
        `time_received` = UNIX_TIMESTAMP()
        WHERE `wu_id` = "' . mysql_escape_string($posted['id']) . '"
        LIMIT 1';
      mysql_query($q);

      $q = 
        'INSERT INTO `bigprimes`.`wu_result` '.
        '(`client_id`, `wu_id`, `time_received`, `time_taken_ms`, `work_done`, `s3location`,`validation`) '.
        'VALUES ('.
          '"' . mysql_escape_string($posted['clientId']) . '",'. 
          '"' . mysql_escape_string($posted['id'])       . '",'.
          'UNIX_TIMESTAMP(),'.
          '"'.mysql_escape_string($posted['timeTakenMilliseconds']).'",'.
          '"'.mysql_escape_string($posted['work']).'",'.
          '"'.mysql_escape_string($s3Path).'",'.
          'false'
        ')';
       
      mysql_query($q);

      //job jobbed - we only need a 200 response
      exit();
    }

    $query = 'SELECT * FROM `bigprimes`.`wu` 
              WHERE `wu_id` = "' . mysql_escape_string($posted['id']) . '"
              AND `state` = "Validating"
              AND `validation_client` = "' . mysql_escape_string($posted['clientId']) . '"
              AND `time_sent_validation` > UNIX_TIMESTAMP() - 1800
              LIMIT 1;';
    $res = mysql_query($query);

    if (mysql_num_rows($res) == 1) {
      //aws library
      $aws = new \awsS3();
      //write file to s3
      $s3Path = $posted['id'] . '-validation.json';
      $return   = $aws->saveDataToS3($postedJSON, $s3Path);
      // update DB
      $q = 'UPDATE `bigprimes`.`wu` SET
        `state` = "Needs Processing",
        `time_received_validation` = UNIX_TIMESTAMP()
        WHERE `wu_id` = "' . mysql_escape_string($posted['id']) . '"
        LIMIT 1';
      mysql_query($q);

      $q = 
        'INSERT INTO `bigprimes`.`wu_result` '.
        '(`client_id`, `wu_id`, `time_received`, `time_taken_ms`, `work_done`, `s3location`,`validation`) '.
        'VALUES ('.
          '"' . mysql_escape_string($posted['clientId']) . '",'. 
          '"' . mysql_escape_string($posted['id'])       . '",'.
          'UNIX_TIMESTAMP(),'.
          '"'.mysql_escape_string($posted['timeTakenMilliseconds']).'",'.
          '"'.mysql_escape_string($posted['work']).'",'.
          '"'.mysql_escape_string($s3Path).'",'.
          'true'
        ')';
       
      mysql_query($q);

      //job jobbed - we only need a 200 response
      exit();
    }

    //something went terribly wrong
    exit();
  }
}
