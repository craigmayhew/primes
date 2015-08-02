<?php
namespace pages;
 
class clientwork24 extends \pages{
  public function getContent(){
    header('Content-Type: application/json');
    
    mysql_connect(getenv('bigprimesDBEndPoint'), getenv('bigprimesDBUser'), getenv('bigprimesDBPass'));

    $query =
      'SELECT 
      count(*) as WorkUnits, 
      sum(`size`) as \'Numbers factored\', 
      sum(time_taken_ms)/86400000 as \'CPU Days\', 
      sum(work_done) / 1000000000000 as \'TDivs\', 
      sum(work_done)/(sum(time_taken_ms)/1000)/1000000 as \'MDivs/Sec\', 
      user.name as User, client.name as Client 
      FROM bigprimes.wu_result 
      left join bigprimes.client on client.client_id = wu_result.client_id 
      left join bigprimes.user on client.user = user.user_id 
      left join bigprimes.`wu` on `wu`.`wu_id` = `wu_result`.`wu_id` 
      where `wu_result`.`time_received` > UNIX_TIMESTAMP() - (86400 * 1) 
      group by client.client_id
      order by TDivs desc;';

    $return = array();
    $res = mysql_query($query);
    while($row = mysql_fetch_assoc($res)){
      $return[] = $row;
    }

    return json_encode($return);
  }
}
