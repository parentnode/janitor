<?php
$access_item["/"] = true;
$access_item["/pull"] = true;
$access_item["/modules"] = true;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init-setup.php");


$action = $page->actions();


// Setup class as model
include_once("classes/system/setup.class.php");
$model = new Setup();


$page->pageTitle("Janitor setup guide");


if(is_array($action) && count($action)) {

	// Setup process
	if(preg_match("/^(software|config|account|database|mail|payment|finish)$/", $action[0])) {

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
		else if(security()->validateCsrfToken() && preg_match("/^(update|finish)[a-zA-Z]+/", $action[1])) {

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

		$output = new Output();
		$output->screen($model->reset());
		exit();

	}
	// Reset install process
	else if(preg_match("/^(pull)$/", $action[0])) {

		if(getPost("pull") == "ok" && security()->validateCsrfToken()) {

			$output = new Output();
			$output->screen($model->pull());
			exit();

		}
		else {

			$page->page(array(
				"body_class" => $action[0],
				"type" => "setup",
				"templates" => "setup/pull.php"
			));
			exit();

		}

	}
	// keepAlive for install process
	else if(preg_match("/^(keepAlive)$/", $action[0])) {

		print 1;
		exit();

	}
	else if(preg_match("/^(modules)$/", $action[0])) {

		$page->pageTitle("Janitor modules");


		$SetupClass = new Setup();

		if(!$SetupClass->readWriteTest()) {

			$page->page(array(
				"body_class" => "modules",
				"type" => "setup",
				"templates" => "modules/read-write-error.php"
			));
			exit();

		}


		if(count($action) == 1) {

			$page->page(array(
				"body_class" => "modules",
				"type" => "setup",
				"templates" => "modules/index.php"
			));
			exit();
			
		}
		else if(count($action) == 4 && $action[1] === "install" && security()->validateCsrfToken()) {

			// check if custom function exists on User class
			if(module() && method_exists(module(), "API_installModule")) {

				$output = new Output();
				$output->screen(module()->API_installModule($action));
 				exit();
			}
			exit();

		}
		else if(count($action) == 4 && $action[1] === "uninstall" && security()->validateCsrfToken()) {

			// check if custom function exists on User class
			if(module() && method_exists(module(), "API_uninstallModule")) {

				$output = new Output();
				$output->screen(module()->API_uninstallModule($action), [
					"reset_messages" => false,
				]);
				exit();
			}
			exit();

		}
		else if(count($action) == 4 && $action[1] === "updateSettings" && security()->validateCsrfToken()) {

			// check if custom function exists on User class
			if(module() && method_exists(module(), "API_updateSettings")) {

				$output = new Output();
				$output->screen(module()->API_updateSettings($action));
				exit();
			}
			exit();

		}
		else if(count($action) == 4 && $action[1] === "upgrade" && security()->validateCsrfToken()) {

			// check if custom function exists on User class
			if(module() && method_exists(module(), "API_upgradeModule")) {

				$output = new Output();
				$output->screen(module()->API_upgradeModule($action));
				exit();
			}
			exit();

		}
		else if(count($action) == 3) {

			$module_group_id = $action[1];
			$module_id = $action[2];

			// Check that module settings actually exists
			if(file_exists(LOCAL_PATH."/templates/janitor/modules/$module_group_id/$module_id/index.php")) {
				$page->page(array(
					"body_class" => "modules ".$module_id,
					"type" => "setup",
					"templates" => "janitor/modules/$module_group_id/$module_id/index.php",
				));
				exit();
			}
			// Returning to module setting after deletion (browser back) – go to main modules page
			else {
				message()->addMessage("Module $module_id was not found");
				header("Location: /janitor/admin/setup/modules");
				exit();
			}

		}

	}
	else if(preg_match("/^(upgrade)$/", $action[0])) {

		$page->pageTitle("Janitor upgrade");

		include_once("classes/system/upgrade.class.php");
		$upgrade_model = new Upgrade();

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
			$upgrade_model = new Upgrade();

			// Class interface
			if(security()->validateCsrfToken() && preg_match("/^[a-zA-Z]+/", $action[1])) {

				// check if custom function exists on User class
				if($upgrade_model && method_exists($upgrade_model, $action[1])) {

					$output = new Output();
					$output->screen($upgrade_model->{$action[1]}($action));
					exit();
				}
			}
			else {

				$page->page(array(
					"body_class" => $action[0],
					"type" => "setup",
					"templates" => "upgrade/".$action[1].".php"
				));
				exit();

			}

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
