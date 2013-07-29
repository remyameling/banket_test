<?php

ini_set('display_errors',1);
ini_set("soap.wsdl_cache_enabled", 0);

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../../application'));

// site specific settings	
require_once($_SERVER['HTTP_HOST'].".inc.php");

// define WEBSITE
if (isset($_GET['website']))
	define('WEBSITE',			$_GET['website']);
else
	define('WEBSITE',			DEFAULT_WEBSITE);
	
// define Zend version
define("ZEND","ZendFramework-1.10.2");

// set include path
set_include_path(LIB.ZEND."/library/");//.get_include_path());



//die(get_include_path());


// Zend_Application 
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);


$application->bootstrap()
            ->run();
            
?>