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

	// login/forgot
	if(count($action) == 1 && $action[0] == "forgot") {

		$page->page(array(
			"type" => "janitor",
			"templates" => "pages/forgot_password.php"
		));
		exit();
	}
	// login/forgot/receipt
	else if(count($action) == 2 && $action[0] == "forgot" && $action[1] == "receipt") {

		$page->page(array(
			"type" => "janitor",
			"templates" => "pages/forgot_password_receipt.php"
		));
		exit();
	}
	// login/requestReset
	else if(count($action) == 1 && $action[0] == "requestReset" && security()->validateCsrfToken()) {

		// request password reset
		if($model->requestPasswordReset($action)) {
			header("Location: forgot/receipt");
			exit();
		}

		// could not create reset request
		else {
			message()->addMessage("Sorry, you cannot reset the password for the specified user!", array("type" => "error"));
			header("Location: forgot");
			exit();
		}
	}
}

// plain login
$page->page(array(
	"type" => "janitor",
	"templates" => "pages/login.php"
));

?>
