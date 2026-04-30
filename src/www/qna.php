<?php
//$access_item = false;
$access_item["/"] = true;
$access_item["/owner"] = true;
$access_item["/updateOwner"] = "/owner";
$access_item["/edit"] = true;
$access_item["/update"] = "/edit";
$access_item["/new"] = true;
$access_item["/save"] = "/new";
$access_item["/addTag"] = "/new";
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();
$IC = new Items();
$itemtype = "qna";
$model = $IC->typeObject($itemtype);


$page->bodyClass($itemtype);
$page->pageTitle("Questions and Answers");


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
