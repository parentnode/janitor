<?php
$access_item["/"] = true;

if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();

include_once("classes/items/taglist.class.php");
$model = new Taglist();


$page->bodyClass("taglist");
$page->pageTitle("Taglist");


if(is_array($action) && count($action)) {

	// LIST/EDIT/NEW/ADD ITEM
	if(preg_match("/^(list|edit|new|add)$/", $action[0])) {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/taglist/".$action[0].".php"
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
