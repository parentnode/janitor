<?php
$access_item["/"] = true;
$access_item["/countries"] = true;
$access_item["/languages"] = true;
$access_item["/currencies"] = true;
$access_item["/vatrates"] = true;
$access_item["/payment_methods"] = true;
$access_item["/subscription_methods"] = true;

$access_item["/updateData"] = true;
$access_item["/data"] = true;


if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");

$action = $page->actions();
$model = systemdata();


$page->bodyClass("system");
$page->pageTitle("System");


if(is_array($action) && count($action)) {

	// LANGUAGES/COUNTRIES/VATRATES/CURRENCIES/PAYMENT METHODS/SUBSCRIPTION METHODS
	if(preg_match("/^(languages|countries|vatrates|currencies|payment_methods|subscription_methods)$/", $action[0])) {

		if(preg_match("/^(new|list)$/", $action[1])) {

			$page->page([
				"type" => "janitor",
				"templates" => "janitor/system/".$action[0]."/".$action[1].".php"
			]);
			exit();
		}
	}

	else if(count($action) === 1 && preg_match("/^data$/", $action[0])) {

		$page->page([
			"type" => "janitor",
			"templates" => "janitor/system/data/list.php"
		]);
		exit();

	}

	else if(count($action) === 2 && preg_match("/^data$/", $action[0])) {

		$page->page([
			"type" => "janitor",
			"templates" => "janitor/system/data/edit.php",
		]);
		exit();

	}

	// CACHE
	else if(preg_match("/^(cache)$/", $action[0])) {

		$page->page([
			"type" => "janitor",
			"templates" => "janitor/system/cache/list.php"
		]);
		exit();
	}

	// Handle possible API request
	else {
		security()->API_request($model, $action);
	}

}


// bad command
$page->page([
	"templates" => "pages/404.php"
]);

?>
