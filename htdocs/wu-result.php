<?php
date_default_timezone_set('Europe/London');
ini_set('memory_limit','64M');
require_once('autoLoader.php');
require '../vendor/autoload.php';


$postedJSON = file_get_contents("php://input");


