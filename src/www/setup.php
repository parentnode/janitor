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

		include_once("classes/system/module.class.php");
		$module_model = new Module();

		if(count($action) == 1) {

			$page->page(array(
				"body_class" => "modules",
				"type" => "setup",
				"templates" => "modules/index.php"
			));
			exit();
			
		}
		else if(count($action) == 4 && $action[1] === "view") {

			$module_group = $action[2];
			$module_name = $action[3];

			$page->page(array(
				"body_class" => "modules",
				"type" => "setup",
				"templates" => "modules/view_module.php"
			));
			exit();

		}
		else if(count($action) == 4 && $action[1] === "install" && security()->validateCsrfToken()) {

			// check if custom function exists on User class
			if($module_model && method_exists($module_model, "API_installModule")) {

				$output = new Output();
				$output->screen($module_model->API_installModule($action));
				exit();
			}
			exit();

		}
		else if(count($action) == 4 && $action[1] === "uninstall" && security()->validateCsrfToken()) {

			// check if custom function exists on User class
			if($module_model && method_exists($module_model, "API_uninstallModule")) {

				$output = new Output();
				$output->screen($module_model->API_uninstallModule($action));
				exit();
			}
			exit();

		}
		else if(count($action) == 4 && $action[1] === "updateSettings" && security()->validateCsrfToken()) {

			// check if custom function exists on User class
			if($module_model && method_exists($module_model, "API_updateSettings")) {

				$output = new Output();
				$output->screen($module_model->API_updateSettings($action));
				exit();
			}
			exit();

		}
		else if(count($action) == 4 && $action[1] === "upgrade" && security()->validateCsrfToken()) {

			// check if custom function exists on User class
			if($module_model && method_exists($module_model, "API_upgradeModule")) {

				$output = new Output();
				$output->screen($module_model->API_upgradeModule($action));
				exit();
			}
			exit();

		}
		else if(count($action) == 3) {

			$module_group = $action[1];
			$module_name = $action[2];

			$page->page(array(
				"body_class" => "modules ".$module_name,
				"type" => "setup",
				"templates" => "janitor/modules/$module_group/$module_name/index.php"
			));
			exit();

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
