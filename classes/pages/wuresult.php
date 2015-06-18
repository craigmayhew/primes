<?php
namespace pages;

class wuresult extends \pages{
  public function getContent(){
    $postedJSON = file_get_contents("php://input");

    return '';
  }
}
