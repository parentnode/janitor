<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();
$model = new User();


$page->bodyClass("login");
$page->pageTitle("Login");


if(is_array($action) && count($action)) {

	if(count($action) == 1 && $action[0] == "forgot_password") {

		$page->page(array(
			"templates" => "pages/forgot_password.php"
		));
		exit();
	}

}

// plain login
$page->page(array(
	"templates" => "pages/login.php"
));

?>
