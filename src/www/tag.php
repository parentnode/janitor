<?php
$access_item["/list/"] = true;
$access_item["/edit/"] = true;
$access_item["/updateTag/"] = true;
$access_item["/deleteTag/"] = true;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();
$model = new Tag();


$page->bodyClass("tags");
$page->pageTitle("Tags management");


if(is_array($action) && count($action)) {

	// LIST ITEM
	if(count($action) == 1 && $action[0] == "list") {

		$page->page(array(
			"type" => "admin",
			"templates" => "admin/tag/list.php"
			)
		);
		exit();

	}
	// EDIT ITEM
	else if(count($action) == 2 && $action[0] == "edit") {

		$page->page(array(
			"type" => "admin",
			"templates" => "admin/tag/edit.php"
			)
		);
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
	)
);

?>
