<?php
/**
* This file contains the site setup functionality.
*/
class ModuleCore extends Model {


	private $digest_algo;


	/**
	* Get required information
	*/
	function __construct($class_name) {

		parent::__construct($class_name);


		$this->digest_algo = "sha256";
	}

	// Get all installed modules orderred by module group
	function getInstalledModules() {


		$installed_modules = [];

		$manifests = glob(LOCAL_PATH."/config/modules/*/*/manifest.json");
		if($manifests) {
			// debug([$manifests]);

			foreach($manifests as $manifest_file) {

				preg_match("/\/config\/modules\/(.*?)\/(.*?)\/manifest\.json$/", $manifest_file, $matches);
				if($matches && count($matches) == 3) {
					$module_group_id = $matches[1];
					$module_id = $matches[2];

					$installed_modules[$module_group_id][] = $this->getModule($module_group_id, $module_id);
				}

			}
			// debug([$installed_modules]);
			return $installed_modules;

		}

		return false;

	}

	// Get all available modules from jam-sources.php orderred by module group
	function getAvailableModules() {

		$modules = [];

		@include("config/jam-sources.php");
		if($jam_sources) {

			foreach($jam_sources as $module_group_id => $module_group) {
				foreach($module_group["modules"] as $module_id => $module) {
					$modules[$module_group_id][] = $this->getModule($module_group_id, $module_id);
				}
			}

		}

		return $modules;

	}

	function getModule($module_group_id, $module_id) {

		$module = false;

		@include("config/jam-sources.php");
		if($jam_sources) {
			if(isset($jam_sources[$module_group_id]) && isset($jam_sources[$module_group_id]["modules"][$module_id])) {
				$module = $jam_sources[$module_group_id]["modules"][$module_id];
				$module["id"] = $module_id;
				$module["group_id"] = $module_group_id;

				return $module;
			}
		}
		return $module;

	}

	function getModuleGroup($module_group_id) {

		$module_group = false;

		@include("config/jam-sources.php");
		if($jam_sources) {
			if(isset($jam_sources[$module_group_id])) {
				$module_group = [];
				$module_group["id"] = $module_group_id;
				$module_group["name"] = $jam_sources[$module_group_id]["name"];
				$module_group["description"] = $jam_sources[$module_group_id]["description"];

				return $module_group;
			}
		}

		return $module_group;

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

	function checkDigest($module_group_id, $module_id) {

		$modified_files = [];

		$module_config_path = LOCAL_PATH."/config/modules/$module_group_id/$module_id";
		if(file_exists($module_config_path."/manifest.json")) {

			$manifest = json_decode(file_get_contents($module_config_path."/manifest.json"), true);

			// Files to uninstall
			if(isset($manifest["files"])) {

				foreach($manifest["files"] as $file) {

					$destination = $file["dest"];

					if(file_exists(LOCAL_PATH."/".$destination)) {

						$digest = $this->getFileDigest(LOCAL_PATH."/".$destination);
						// debug(["digest", preg_replace("/".$this->digest_algo."\:/", "", $file["digest"]), $digest]);
						if($digest !== preg_replace("/".$this->digest_algo."\:/", "", $file["digest"])) {
							$modified_files[] = $destination;
						}

					}
					else {

						// debug(["missing file", LOCAL_PATH."/".$file]);
						$modified_files[] = $destination;

					}

				}

			}

		}

		// debug([$modified_files]);
		return $modified_files ? $modified_files : true;

	}

	// Get file digest for file
	function getFileDigest($file_path) {
		$hash = hash_file($this->digest_algo, $file_path);
		// debug(["hash", $hash, $file_path]);
		return $hash;
	}

	function updateAvailable($module_group_id, $module_id) {

		$local_version = $this->getLocalVersion($module_group_id, $module_id);
		// debug(["current version", $local_version]);

		$module = $this->getModule($module_group_id, $module_id);

		$raw_content_source = preg_replace("/github\.com/", "raw.githubusercontent.com", $module["repos"]);
		$newest_manifest_response = curl()->request($raw_content_source."/main/manifest.json?dev=".randomKey(4));
		// debug([$raw_content_source, $newest_manifest_response]);
		if($newest_manifest_response && isset($newest_manifest_response["body"])) {
			$manifest = json_decode($newest_manifest_response["body"], true);
			$latest_version = isset($manifest["version"]) ? $manifest["version"] : 0;
			// debug([$local_version, $latest_version]);

			 return (version_compare($local_version, $latest_version, "<") ? $latest_version : false);
		}

		return false;

	}


	// API endpoint for installModule
	function API_installModule($action) {

		if(count($action) == 4) {
			$module_group_id = $action[2];
			$module_id = $action[3];

			$result = $this->installModule($module_group_id, $module_id);
			if($result) {

				message()->addMessage("$module_id module installed sucessfully.");
				return $result;

			}
		}

		message()->addMessage("$module_id module could not be installed.", ["type" => "error"]);
		return false;
	}

	function installModule($module_group_id, $module_id) {

		$SetupClass = new Setup();

		// Do read/write test before continuing
		if(!$SetupClass->readWriteTest()) {
			$result["message"] = "<p>You need to allow Apache to modify files in your project folder.<br />Run this command in your terminal to continue:</p>";
			$result["message"] .= "<code>sudo chown -R ".$SetupClass->get("system", "apache_user").":".$SetupClass->get("system", "deploy_user")." ".PROJECT_PATH."</code>";
			$result["success"] = false;
			return $result;
		}


		$fs = new FileSystem();

		$install_messages = [];

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

		if(file_exists($file_path."/main.tar.gz")) {

			$install_messages[] = ["message" => "Downloaded module package.", "type" => "success"];

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

				$install_messages[] = ["message" => "Module package unpacked.", "type" => "success"];

				// debug([file_get_contents($file_path."/main/manifest.json")]);
				$manifest = json_decode(file_get_contents($file_path."/main/manifest.json"), true);
				// debug([$manifest, json_last_error_msg()]);

				// Install manifest and connect template file
				$module_config_path = LOCAL_PATH."/config/modules/$module_group_id/$module_id";
				$fs->makeDirRecursively($module_config_path);
				$fs->copy($file_path."/main/manifest.json", $module_config_path."/manifest.json");

				$install_messages[] = ["message" => "Module manifest installed.", "type" => "success"];

				// Copy connect template file, if it exists
				if(file_exists($file_path."/main/src/config/connect_".$module_group_id.".php")) {
					$fs->copy($file_path."/main/src/config/connect_".$module_group_id.".php", $module_config_path."/connect_".$module_group_id.".php");
				}


				// Files to install
				if(isset($manifest["files"])) {

					foreach($manifest["files"] as $file_info) {

						$source = $file_info["src"];
						$destination = $file_info["dest"];
						// debug(["install", $source, $destination]);

						// Copy file if it exists
						if(file_exists($file_path."/main/src/$source")) {

							// Check if file already exists to avoid overwriting modified existing files
							// debug(["digest", preg_replace("/".$this->digest_algo."\:/", "", $file["digest"]), $digest]);
							if(file_exists(LOCAL_PATH."/$destination")) {
								// Check file digest, no need to back up identical file
								$digest = $this->getFileDigest(LOCAL_PATH."/".$destination);
								// File does not match, rename file to avoid overwriting
								if($digest !== preg_replace("/".$this->digest_algo."\:/", "", $file_info["digest"])) {
									rename(LOCAL_PATH."/$destination", LOCAL_PATH."/$destination._original_".date("YmdHis"));
								}
							}

							$fs->copy($file_path."/main/src/$source", LOCAL_PATH."/$destination");
						}
						// File is missing in package
						else {
							// debug(["missing file", $file_path."/main/src/$source"]);
							$install_messages[] = ["message" => "Module file is missing: $source", "type" => "error"];
						}

					}

				}

				// DATA to install
				if(isset($manifest["data"]) && isset($manifest["data"]["install"])) {

					foreach($manifest["data"]["install"] as $file) {
						$install_messages[] = $this->moduleSql($file_path."/main/src/".$file);
					}

				}

				// Save files for uninstall
				// Currently only SQL files are supported
				if(isset($manifest["data"]) && isset($manifest["data"]["uninstall"])) {

					foreach($manifest["data"]["uninstall"] as $file) {

						if($fs->copy($file_path."/main/src/".$file, $module_config_path."/uninstall/".$file)) {
							$install_messages[] = ["message" => "Saved uninstall file: $file", "type" => "success"];
						}
						else {
							$install_messages[] = ["message" => "Could not save uninstall file: $file", "type" => "error"];
						}

					}
				}

				// Save files for data uninstall
				// Currently only SQL files are supported
				if(isset($manifest["data"]) && isset($manifest["data"]["uninstall-data"])) {

					foreach($manifest["data"]["uninstall-data"] as $file) {

						if($fs->copy($file_path."/main/src/".$file, $module_config_path."/uninstall-data/".$file)) {
							$install_messages[] = ["message" => "Saved uninstall data file: $file", "type" => "success"];
						}
						else {
							$install_messages[] = ["message" => "Could not save uninstall data file: $file", "type" => "error"];
						}

					}

				}

			}
			// manifest file is missing
			else {
				$install_messages[] = ["message" => "Module manifest file is missing.", "type" => "error"];
			}

		}
		else {
			$install_messages[] = ["message" => "Module package was not found.", "type" => "error"];
		}


		// Clean up
		$fs->removeDirRecursively(PRIVATE_FILE_PATH."/$install_id");

		// Check for errors
		// debug(["install errors", $install_messages]);

		$error = arrayKeyValue($install_messages, "type", "error");
		// debug(["install error", $error]);
		if($error !== false) {

			// Uninstall any partial installation
			$this->uninstallModule($module_group_id, $module_id);
			return false;

		}

		return true;

	}



	function API_uninstallModule($action) {

		if(count($action) == 4) {
			$module_group_id = $action[2];
			$module_id = $action[3];

			$delete_data = getPost("delete_data");
			$delete_modified_files = getPost("delete_modified_files");

			$result = $this->uninstallModule($module_group_id, $module_id, [
				"delete_data" => $delete_data,
				"delete_modified_files" => $delete_modified_files,
			]);
			if($result) {

				message()->addMessage("$module_id module uninstalled");
				return $result;

			}
		}

		message()->addMessage("$module_id module could not be uninstalled.", ["type" => "error"]);
		return false;
	}

	function uninstallModule($module_group_id, $module_id, $_options = false) {
		// debug(["uninstallModule", $module_group_id, $module_id, $_options]);

		$delete_data = false;
		$delete_modified_files = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "delete_data"              : $delete_data               = $_value; break;
					case "delete_modified_files"    : $delete_modified_files     = $_value; break;

				}
			}
		}


		$uninstall_messages = [];


		$fs = new FileSystem();

		$module_config_path = LOCAL_PATH."/config/modules/$module_group_id/$module_id";
		if(file_exists($module_config_path."/manifest.json")) {

			$manifest = json_decode(file_get_contents($module_config_path."/manifest.json"), true);


			// Remove connect file
			if($delete_data && file_exists($module_config_path."/connect_$module_group_id.php") && file_exists(LOCAL_PATH."/config/connect_$module_group_id.php")) {
				// debug(["unlink", LOCAL_PATH."/config/connect_$module_group_id.php"]);
				unlink(LOCAL_PATH."/config/connect_$module_group_id.php");

				$uninstall_messages[] = ["message" => "Remove connect file.", "type" => "success"];
			}

			// Files to uninstall
			if(isset($manifest["files"])) {

				foreach($manifest["files"] as $file_info) {

					$destination = $file_info["dest"];

					if(file_exists(LOCAL_PATH."/".$destination)) {
						// debug(["unlink", LOCAL_PATH."/".$file]);
						$digest = $this->getFileDigest(LOCAL_PATH."/".$destination);
						if($delete_modified_files || $digest === preg_replace("/".$this->digest_algo."\:/", "", $file_info["digest"])) {
							unlink(LOCAL_PATH."/".$destination);
						}
					}
					// debug([dirname(LOCAL_PATH."/".$file), $fs->files(dirname(LOCAL_PATH."/".$file))]);

					// Is this leaving an empty folder, then delete it
					if(!$fs->files(dirname(LOCAL_PATH."/".$destination))) {
						// debug(["removeDirRecursively", dirname(LOCAL_PATH."/".$file)]);
						$fs->removeDirRecursively(dirname(LOCAL_PATH."/".$destination));
					}

				}

			}

			// System data to uninstall
			if(isset($manifest["data"]) && isset($manifest["data"]["uninstall"])) {

				foreach($manifest["data"]["uninstall"] as $file) {
					// Uninstall files are stored in module config path at installation time
					if(file_exists($module_config_path."/uninstall/".$file)) {
						$uninstall_messages[] = $this->moduleSql($module_config_path."/uninstall/".$file);
					}
					else {
						$uninstall_messages[] = ["message" => "Uninstall SQL file is missing: ".$file, "type" => "error"];
					}
				}

			}

			// Delete user data if requested
			if($delete_data && isset($manifest["data"]) && isset($manifest["data"]["uninstall-data"])) {

				foreach($manifest["data"]["uninstall-data"] as $file) {
					// Uninstall files are stored in module config path at installation time
					if(file_exists($module_config_path."/uninstall-data/".$file)) {
						$uninstall_messages[] = $this->moduleSql($module_config_path."/uninstall-data/".$file);
					}
					else {
						$uninstall_messages[] = ["message" => "Uninstall data SQL file is missing: ".$file, "type" => "error"];

					}
				}

			}

			// debug(["uninstall_messages", $uninstall_messages]);
			$error = arrayKeyValue("type", "error", $uninstall_messages);
			if($error !== false) {

				return false;

			}

			// debug(["removeDirRecursively", $module_config_path]);
			$removed = $fs->removeDirRecursively($module_config_path);
			// debug(["removed", $removed]);

			return true;
		}

		return false;
	}


	function API_upgradeModule($action) {

		if(count($action) == 4) {
			$module_group_id = $action[2];
			$module_id = $action[3];

			$delete_modified_files = getPost("delete_modified_files");

			$result = $this->upgradeModule($module_group_id, $module_id, [
				"delete_modified_files" => $delete_modified_files,
			]);
			if($result) {

				message()->addMessage("Module upgraded");
				return $result;
			}
		}

		message()->addMessage("Module could not be upgraded.", ["type" => "error"]);
		return false;
	}

	function upgradeModule($module_group_id, $module_id, $_options = false) {

		// Set a clear base by uninstalling first
		$uninstall_success = $this->uninstallModule($module_group_id, $module_id, $_options);
		// debug(["uninstall_success", $uninstall_success]);
		if($uninstall_success === true) {

			// Reinstall module
			$this->installModule($module_group_id, $module_id, $_options);

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


	// Execute module sql files
	// Used for both install, upgrade and uninstall
	function moduleSql($sql_file) {
		// debug(["sql_file", $sql_file]);

		$query = new Query();

			// found SQL file
		if(file_exists($sql_file)) {
			$sql = file_get_contents($sql_file);
			$sql = str_replace("SITE_DB", SITE_DB, $sql);
			// debug(["sql", $sql]);
			if($query->sql($sql)) {
				$message = "$sql_file imported";
				$type = "success";
			}
			else {
				$message = "$sql_file import failed: ".$query->dbError();
				$type = "error";
			}
		}
		// could not find SQL file
		else {
			$message = "Could not find sql file, $sql_file.";
			$type = "error";
		}

		return array("type" => $type, "message" => $message);
	}

}

?>
