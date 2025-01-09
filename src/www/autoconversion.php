<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


include_once("classes/system/autoconversion.class.php");
$AC = new AutoConversion();



$AC->parseRequest();

$AC->processRequest();


?>
