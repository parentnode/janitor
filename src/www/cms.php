<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


include_once("class/system/cms.class.php");

$CMS = new CMS();
$CMS->processRequest();


// Fallback if CMS controller does not find matching request
$page->header();
$page->template("404.php");
$page->footer();

?>
