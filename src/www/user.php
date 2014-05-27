<?php
$access_item["/"] = true;

if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");

// include the output class for output method support
include_once("class/system/output.class.php");

$action = $page->actions();

$model = new User();
$output = new Output();

// Add to cart handled


$page->bodyClass("user");
$page->pageTitle("User management");


if(is_array($action) && count($action)) {


	if(preg_match("/[a-zA-Z]+/", $action[0])) {

		// check if custom function exists on User class
		if($model && method_exists($model, $action[0])) {

			$output->screen($model->$action[0]($action));
			exit();
		}
	}

	// LIST ITEM
	// Requires exactly two parameters /enable/#item_id#
	if(count($action) >= 1 && $action[0] == "list") {

		$page->header(array("type" => "admin"));
		$page->template("admin/user/list.php");
		$page->footer(array("type" => "admin"));
		exit();

	}
	// NEW ITEM
	else if(count($action) == 1 && $action[0] == "new") {

		$page->header(array("type" => "admin"));
		$page->template("admin/user/new.php");
		$page->footer(array("type" => "admin"));
		exit();

	}
	// EDIT ITEM
	else if(count($action) == 2 && $action[0] == "edit") {
	
		$page->header(array("type" => "admin"));
		$page->template("admin/user/edit.php");
		$page->footer(array("type" => "admin"));
		exit();
	
	}

	// ADD ADDRESS
	else if(count($action) == 2 && $action[0] == "new_address") {
	
		$page->header(array("type" => "admin"));
		$page->template("admin/user/new_address.php");
		$page->footer(array("type" => "admin"));
		exit();
	
	}
	// EDIT ADDRESS
	else if(count($action) == 2 && $action[0] == "edit_address") {
	
		$page->header(array("type" => "admin"));
		$page->template("admin/user/edit_address.php");
		$page->footer(array("type" => "admin"));
		exit();
	
	}

	// GROUP LIST
	else if(count($action) == 2 && $action[0] == "group" && $action[1] == "list") {
	
		$page->header(array("type" => "admin", "body_class" => "usergroup", "page_title" => "User groups / Access control management"));
		$page->template("admin/user_group/list.php");
		$page->footer(array("type" => "admin"));
		exit();
	
	}
	// GROUP NEW
	else if(count($action) == 2 && $action[0] == "group" && $action[1] == "new") {
	
		$page->header(array("type" => "admin", "body_class" => "usergroup", "page_title" => "User groups"));
		$page->template("admin/user_group/new.php");
		$page->footer(array("type" => "admin"));
		exit();
	
	}
	// GROUP EDIT
	else if(count($action) == 3 && $action[0] == "group" && $action[1] == "edit") {
	
		$page->header(array("type" => "admin", "body_class" => "usergroup", "page_title" => "User groups"));
		$page->template("admin/user_group/edit.php");
		$page->footer(array("type" => "admin"));
		exit();
	
	}
	// ACCESS EDIT
	else if(count($action) == 3 && $action[0] == "access" && $action[1] == "edit") {
	
		$page->header(array("type" => "admin", "body_class" => "usergroup", "page_title" => "Access control management"));
		$page->template("admin/user/access.php");
		$page->footer(array("type" => "admin"));
		exit();
	
	}

}

$page->header();
$page->template("404.php");
$page->footer();

?>
