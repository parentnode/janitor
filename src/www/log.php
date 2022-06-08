<?php
$access_item["/"] = true;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");
include_once("classes/system/log.class.php");

$action = $page->actions();
$LC = new Log();


$page->bodyClass("logs");
$page->pageTitle("Log viewer");


if(is_array($action) && count($action)) {

	// LIST/EDIT ITEM
	if(preg_match("/^(list|view)$/", $action[0])) {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/log/".$action[0].".php"
		));
		exit();
	}

	// Class interface
	else if(security()->validateCsrfToken() && preg_match("/[a-zA-Z]+/", $action[0])) {

		// check if custom function exists on User class
		if($LC && method_exists($LC, $action[0])) {

			$output = new Output();
			$output->screen($LC->{$action[0]}($action));
			exit();
		}
	}

}

$page->page(array(
	"templates" => "pages/404.php"
));

?>
