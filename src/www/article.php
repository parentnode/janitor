<?php
$access_item["/"] = true;
$access_item["/owner"] = true;
$access_item["/updateOwner"] = "/owner";
$access_item["/comments"] = true;
$access_item["/addComment"] = "/comments";
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();
$IC = new Items();
$itemtype = "article";
$model = $IC->typeObject($itemtype);


$page->bodyClass($itemtype);
$page->pageTitle("Articles");


if(is_array($action) && count($action)) {

	// LIST/EDIT/NEW ITEM
	if(preg_match("/^(list|edit|new)$/", $action[0])) {

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
