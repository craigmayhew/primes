<?php
date_default_timezone_set('Europe/London');
ini_set('memory_limit','64M');

/*AUTOLOADER START*/
//used to autoload classes
function myAutoload($name){
    $doNotInclude = false;
    if(file_exists($className = 'classes/'.str_replace('\\', '/', $name).'.php')){
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

require_once('vendor/autoload.php');

$pageName = $_GET['page'];
$page = new $pageName();
echo $page->getContent();
