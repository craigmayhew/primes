<?php
date_default_timezone_set('Europe/London');
ini_set('memory_limit','256M');

if(isset($argv[1])){
  $_GET['page'] = $argv[1];
}

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
require_once('../vendor/autoload.php');
/*AUTOLOADER END*/
<<<<<<< HEAD:html/index.php

=======
require_once('../vendor/autoload.php');
>>>>>>> c707c823d2cb339795add95263d7ba8e779c837d:html/index.php
$pageName = 'pages\\'.$_GET['page'];
$page = new $pageName();
echo $page->getContent();
