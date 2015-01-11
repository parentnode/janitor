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

	// Class interface
	else if($page->validateCsrfToken() && preg_match("/[a-zA-Z]+/", $action[0])) {

		// check if custom function exists on User class
		if($model && method_exists($model, $action[0])) {

			$output = new Output();
			$output->screen($model->$action[0]($action));
			exit();
		}
	}

}

$page->page(array(
	"templates" => "pages/404.php"
));

?>
