<?php
$access_item["/"] = true;
$access_item["/renewToken"] = true;
$access_item["/disableToken"] = "/renewToken";
$access_item["/reset"] = false;
$access_item["/resetPassword"] = "/reset";

if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();
$model = new User();


$page->bodyClass("profile");
$page->pageTitle("User profile");


if(is_array($action) && count($action)) {

	// LIST/EDIT/NEW/NEW_ADDRESS/EDIT_ADDRESS
	if(preg_match("/^(new_address|edit_address)$/", $action[0])) {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/profile/".$action[0].".php"
		));
		exit();
	}

	// CONTENT OVERVIEW
	else if(preg_match("/^(content)$/", $action[0])) {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/profile/content.php"
		));
		exit();
	}

	// RESET PASSWORD
	else if(preg_match("/^(reset)$/", $action[0]) && count($action) == 2) {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/profile/reset.php"
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
// edit profile
else {

	$page->page(array(
		"type" => "janitor",
		"templates" => "janitor/profile/edit.php"
	));
	exit();

}


// bad command
$page->page(array(
	"templates" => "pages/404.php"
));

?>
