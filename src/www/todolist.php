<?php
$access_item["/"] = true;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();
$IC = new Items();
$itemtype = "todolist";
$model = $IC->typeObject($itemtype);


$page->bodyClass($itemtype);
$page->pageTitle("TODO list");


if(is_array($action) && count($action)) {

	// EDIT/NEW ITEM
	if(preg_match("/^(edit|new)$/", $action[0])) {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/".$itemtype."/".$action[0].".php"
		));
		exit();
	}

	// Handle possible API request
	else {
		security()->API_request($model, $action);
	}

}

$page->page(array(
	"templates" => "pages/404.php"
));

?>
