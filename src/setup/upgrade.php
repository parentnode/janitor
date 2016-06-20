<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");
include_once("classes/system/upgrade.class.php");


$action = $page->actions();


$model = new Upgrade();

$page->bodyClass("upgrade");
$page->pageTitle("Janitor Upgrade");


if(is_array($action) && count($action)) {

	// LIST/EDIT/NEW ITEM
	if(preg_match("/^([a-z\-]+)$/", $action[0])) {
		$page->page(array(
			"type" => "setup",
			"templates" => "upgrade/".$action[0].".php"
		));
		exit();
	}

}

$page->page(array(
	"type" => "setup",
	"templates" => "pages/404.php"
));

?>