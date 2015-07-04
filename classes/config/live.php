<?php

namespace config;

class live{
  public $projectPath = '';

  function __construct(){
    $this->projectPath = __DIR__.'/../../';
  }
}
