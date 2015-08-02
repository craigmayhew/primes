<?php
namespace pages;
 
class genPrimeBlocks extends \pages{
  private $blockSize = 1001;//number of primes per block;
  public function getContent(){
    $conn = mysql_connect(getenv('bigprimesDBEndPoint'), getenv('bigprimesDBUser'), getenv('bigprimesDBPass'));
    mysql_select_db('bigprimes');

    $q = 'SELECT `prime` FROM `prime_q` WHERE 1 AND `prime`!=1 ORDER BY `id` ASC LIMIT '.$this->blockSize;
    $res = mysql_query($q);
    if(mysql_num_rows($res) != $this->blockSize){
       echo 'breaking loop.'.PHP_EOL;
       exit();
    }

    $first = true;
    $prev = null;
    $halfDiffs = array();
    while($p = mysql_fetch_assoc($res)){
      if(true === $first){
        $n = $p['prime'];
        $first = false;
      }
      if($prev){
        $halfDiffs[] = bcdiv(bcsub($p['prime'],$prev),2);
      }

      $prev = $p['prime'];
    }

    $largestDiff = max($halfDiffs);

    $bits=2;
    echo "ld: $largestDiff\r\n";
    while (true){
      if ((pow(2, $bits)) > $largestDiff){
        echo "Choosing $bits bits\r\n";
        break;
      }
      echo "Bits: $bits     " . (pow(2, $bits)) . "    $largestDiff\r\n";
      $bits++;
    }
  
    $temp = '';
    foreach ($halfDiffs as $halfDiff) {
      $temp .= decbin($halfDiff);
    }
    echo $temp . "\r\n";
    $padding = 8 - (($bits * count($halfDiffs)) % 8);
    if ($padding == 8) $padding = 0;
    echo "Padding: $padding    Bits: $bits     count(): " . count($halfDiffs) . "\r\n";
    $temp .= str_repeat('0', $padding);
    echo $temp;
    $binary = '';
    do {
      $bits = substr($temp, 0, 8);
      $temp = substr($temp, 8);
      $binary .= chr(bindec($bits));
    } while ($temp != '');
    echo $binary . "\r\n";

    $data = json_encode(array(
              'n'=>$n,
              'nth'=>$nth,
              'blockSize'=>$this->blockSize,
              'diffInBits'=>$bits
    ));
    
    //aws library
    $aws = new \awsS3();

    //write files to aws
    $uuid = \utils::generate_uuid();
    $s3FileName = 'ALLTHEPRIMES-'.$uuid;
    $aws->saveDataToS3($data,$s3FileName);
    $aws->saveDataToS3($binary,$s3FileName.'.bin');
  }
}
