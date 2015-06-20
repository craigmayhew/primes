<?php
namespace pages;

class wuresult extends \pages{
  public function getContent(){
    $postedJSON = file_get_contents("php://input");

    //json decode so we can access the id for the filename
    $posted = json_decode($postedJSON);

    //aws library
    $aws = new \awsS3();

    //write file to s3
    $return   = $aws->saveDataToS3($postedJSON,$posted->id);

    return 'success';
  }
}
