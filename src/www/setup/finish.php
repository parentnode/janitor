<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once("../../config/setup/init.php");

$action = $page->actions();


$page->bodyClass("finish");
$page->pageTitle("Janitor setup guide");


$page->header(array("type" => "setup"));
$page->template("setup/finish.php");
$page->footer(array("type" => "setup"));
exit();

?>