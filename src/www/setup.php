<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init-setup.php");


$action = $page->actions();


$page->pageTitle("Janitor setup guide");


if(is_array($action) && count($action)) {

	// Setup process
	if(preg_match("/^(check|config|database|mail|finish)$/", $action[0])) {

		// Setup class as model
		include_once("classes/system/setup.class.php");
		$model = new Setup();


		// Basic install process
		if(count($action) == 1) {

			$page->page(array(
				"body_class" => $action[0],
				"type" => "setup",
				"templates" => "setup/".$action[0].".php"
			));
			exit();

		}
		// Class interface
		else if($page->validateCsrfToken() && preg_match("/^(update|finish)[a-zA-Z]+/", $action[1])) {

			// check if custom function exists on User class
			if($model && method_exists($model, $action[1])) {

				$output = new Output();
				$output->screen($model->{$action[1]}($action));
				exit();
			}
		}

	}
	// Reset install process
	else if(preg_match("/^(reset)$/", $action[0])) {

		include_once("classes/system/setup.class.php");
		$model = new Setup();

		$output = new Output();
		$output->screen($model->reset());
		exit();

	}
	// keepAlive for install process
	else if(preg_match("/^(keepAlive)$/", $action[0])) {

		print 1;
		exit();

	}
	else if(preg_match("/^(upgrade)$/", $action[0])) {

		if(count($action) == 1) {

			$page->page(array(
				"body_class" => $action[0],
				"type" => "setup",
				"templates" => "upgrade/index.php"
			));
			exit();
			
		}
		else if(count($action) > 1) {

			include_once("classes/system/upgrade.class.php");
			$model = new Upgrade();

			$page->page(array(
				"body_class" => $action[0],
				"type" => "setup",
				"templates" => "upgrade/".$action[1].".php"
			));
			exit();
			
		}


	}


}


// Setup front page
$page->page(array(
	"body_class" => "front",
	"type" => "setup",
	"templates" => "setup/index.php"
));
exit();




?>


<?
// $access_item = false;
// if(isset($read_access) && $read_access) {
// 	return;
// }
//
//
// // no path
// if(!isset($_SERVER["PATH_INFO"]) || $_SERVER["PATH_INFO"] == "/") {
// 	$params = array();
// }
// else {
// 	// get params
// 	$params = explode("/", preg_replace("/^\/|\/$/", "", $_SERVER["PATH_INFO"]));
// }
//
//
// if($params) {
// //	print_r($params);
// 	include_once("../setup/".$param[0].".php");
//
// }
// else {
// //	print "include index";
// 	include_once("../setup/index.php");
//
// }

?>
