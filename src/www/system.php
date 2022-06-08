<?php
$access_item["/"] = true;

if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");
include_once("classes/system/system.class.php");

$action = $page->actions();
$model = new System();


$page->bodyClass("system");
$page->pageTitle("System");


if(is_array($action) && count($action)) {

	// LANGUAGES/COUNTRIES/VATRATES/CURRENCIES/PAYMENT METHODS/SUBSCRIPTION METHODS
	if(preg_match("/^(languages|countries|vatrates|currencies|payment_methods|subscription_methods)$/", $action[0])) {

		if(preg_match("/^(new|list)$/", $action[1])) {

			$page->page(array(
				"type" => "janitor",
				"templates" => "janitor/system/".$action[0]."/".$action[1].".php"
			));
			exit();
		}
	}

	// CACHE
	else if(preg_match("/^(cache)$/", $action[0])) {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/system/cache/list.php"
		));
		exit();
	}

	// Class interface
	else if(security()->validateCsrfToken() && preg_match("/[a-zA-Z]+/", $action[0])) {

		// check if custom function exists on User class
		if($model && method_exists($model, $action[0])) {

			$output = new Output();
			$output->screen($model->{$action[0]}($action));
			exit();
		}
	}

}


// bad command
$page->page(array(
	"templates" => "pages/404.php"
));

?>
