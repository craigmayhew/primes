<?php
namespace pages;
 
class genPrimeQ extends \pages{
  public function getContent(){
    $conn = mysql_connect(getenv('bigprimesDBEndPoint'), getenv('bigprimesDBUser'), getenv('bigprimesDBPass'));
    mysql_select_db('bigprimes');

    //aws library
    $aws = new \awsS3();
    
    //this is going to need to load from a known save point
    $q=mysql_fetch_array(mysql_query('SELECT `value` FROM `global` WHERE `key`="q_start"'));
    $start = $q[0];
    echo $start;
    
    //then we need to read data from the workunits
    $wus = array();
    while (true){
      $q = "
        SELECT 
          wu.wu_id,
          wu.`start`,
          wu.`to`,
          wur.s3location AS s3locationFirst,
          wurv.s3location AS s3locationValidation 
        FROM wu
        LEFT JOIN wu_result wur ON
          wur.wu_id=wu.wu_id
          AND wur.validation=0
        LEFT JOIN wu_result wurv ON
          wurv.wu_id=wu.wu_id
          AND wurv.validation=1
        WHERE 
          state='Needs Processing' 
          AND start='$start'
        LIMIT 1";
      $res = mysql_query($q);
      if(mysql_num_rows($res) != 1){
        echo 'breaking loop.'.PHP_EOL;
        break;
      }

      $wu = mysql_fetch_assoc($res);

      $wus[] = $wu;
        
      //echo 'Retrieved wu to '.$start.PHP_EOL;
      echo $wu['wu_id'] . " from " . $wu['start'] . " to " . $wu['to'] . " ";
      $start = bcadd($wu['to'],1);

      //retrieve the work unit result files
      //TODO:change these functions to load data directly without writing temp files
      $firstFile      = $aws->LoadFileFromS3($wu['s3locationFirst']);
      $validationFile = $aws->LoadFileFromS3($wu['s3locationValidation']);

      //read data from those files
      $first = json_decode(file_get_contents($firstFile),true);
      $valid = json_decode(file_get_contents($validationFile),true);

      unlink($firstFile);
      unlink($validationFile);

      //sanity checks
      if($first['work'] != $valid['work']){
        die('work: '.$first['work'].' != '.$valid['work']);
      }

      if($issue = array_diff($valid['primes'],$first['primes'])){
        die('missing number: '.print_r($issue,1));
      }

      if($issue = array_diff($first['primes'],$valid['primes'])){
        die('missing number: '.print_r($issue,1));
      }

      $primes = $first['primes'];

      //ordered even if some of the keys are strings!
      sort($primes, SORT_NATURAL);
      echo count($primes) . ' ';

      foreach($primes as &$p){
        $p = mysql_real_escape_string($p);
      }

      $i = 0;
      $insertVal = array();
      $count = count($primes);
      foreach($primes as $i => $prime) {
        $insertVal[] = $prime;
        if (($i >= 1 && $i % 1000 == 0) || $i == $count - 1) {
          $query = 'INSERT INTO prime_q (`prime`) VALUES ("'.implode('"),("',$insertVal).'")';
          mysql_query($query);
          echo '.';
          $insertVal = array();
        }
      }
      echo "\r\n";

      mysql_query($q = 'INSERT INTO `global` (`key`,`value`) VALUES ("q_start","'.$start.'") ON DUPLICATE KEY UPDATE `value`="'.$start.'"');
    }


    //that data needs to be parsed into binary nk +x
    
    
    //and saved to s3
    //$return   = $aws->saveDataToS3($data,$fileId);
    
    //and we know whet to stop if all work units have been processed

/*
      //write file to s3
      $s3Path = $posted['id'] . '-first.json';
      $return   = $aws->saveDataToS3($postedJSON, $s3Path);
*/  
  }
}
