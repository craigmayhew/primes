<?php
namespace pages;

class WorkUnitQueuePopulate extends \pages {
  function __construct(){
    mysql_connect(getenv('bigprimesDBEndPoint'), getenv('bigprimesDBUser'), getenv('bigprimesDBPass'));

    do {
      // reset work units that aren't coming back
      $q = "UPDATE `bigprimes`.`wu` SET `state` = 'New', `time_sent` = Null, `sent_to_client` = Null WHERE `state` = 'Crunching' AND `time_sent` < UNIX_TIMESTAMP() - 3600";
      mysql_query($q);
      $q = "UPDATE `bigprimes`.`wu` SET `state` = 'Needs Validating', `time_sent_validation` = Null, `validation_client` = Null WHERE `state` = 'Validating' AND `time_sent_validation` < UNIX_TIMESTAMP() - 3600";
      mysql_query($q);
      // if the work unit count is a bit low, make some more...
      $q = "SELECT COUNT(*) FROM `bigprimes`.`wu` WHERE `state` = 'New'";
      $res = mysql_query($q);
      $count =  mysql_result($res, 0);
      echo "$count units in buffer\r\n";
      if ($count < 50) {
        echo "Generating some more";
        // generate 50 units
        // where to start?
        $q = "SELECT `value` FROM `bigprimes`.`global` WHERE `key` = 'maxWorkunit';";
        $res = mysql_query($q);
        $res = mysql_result($res, 0);
        if ($res === null) {
                echo "I don't know where to start from\r\n";
                exit();
        }
        $start = bcadd($res, '1');
        for ($i = 0; $i < 50; $i++) {
                $size = 1500000; // rand(600000, 1500000);
                $to   = bcadd($start, bcadd($size, "-1"));
                $q = "INSERT INTO `bigprimes`.`wu` (`wu_id`, `generated`, `start`, `to`, `technique`, `size`, `state`) VALUES ('" . self::gen_uuid() . "', '" . time() . "', '" . $start . "', '" . $to . "', 'bf', '$size', 'New');";
                mysql_query($q);
                echo '.';
                $start = bcadd($to, 1);
        }
        echo "\r\n";
        $q = "UPDATE `bigprimes`.`global` SET `value` = '" . bcadd($start, "-1") . "' WHERE `key` = 'maxWorkunit';";
        mysql_query($q);
        echo "Generated units up to " . bcadd($start, "-1") . "\r\n";
      }
      sleep(15);
    } while (true);
  }

  function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      // 32 bits for "time_low"
      mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
      // 16 bits for "time_mid"
      mt_rand( 0, 0xffff ),
      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 4
      mt_rand( 0, 0x0fff ) | 0x4000,
      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      mt_rand( 0, 0x3fff ) | 0x8000,
      // 48 bits for "node"
      mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
  }
}
