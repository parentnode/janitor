<?php
/**
* This file contains the site setup functionality.
*/
class ModuleCore extends Model {



	/**
	* Get required information
	*/
	function __construct($class_name) {

		parent::__construct($class_name);


	}


	function getAvailableModules() {

		@include("config/jam-sources.php");
		if($jam_sources) {
			return $jam_sources;
		}

		return false;

	}

	function getModule($module_group_id, $module_id) {

		@include("config/jam-sources.php");
		if($jam_sources) {
			if(isset($jam_sources[$module_group_id]) && isset($jam_sources[$module_group_id]["modules"][$module_id])) {
				return $jam_sources[$module_group_id]["modules"][$module_id];
			}
		}

	}


	function getLocalVersion($module_group_id, $module_id) {

		if(file_exists(LOCAL_PATH."/config/modules/$module_group_id/$module_id/manifest.json")) {

			$manifest = json_decode(file_get_contents(LOCAL_PATH."/config/modules/$module_group_id/$module_id/manifest.json"), true);
			if($manifest && $manifest["version"]) {
				return $manifest["version"];
			}

		}

		return false;

	}


	function updateAvailable($module_group_id, $module_id) {

		$local_version = $this->getLocalVersion($module_group_id, $module_id);
		// debug(["current version", $local_version]);

		$module = $this->getModule($module_group_id, $module_id);

		$raw_content_source = preg_replace("/github\.com/", "raw.githubusercontent.com", $module["repos"]);
		$newest_manifest_response = curl()->request($raw_content_source."/main/manifest.json?dev=2");
		// debug([$newest_manifest_response]);
		if($newest_manifest_response && isset($newest_manifest_response["body"])) {
			$manifest = json_decode($newest_manifest_response["body"], true);
			$latest_version = isset($manifest["version"]) ? $manifest["version"] : 0;
			// debug([$local_version, $latest_version]);

			 return version_compare($local_version, $latest_version, "<");
		}

		return false;

	}



	function API_installModule($action) {

		if(count($action) == 4) {
			$module_group_id = $action[2];
			$module_id = $action[3];

			$result = $this->installModule($module_group_id, $module_id);
			if($result) {

				message()->addMessage("Module installed");
				return $result;
			}
		}

		message()->addMessage("Module could not be installed.", ["type" => "error"]);
		return false;
	}

	function installModule($module_group_id, $module_id) {

		$SetupClass = new Setup();

		if(!$SetupClass->readWriteTest()) {
			$result["message"] = "<p>You need to allow Apache to modify files in your project folder.<br />Run this command in your terminal to continue:</p>";
			$result["message"] .= "<code>sudo chown -R ".$SetupClass->get("system", "apache_user").":".$SetupClass->get("system", "deploy_user")." ".PROJECT_PATH."</code>";
			$result["success"] = false;
			return $result;
		}


		$fs = new FileSystem();

		$module = $this->getModule($module_group_id, $module_id);

		// Fetch source – unpack/install as specified in manifest

		$module_repos = $module["repos"];
		$module_source = $module_repos."/archive/refs/heads/main.tar.gz";
		// debug([$module_source]);


		// Generate random string for unique download and unpacking folder
		$install_id = randomKey(8);


		$file_path = PRIVATE_FILE_PATH."/$install_id/$module_group_id/$module_id";

		// Create download folder
		$fs->makeDirRecursively($file_path);

		// Download file
		$response = curl()->request($module_source, [
			"download" => $file_path."/main.tar.gz",
		]);


		// Create unpacking folder
		$fs->makeDirRecursively($file_path."/main");


		// Extraction on Windows
		if(!preg_match("/darwin/i", PHP_OS) && preg_match("/win/i", PHP_OS)) {
			// Extract
			$output = shell_exec('"C:/Program Files/7-Zip/7z.exe" x "'.$file_path.'/main.tar.gz" -o"'.$file_path.'/main"');
			$output = shell_exec('"C:/Program Files/7-Zip/7z.exe" x "'.$file_path.'/main.tar" -o"'.$file_path.'/main"');
		}
		// Extraction on Mac/Linux
		else {
			$output = shell_exec("tar -xzf ".$file_path."/main.tar.gz -C ".$file_path."/main --strip-components 1 2>&1");
		}


		// Do we have manifest file
		if(file_exists($file_path."/main/manifest.json")) {
			$manifest = json_decode(file_get_contents($file_path."/main/manifest.json"), true);


			// Install manifest and connect template file
			$module_config_path = LOCAL_PATH."/config/modules/$module_group_id/$module_id";
			$fs->makeDirRecursively($module_config_path);
			$fs->copy($file_path."/main/manifest.json", $module_config_path."/manifest.json");
			if(file_exists($file_path."/main/src/config/connect_".$module_group_id.".php")) {
				$fs->copy($file_path."/main/src/config/connect_".$module_group_id.".php", $module_config_path."/connect_".$module_group_id.".php");
			}


			// Files to install
			if(isset($manifest["files"])) {

				foreach($manifest["files"] as $source => $destination) {

					if(file_exists($file_path."/main/src/$source")) {
						$fs->copy($file_path."/main/src/$source", LOCAL_PATH."/$destination");
					}
					else {
						message()->addMessage("Something terrible happened");
					}

				}

			}

		}

		// Clean up
		$fs->removeDirRecursively(PRIVATE_FILE_PATH."/$install_id");

		return true;

	}


	function API_uninstallModule($action) {

		if(count($action) == 4) {
			$module_group_id = $action[2];
			$module_id = $action[3];

			$result = $this->uninstallModule($module_group_id, $module_id);
			if($result) {

				message()->addMessage("Module uninstalled");
				return $result;
			}
		}

		message()->addMessage("Module could not be uninstalled.", ["type" => "error"]);
		return false;
	}

	function uninstallModule($module_group_id, $module_id) {

		$fs = new FileSystem();

		$module_config_path = LOCAL_PATH."/config/modules/$module_group_id/$module_id";
		if(file_exists($module_config_path."/manifest.json")) {

			$manifest = json_decode(file_get_contents($module_config_path."/manifest.json"), true);


			// Remove connect file
			if(file_exists($module_config_path."/connect_$module_group_id.php") && file_exists(LOCAL_PATH."/config/connect_$module_group_id.php")) {
				// debug(["unlink", LOCAL_PATH."/config/connect_$module_group_id.php"]);
				unlink(LOCAL_PATH."/config/connect_$module_group_id.php");
			}

			// Files to uninstall
			if(isset($manifest["files"])) {

				foreach($manifest["files"] as $file) {

					if(file_exists(LOCAL_PATH."/".$file)) {
						// debug(["unlink", LOCAL_PATH."/".$file]);
						unlink(LOCAL_PATH."/".$file);
					}
					// debug([dirname(LOCAL_PATH."/".$file), $fs->files(dirname(LOCAL_PATH."/".$file))]);

					// Is this leaving an empty folder, then delete it
					if(!$fs->files(dirname(LOCAL_PATH."/".$file))) {
						// debug(["removeDirRecursively", dirname(LOCAL_PATH."/".$file)]);
						$fs->removeDirRecursively(dirname(LOCAL_PATH."/".$file));
					}

				}

			}

			// debug(["removeDirRecursively", $module_config_path]);
			$fs->removeDirRecursively($module_config_path);

			return true;
		}

		return false;
	}


	function API_upgradeModule($action) {

		if(count($action) == 4) {
			$module_group_id = $action[2];
			$module_id = $action[3];

			$result = $this->upgradeModule($module_group_id, $module_id);
			if($result) {

				message()->addMessage("Module upgraded");
				return $result;
			}
		}

		message()->addMessage("Module could not be upgraded.", ["type" => "error"]);
		return false;
	}

	function upgradeModule($module_group_id, $module_id) {

		$fs = new FileSystem();

		$module_config_path = LOCAL_PATH."/config/modules/$module_group_id/$module_id";
		if(file_exists($module_config_path."/manifest.json")) {

			$manifest = json_decode(file_get_contents($module_config_path."/manifest.json"), true);


			// Files to remove before reinstall
			if(isset($manifest["files"])) {

				foreach($manifest["files"] as $file) {

					if(file_exists(LOCAL_PATH."/".$file)) {
						// debug(["unlink", LOCAL_PATH."/".$file]);
						unlink(LOCAL_PATH."/".$file);
					}
					// debug([dirname(LOCAL_PATH."/".$file), $fs->files(dirname(LOCAL_PATH."/".$file))]);

					// Is this leaving an empty folder, then delete it
					if(!$fs->files(dirname(LOCAL_PATH."/".$file))) {
						// debug(["removeDirRecursively", dirname(LOCAL_PATH."/".$file)]);
						$fs->removeDirRecursively(dirname(LOCAL_PATH."/".$file));
					}

				}

			}

			// debug(["removeDirRecursively", $module_config_path]);
			$fs->removeDirRecursively($module_config_path);


			$this->installModule($module_group_id, $module_id);


			return true;
		}

		return false;
	}


	// Parse existing connect file for values
	function getConnectValues($module_group_id) {

		if(file_exists(LOCAL_PATH."/config/connect_".$module_group_id.".php")) {
			$connect_file_lines = file(LOCAL_PATH."/config/connect_".$module_group_id.".php");
			$values = [];
			foreach($connect_file_lines as $line) {
				if(preg_match("/\"([^\"]+)\" \=\> \"([^\"]+)\"/", $line, $matches)) {
					$values[$matches[1]] = $matches[2];
				}
			}
			return $values;
		}

		return false;
	}

	function API_updateSettings($action) {

		if(count($action) === 4) {
			$module_group_id = $action[2];
			$module_id = $action[3];
		}

		// Do update via adapter – it knows the inputs required for it's settings file
		if(file_exists(LOCAL_PATH."/classes/adapters/$module_group_id/$module_id.class.php")) {
			@include_once("classes/adapters/$module_group_id/$module_id.class.php");
			$class_name = "Janitor".ucfirst($module_id);
			$module_class = new $class_name(false);

			$module_model = $module_class->getModel();
			$module_class->getPostedEntities();

			$values = [];
			$entities = [];
			foreach($module_model as $entity => $properties) {
				if($entity !== "item_id" && $entity !== "user_id") {
					$entities[] = $entity;
					$values[$entity] = $module_class->getProperty($entity, "value");
				}
			}

	
			if($this->validateList($entities)) { 
				$result = $this->updateSettings($module_group_id, $module_id, $values);
				if($result) {
					message()->addMessage("Settings updated");
					return $result;
				}
			}
		}

		message()->addMessage("Settings could not be updated", ["type" => "error"]);
		return false;

	}

	// Generic updater – gets values based on module adapter class and just updates these values
	function updateSettings($module_group_id, $module_id, $values) {

		$config_file = file_get_contents(LOCAL_PATH."/config/modules/$module_group_id/$module_id/connect_$module_group_id.php");

		foreach($values as $key => $value) {
			$config_file = preg_replace("/###".strtoupper($key)."###/", $value, $config_file);
		}

		$config_file = file_put_contents(LOCAL_PATH."/config/connect_$module_group_id.php", $config_file);

		return true;
	}

}

?>
