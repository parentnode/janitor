<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once("defaults/init.php");


$action = $page->actions();

$model = new Setup();

$model->reset();

$page->bodyClass("check");
$page->pageTitle("Janitor setup guide");


$page->page(array(
	"type" => "setup",
	"templates" => "setup/check.php"
));
exit();

?>