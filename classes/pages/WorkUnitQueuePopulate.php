<?php
namespace pages;

class WorkUnitQueuePopulate extends \pages {
  function __construct(){
    mysql_connect(getenv('bigprimesDBEndPoint'), getenv('bigprimesDBUser'), getenv('bigprimesDBPass'));

    do {
      // if the work unit count is a bit low, make some more...
      $q = "SELECT COUNT(*) FROM `bigprimes`.`workUnit` WHERE `timeSent` IS NULL";
      $res = mysql_query($q);
      $count =  mysql_result($res, 0);
      echo "$count units in buffer\r\n";
      if ($count < 20) {
        echo "Generating some more\r\n";
        // generate 1000 units
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
                $size = rand(100000, 100000);
                $to   = bcadd($start, bcadd($size, "-1"));
                $q = "INSERT INTO `bigprimes`.`workUnit` (`id`, `generated`, `start`, `to`, `technique`, `size`) VALUES ('" . self::gen_uuid() . "', '" . time() . "', '" . $start . "', '" . $to . "', 'bf-int', '$size');";
                mysql_query($q);
                $start = bcadd($to, 1);
        }
        $q = "UPDATE `bigprimes`.`global` SET `value` = '" . bcadd($start, "-1") . "' WHERE `key` = 'maxWorkunit';";
        mysql_query($q);
      }
      sleep(5);
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
