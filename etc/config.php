<?
//Config Bootstrap

// Mostly set things up automatically by detecting 
// our dir location
$config = new StdClass(); 
$config->etc = dirname(__FILE__); 
$config->debug = FALSE;
$config->loopDelay = 2;

// Override DB in server config. 
$config->database = new StdClass();
$config->database->enable=FALSE;
$config->database->user="";
$config->database->password="";
$config->database->host="";
$config->database->database="";

//Load server config 
include($config->etc . "/server.php");  // This can be a symlink to have

?>
