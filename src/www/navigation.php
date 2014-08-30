<?php
$access_item = false;
//$access_item["/list/"] = true;

if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();
$model = new Navigation();


$page->bodyClass("navigation");
$page->pageTitle("Navigation");


if(is_array($action) && count($action)) {

	// LIST ITEM
	if(count($action) >= 1 && $action[0] == "list") {

		$page->header(array("type" => "admin"));
		$page->template("admin/navigation/list.php");
		$page->footer(array("type" => "admin"));
		exit();

	}
	// NEW ITEM
	else if(count($action) == 1 && $action[0] == "new") {

		$page->header(array("type" => "admin"));
		$page->template("admin/navigation/new.php");
		$page->footer(array("type" => "admin"));
		exit();

	}
	// EDIT ITEM
	else if(count($action) == 2 && $action[0] == "edit") {
	
		$page->header(array("type" => "admin"));
		$page->template("admin/navigation/edit.php");
		$page->footer(array("type" => "admin"));
		exit();
	
	}

	// ADD NAVIGATION NODE
	else if(count($action) == 2 && $action[0] == "new_node") {
	
		$page->header(array("type" => "admin"));
		$page->template("admin/navigation/new_node.php");
		$page->footer(array("type" => "admin"));
		exit();
	
	}
	// EDIT NAVIGATION NODE
	else if(count($action) == 2 && $action[0] == "edit_node") {

		$page->header(array("type" => "admin"));
		$page->template("admin/navigation/edit_node.php");
		$page->footer(array("type" => "admin"));
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

$page->header();
$page->template("404.php");
$page->footer();

?>
