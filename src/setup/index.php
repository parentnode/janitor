<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once("defaults/init.php");


$action = $page->actions();

$model = new Setup();

$model->reset();

$page->bodyClass("front");
$page->pageTitle("Janitor setup guide");


$page->page(array(
	"type" => "setup",
	"templates" => "setup/index.php"
));
exit();

?>