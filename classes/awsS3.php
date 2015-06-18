<?php
use Aws\Common\Aws;

class awsS3{
  static private $aws;
  static private $bucket;
  private $client;
  function __construct(){
    $this->config = new \config();
    $this->loadConfig(); 
  }
  public function loadConfig(){
    // Create the AWS service builder, providing the path to the config file
    self::$aws        = Aws::factory($this->config->projectPath.'config/aws.php');
    self::$bucket     = 'bigprimes';
    $this->client   = self::$aws->get('s3');
  }
  /* $tmpFileName string local filepath
   * $s3FileName string s3 filepath, relative from bucket. Should not start with a slash
   */
  public function saveFileToS3($tmpFileName,$s3FileName=false,$overWriteFile=false){
    //At this point, you can now create clients using the get() method of the Aws object:
    $sha1           = sha1_file($tmpFileName);
    $s3FileName     = $s3FileName?$s3FileName:'uploads/'.$sha1;
    
    if(false == $overWriteFile){
      $fileExistsInS3 = $this->client->doesObjectExist(self::$bucket,$s3FileName);
    }else{
      $fileExistsInS3 = false;
    }

    if($fileExistsInS3){

    }else{
        $command = $this->client->getCommand('PutObject', array(
          'Bucket'       => self::$bucket,
          'Key'          => $s3FileName,
          'SourceFile'   => $tmpFileName
         ));
        $command->getResult();
    }
    return $sha1;
  }

  public function LoadFileFromS3($fileName){
    //TODO: This function could just output the file directly rather than write to tmp first - actually create a fileGetContents() for this

    //At this point, you can now create clients using the get() method of the Aws object:
    $s3FileName       = $fileName;
    $fileTmpNameHash  = sha1($fileName).'_'.(string)time();
    $fileExistsInS3   = $this->client->doesObjectExist(self::$bucket,$s3FileName);
    
    if($fileExistsInS3){
        $command = $this->client->getObject(array(
          'Bucket'       => self::$bucket,
          'Key'          => $s3FileName,
          'SaveAs'       => '/tmp/'.$fileTmpNameHash
         ));
        return '/tmp/'.$fileTmpNameHash;
    }else{
        return false;
    }
  }

  public function deleteFileFromS3($s3FileName){
     $command = $this->client->deleteObject(array(
       'Bucket'       => self::$bucket,
       'Key'          => $s3FileName
     ));
     return true;
  }
  
  public function listDir($prefix){
    //prefix is essentially the directory within the bucket
    $result = $this->client->listObjects(array(
                          'Bucket'=>self::$bucket,
                          'Prefix'=>$prefix
                        ));
    if(!is_array($result['Contents'])){
      return array();
    }
    $return = array();
    $i=0;
    foreach($result['Contents'] as $v){
      if(0===$i){
        $i++;
        //continue;
      }
      $return[] = $v;
    }
    return $return;
  }
}

