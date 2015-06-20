<?php
date_default_timezone_set('Europe/London');
ini_set('memory_limit','64M');

if(isset($argv[1])){
  $_GET['page'] = $argv[1];
}

/*AUTOLOADER START*/
//used to autoload classes
function myAutoload($name){
    $doNotInclude = false;
    $className = '../classes/'.str_replace('\\', '/', $name).'.php';
    if(file_exists($className)){
    }else{
      header('HTTP/1.0 404 Not Found');
      $doNotInclude = true;
    }
    if($doNotInclude == false){
      require_once $className;
    }   
}
if(function_exists('spl_autoload_register')){
    spl_autoload_register('myAutoload');
}else{
    function __autoload($name){
        myAutoload($name);
    }   
}
/*AUTOLOADER END*/
require_once('../vendor/autoload.php');
$pageName = 'pages\\'.$_GET['page'];
$page = new $pageName();
echo $page->getContent();
