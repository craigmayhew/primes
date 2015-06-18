<?php

class config{
  private $environment='live';
  function __construct(){
    $env = getenv('bigprimesenvironment');
    if($env == 'live'){
      $this->environment='live';
    }elseif($env == 'dev'){
      $this->environment='dev';
    }
    $conf = 'config\\'.$this->environment;
    $this->options = new $conf();
  }
  function __get($v){
    return
    $this->options->$v;
  }
}
