<?php
namespace pages;

class wuresult extends \pages{
  public function getContent(){
    $postedJSON = file_get_contents("php://input");

    //json decode so we can access the id for the filename
    $posted = json_decode($postedJSON);

    //aws library
    $aws = new \awsS3();

    //create a uniquely named temp file
    $filename = '/tmp/'.sha1(rand().time().$posted->id);

    //save json to disk
    file_put_contents($filename,$postedJSON);

    //write file to s3
    $return   = $aws->saveFileToS3($filename,$posted->id);
    
    //delete temporary file
    unlink($filename);

    return '';
  }
}
