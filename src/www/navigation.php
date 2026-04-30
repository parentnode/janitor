<?php
$access_item["/"] = true;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();
$model = new Navigation();


$page->bodyClass("navigation");
$page->pageTitle("Navigation");


if(is_array($action) && count($action)) {

	// LIST/EDIT/NEW/EDIT_NODE/NEW_NODE
	if(preg_match("/^(list|edit|new|edit_node|new_node)$/", $action[0])) {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/navigation/".$action[0].".php"
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
