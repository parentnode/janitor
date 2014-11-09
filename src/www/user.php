<?php
$access_item["/list"] = true;

$access_item["/new"] = true;
$access_item["/save"] = true;

$access_item["/edit"] = true;
$access_item["/update"] = true;
$access_item["/updateUsernames"] = true;
$access_item["/setPassword"] = true;

$access_item["/addess"] = true;
$access_item["/new_address"] = "/addess";
$access_item["/edit_address"] = "/addess";
$access_item["/addAddress"] = "/addess";
$access_item["/updateAddress"] = "/addess";
$access_item["/deleteAddress"] = "/addess";


$access_item["/delete"] = true;
$access_item["/status"] = true;

$access_item["/access"] = true;
$access_item["/updateAccess"] = true;

$access_item["/group"] = true;
$access_item["/deleteUserGroup"] = "/group";
$access_item["/saveUserGroup"] = "/group";
$access_item["/updateUserGroup"] = "/group";

$access_item["/content"] = true;

if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();

$model = new User();


$page->bodyClass("user");
$page->pageTitle("User management");


if(is_array($action) && count($action)) {

	// LIST ITEM
	if(count($action) >= 1 && $action[0] == "list") {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/user/list.php"
			)
		);
		exit();

	}
	// NEW ITEM
	else if(count($action) == 1 && $action[0] == "new") {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/user/new.php"
			)
		);
		exit();

	}
	// EDIT ITEM
	else if(count($action) == 2 && $action[0] == "edit") {
	
		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/user/edit.php"
			)
		);
		exit();
	
	}

	// ADD ADDRESS
	else if(count($action) == 2 && $action[0] == "new_address") {
	
		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/user/new_address.php"
			)
		);
		exit();
	
	}
	// EDIT ADDRESS
	else if(count($action) == 3 && $action[0] == "edit_address") {
	
		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/user/edit_address.php"
			)
		);
		exit();
	
	}

	// GROUP LIST
	else if(count($action) == 2 && $action[0] == "group" && $action[1] == "list") {
	
		$page->page(array(
			"type" => "janitor",
			"body_class" => "usergroup", 
			"page_title" => "User groups / Access control management",
			"templates" => "janitor/user_group/list.php"
			)
		);
		exit();
	
	}
	// GROUP NEW
	else if(count($action) == 2 && $action[0] == "group" && $action[1] == "new") {
	
		$page->page(array(
			"type" => "janitor",
			"body_class" => "usergroup",
			"page_title" => "User groups",
			"templates" => "janitor/user_group/new.php"
			)
		);
		exit();
	
	}
	// GROUP EDIT
	else if(count($action) == 3 && $action[0] == "group" && $action[1] == "edit") {
	
		$page->page(array(
			"type" => "janitor",
			"body_class" => "usergroup", 
			"page_title" => "User groups",
			"templates" => "janitor/user_group/edit.php"
			)
		);
		exit();
	
	}
	// ACCESS EDIT
	else if(count($action) == 3 && $action[0] == "access" && $action[1] == "edit") {
	
		$page->page(array(
			"type" => "janitor",
			"body_class" => "usergroup", 
			"page_title" => "Access control management",
			"templates" => "janitor/user_group/access.php"
			)
		);
		exit();
	
	}

	// CONTENT OVERVIEW
	else if(count($action) == 2 && $action[0] == "content") {
	
		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/user/content.php"
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
