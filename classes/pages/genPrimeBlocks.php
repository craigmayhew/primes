<?php
namespace pages;
 
class genPrimeBlocks extends \pages{
  private $blockSize = 1000;//number of primes per block;
  public function getContent(){
    //connect to mysql
    $conn = mysql_connect(getenv('bigprimesDBEndPoint'), getenv('bigprimesDBUser'), getenv('bigprimesDBPass'));
    mysql_select_db('bigprimes');

    //pull blockSize primes from the queue
    $q = 'SELECT `prime` FROM `prime_q` WHERE `prime`!=1 ORDER BY `id` ASC LIMIT '.$this->blockSize;
    $res = mysql_query($q);
    //if we don't find blockSize then we can't make a full block, so fail gracefully
    if(mysql_num_rows($res) != $this->blockSize){
       echo 'breaking loop.'.PHP_EOL;
       exit();
    }

    //populate the halfDiffs array
    $prev = null;
    $halfDiffs = array();
    while($p = mysql_fetch_assoc($res)){
      if($prev){
        $halfDiffs[] = bcdiv(bcsub($p['prime'],$prev),2);
      }else{
        $firstPrimeInBlock = $p['prime'];
      }

      $prev = $p['prime'];
    }

    $largestHalfDiff = max($halfDiffs);

    //work out how many bits will be required to store the largest halfdiff
    $bits=2;
    echo "ld: $largestHalfDiff\r\n";
    while (true){
      //if we finally have enough bit as $bits^2 to store the largest halfDiff
      if ((pow(2, $bits)) > $largestHalfDiff){
        echo "Choosing $bits bits\r\n";
        break;
      }
      echo "Bits: $bits     " . (pow(2, $bits)) . "    $largestHalfDiff\r\n";
      $bits++;
    }
  
    $temp = '';
    foreach ($halfDiffs as $halfDiff) {
      // make all of the binary values be the same length so that we can extract them when we read the block
      $temp .= str_pad(decbin($halfDiff), $bits, '0', STR_PAD_LEFT);
    }

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
              'n'=>$firstPrimeInBlock,
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
    echo "Have a look in $s3FileName, its very exciting!";
    exit();
  }
}
