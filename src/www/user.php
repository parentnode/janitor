<?php
$access_item["/list"] = true;

$access_item["/new"] = true;
$access_item["/save"] = "/new";

$access_item["/edit"] = true;
$access_item["/update"] = "/edit";

$access_item["/updateUsernames"] = true;
$access_item["/updateEmail"] = "/updateUsernames";
$access_item["/updateMobile"] = "/updateUsernames";
$access_item["/setPassword"] = true;
$access_item["/renewToken"] = true;
$access_item["/disableToken"] = "/renewToken";

$access_item["/address"] = true;
$access_item["/new_address"] = "/address";
$access_item["/edit_address"] = "/address";
$access_item["/addAddress"] = "/address";
$access_item["/updateAddress"] = "/address";
$access_item["/deleteAddress"] = "/address";

$access_item["/newsletters"] = true;
$access_item["/add_newsletter"] = "/newsletters";
$access_item["/addNewsletter"] = "/newsletters";
$access_item["/deleteNewsletter"] = "/newsletters";


$access_item["/delete"] = true;
$access_item["/status"] = true;

$access_item["/access"] = true;
$access_item["/updateAccess"] = "/access";

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

include_once("classes/users/superuser.class.php");
$model = new SuperUser();


$page->bodyClass("user");
$page->pageTitle("User management");


if(is_array($action) && count($action)) {

	// LIST/EDIT/NEW/NEW_ADDRESS/EDIT_ADDRESS
	if(preg_match("/^(list|edit|new|new_address|edit_address|add_newsletter)$/", $action[0])) {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/user/".$action[0].".php"
		));
		exit();
	}

	// GROUP
	else if(preg_match("/^(group)$/", $action[0]) && count($action) > 1) {

		// GROUP LIST
		if(preg_match("/^(list|edit|new)$/", $action[1])) {

			$page->page(array(
				"type" => "janitor",
				"body_class" => "usergroup", 
				"page_title" => "User groups / Access control management",
				"templates" => "janitor/user_group/".$action[1].".php"
			));
			exit();
		}
	}

	// ACCESS EDIT
	else if(preg_match("/^(access)$/", $action[0]) && count($action) > 1) {
		
		if($action[1] == "edit") {

			$page->page(array(
				"type" => "janitor",
				"body_class" => "usergroup", 
				"page_title" => "Access control management",
				"templates" => "janitor/user_group/access.php"
			));
			exit();

		}
	}

	// CONTENT OVERVIEW
	else if(preg_match("/^(content)$/", $action[0]) && count($action) > 1) {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/user/content.php"
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
