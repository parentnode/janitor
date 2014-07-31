<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();


$page->bodyClass("login");
$page->pageTitle("Login");


if(is_array($action) && count($action)) {

	if(count($action) == 1 && $action[0] == "signup") {

		$page->header();
		$page->template("pages/signup.php");
		$page->footer();
		exit();

	}
	else if(count($action) == 1 && $action[0] == "forgot_password") {

		$page->header();
		$page->template("pages/forgot_password.php");
		$page->footer();
		exit();

	}

}


$page->header();
$page->template("pages/login.php");
$page->footer();

?>
