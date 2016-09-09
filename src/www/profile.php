<?php
$access_item["/"] = true;

// $access_item["/reset"] = false;
// $access_item["/resetPassword"] = "/reset";

$access_item["/apitoken"] = true;
$access_item["/renewToken"] = "/apitoken";
$access_item["/disableToken"] = "/apitoken";

$access_item["/readstate"] = true;
$access_item["/addReadstate"] = "/readstate";
$access_item["/deleteReadstate"] = "/readstate";

$access_item["/subscription"] = true;
$access_item["/addSubscription"] = "/subscription";
$access_item["/deleteSubscription"] = "/subscription";

if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();
$model = new User();


$page->bodyClass("profile");
$page->pageTitle("Profile");


if(is_array($action) && count($action)) {


	// CONTENT OVERVIEW
	if(preg_match("/^(content|orders|readstates|subscriptions)$/", $action[0])) {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/profile/".$action[0].".php"
		));
		exit();
	}

	// ADDRESS
	else if(preg_match("/^(address)$/", $action[0]) && count($action) >= 2) {

		// EDIT/NEW
		if(preg_match("/^(new|edit)$/", $action[1])) {

			$page->page(array(
				"type" => "janitor",
				"templates" => "janitor/profile/address/".$action[1].".php"
			));
			exit();
		}

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
