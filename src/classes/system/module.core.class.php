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

					if(isset($file["dest"])) {

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



		// Reset navigation cache in all scenarios
		cache()->reset("navigation-main-janitor");


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
		filesystem()->makeDirRecursively($file_path);

		// Download file
		$response = curl()->request($module_source, [
			"download" => $file_path."/main.tar.gz",
		]);

		if(file_exists($file_path."/main.tar.gz")) {

			$install_messages[] = ["message" => "Downloaded module package.", "type" => "success"];

			// Create unpacking folder
			filesystem()->makeDirRecursively($file_path."/main");


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

				$install_messages[] = ["message" => "Module unpacked.", "type" => "success"];

				// debug([file_get_contents($file_path."/main/manifest.json")]);
				$manifest = json_decode(file_get_contents($file_path."/main/manifest.json"), true);
				// debug([$manifest, json_last_error_msg()]);

				// Install manifest and connect template file
				$module_config_path = LOCAL_PATH."/config/modules/$module_group_id/$module_id";
				filesystem()->makeDirRecursively($module_config_path);
				filesystem()->copy($file_path."/main/manifest.json", $module_config_path."/manifest.json");

				$install_messages[] = ["message" => "Module manifest installed.", "type" => "success"];

				// Copy connect template file, if it exists
				if(file_exists($file_path."/main/src/config/connect_".$module_group_id.".php")) {
					filesystem()->copy($file_path."/main/src/config/connect_".$module_group_id.".php", $module_config_path."/connect_".$module_group_id.".php");
				}


				// Files to install
				if(isset($manifest["files"])) {

					foreach($manifest["files"] as $file_info) {

						$type = $file_info["type"];

						// File contains data model
						if($type === "data-model") {

							$source = $file_info["src"];

							// Copy file if it exists
							if(file_exists($file_path."/main/src/$source")) {

								// Check file digest
								$digest = $this->getFileDigest($file_path."/main/src/$source");
								if($digest === preg_replace("/".$this->digest_algo."\:/", "", $file_info["digest"])) {

									// Model sql file must be available for upgrade DB syncronization to work
									// Install file
									filesystem()->copy($file_path."/main/src/$source", LOCAL_PATH."/".$source);

									// Run SQL
									$result = $this->moduleSql(LOCAL_PATH."/".$source);
									$install_messages[] = $result;

									// Check result
									if($result["type"] === "table_exists") {

										// Sync DB, to apply any modifications
										include_once("classes/system/upgrade.class.php");
										$UpgradeClass = new Upgrade();
										$sync_result = $UpgradeClass->synchronizeTable(basename($source, ".sql"));
										if($sync_result && $sync_result["success"] === true) {
											$install_messages[] = ["message" => "Table syncronized: ".basename($source, ".sql"), "type" => "success"];
										}
										else {
											$install_messages[] = ["message" => "Table failed syncronization: ".basename($source, ".sql"), "type" => "error"];
										}

									}

								}
								else {
									$install_messages[] = ["message" => "Digest mismatch for file: $source", "type" => "error"];
								}

							}
							// File is missing in package
							else {
								// debug(["missing file", $file_path."/main/src/$source"]);
								$install_messages[] = ["message" => "Module file is missing: $source", "type" => "error"];
							}

						}

						// File contains data to be installed
						else if($type === "data-install") {

							$source = $file_info["src"];

							// Copy file if it exists
							if(file_exists($file_path."/main/src/$source")) {

								// Check file digest
								$digest = $this->getFileDigest($file_path."/main/src/$source");
								if($digest === preg_replace("/".$this->digest_algo."\:/", "", $file_info["digest"])) {

									// No need to save this file – it is a oneway-onetimer
									$install_messages[] = $this->moduleSql($file_path."/main/src/".$source);

								}
								else {
									$install_messages[] = ["message" => "Digest mismatch for file: $source", "type" => "error"];
								}

							}
							// File is missing in package
							else {
								// debug(["missing file", $file_path."/main/src/$source"]);
								$install_messages[] = ["message" => "Module file is missing: $source", "type" => "error"];
							}

						}

						// File contains basic uninstall queries
						else if($type === "data-uninstall") {

							$source = $file_info["src"];

							// Copy file if it exists
							if(file_exists($file_path."/main/src/$source")) {

								// Check file digest
								$digest = $this->getFileDigest($file_path."/main/src/$source");
								if($digest === preg_replace("/".$this->digest_algo."\:/", "", $file_info["digest"])) {

									// Copy file to designated module uninstall location 
									if(filesystem()->copy($file_path."/main/src/".$source, $module_config_path."/uninstall/".$source)) {
										$install_messages[] = ["message" => "Saved uninstall file: $source", "type" => "success"];
									}
									else {
										$install_messages[] = ["message" => "Could not save uninstall file: $source", "type" => "error"];
									}

								}
								else {
									$install_messages[] = ["message" => "Digest mismatch for file: $source", "type" => "error"];
								}

							}
							// File is missing in package
							else {
								// debug(["missing file", $file_path."/main/src/$source"]);
								$install_messages[] = ["message" => "Module file is missing: $source", "type" => "error"];
							}

						}

						// File contains uninstall queries for removing all data
						else if($type === "data-uninstall-all") {

							$source = $file_info["src"];

							// Copy file if it exists
							if(file_exists($file_path."/main/src/$source")) {

								// Check file digest
								$digest = $this->getFileDigest($file_path."/main/src/$source");
								if($digest === preg_replace("/".$this->digest_algo."\:/", "", $file_info["digest"])) {

									// Copy file to designated module uninstall location 
									if(filesystem()->copy($file_path."/main/src/".$source, $module_config_path."/uninstall-data/".$source)) {
										$install_messages[] = ["message" => "Saved uninstall data file: $source", "type" => "success"];
									}
									else {
										$install_messages[] = ["message" => "Could not save uninstall data file: $source", "type" => "error"];
									}

								}
								else {
									$install_messages[] = ["message" => "Digest mismatch for file: $source", "type" => "error"];
								}

							}
							// File is missing in package
							else {
								// debug(["missing file", $file_path."/main/src/$source"]);
								$install_messages[] = ["message" => "Module file is missing: $source", "type" => "error"];
							}

						}

						// Standard file to be installed
						else if(isset($file_info["dest"])){

							$source = $file_info["src"];
							$destination = $file_info["dest"];
							// debug(["install", $source, $destination]);

							// Copy file if it exists
							if(file_exists($file_path."/main/src/$source")) {

								// Check file digest
								$digest = $this->getFileDigest($file_path."/main/src/$source");
								if($digest === preg_replace("/".$this->digest_algo."\:/", "", $file_info["digest"])) {

									// Check if file already exists to avoid overwriting modified existing files
									// debug(["digest", preg_replace("/".$this->digest_algo."\:/", "", $file["digest"]), $digest]);
									if(file_exists(LOCAL_PATH."/$destination")) {

										// Check file digest, no need to back up identical file
										$digest = $this->getFileDigest(LOCAL_PATH."/".$destination);
										// File does not match, rename file to avoid overwriting important modifications
										if($digest !== preg_replace("/".$this->digest_algo."\:/", "", $file_info["digest"])) {
											rename(LOCAL_PATH."/$destination", LOCAL_PATH."/$destination._original_".date("YmdHis"));
										}

									}

									// Install file
									filesystem()->copy($file_path."/main/src/$source", LOCAL_PATH."/$destination");



									// Store default controller for potential duplication
									if($type === "controller" && $module_group_id === "item") {

										filesystem()->copy($file_path."/main/src/$source", "$module_config_path/controller.php");
							
									}



								}
								else {
									$install_messages[] = ["message" => "Digest mismatch for file: $source", "type" => "error"];
								}

							}
							// File is missing in package
							else {
								// debug(["missing file", $file_path."/main/src/$source"]);
								$install_messages[] = ["message" => "Module file is missing: $source", "type" => "error"];
							}

						}

					}

				}

				// Get library with composer
				if(isset($manifest["composer"])) {

					// debug([$manifest["composer"]]);

					foreach($manifest["composer"] as $composer_info) {

						$destination = $composer_info["dest"];
						$json = $composer_info["json"];
					
						$compuser_result = $this->composer($json, LOCAL_PATH."/".$destination);
						if(!$compuser_result || isset($compuser_result["status"]) && $compuser_result["status"] === "error") {
							// $install_messages[] = ["message" => "Composer error: ".print_r($json["require"], true), "type" => "error"];
							$install_messages[] = ["message" => $compuser_result["message"], "type" => "error"];
						}
						else {
							$install_messages[] = ["message" => "$module_id dependencies installed with Composer", "type" => "success"];
						}

					}

				}


				// Check Module constant
				$this->updateModuleGroupConstant($module_group_id);


			}
			// manifest file is missing
			else {
				$install_messages[] = ["message" => "Module manifest file is missing.", "type" => "error"];
			}

		}
		else {
			$install_messages[] = ["message" => "Module package was not found.", "type" => "error"];
		}

		// Copy module files to 
		// Clean up
		filesystem()->removeDirRecursively(PRIVATE_FILE_PATH."/$install_id");

		// Check for errors
		$error = arrayKeyValue($install_messages, "type", "error");
		// debug(["install errors", $install_messages]);
		// debug(["install error", $error]);
		if($error !== false) {
			// debug(["install errors", $install_messages]);

			// Uninstall any partial installation
			$this->uninstallModule($module_group_id, $module_id);
			return $install_messages;

		}

		return true;

	}

	function composer($json, $destination) {
		// debug(["composer", $json, $destination]);

		// Test for composer

		$composer_exists = shell_exec('export PATH="/usr/local/bin:/usr/bin:/opt/local/bin"; php /usr/local/bin/composer.phar --version');
		// debug(["composer_exists", $composer_exists]);


		// Composer does not exist
		if(!$composer_exists || !preg_match("/Composer version/", $composer_exists)) {
			return ["status" => "error", "message" => "Composer is missing:".$composer_exists];
		}


		// Clear the stage
		filesystem()->removeDirRecursively($destination);
		filesystem()->makeDirRecursively($destination);

		// Create composer.json with install information
		file_put_contents($destination."/composer.json", json_encode($json, true));

		// Set export path to find PHP when calling composer
		$command = 'export PATH="/usr/local/bin:/usr/bin:/opt/local/bin"; export HOME='.escapeshellarg($destination."/composer_home").'; php /usr/local/bin/composer.phar install -d '.escapeshellarg($destination).' 2>&1';
		// $result = shell_exec($command);
		exec($command, $result, $result_code);
		// debug(["composer command", $command, $result, $result_code]);


		// Clean up composer install dir
		filesystem()->removeDirRecursively($destination."/composer_home");

		if($result && $result_code === 0) {
			return true;
		}

		return ["status" => "error", "message" => "Composer install error:".print_r($result, true)];

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


		// Reset navigation cache in all scenarios
		cache()->reset("navigation-main-janitor");


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

					$type = $file_info["type"];


					// File contains data model
					if($type === "data-model") {

						$source = $file_info["src"];

						// Uninstall files are stored in module config path at installation time
						if(file_exists(LOCAL_PATH."/".$source)) {

							$digest = $this->getFileDigest(LOCAL_PATH."/".$source);
							if($delete_modified_files || $digest === preg_replace("/".$this->digest_algo."\:/", "", $file_info["digest"])) {
								unlink(LOCAL_PATH."/".$source);
							}
							else {
								$install_messages[] = ["message" => "Digest mismatch for file: $source", "type" => "error"];
							}

						}
						else {
							$uninstall_messages[] = ["message" => "Model SQL is missing: ".$source, "type" => "error"];

						}

					}

					// File contains uninstall queries
					else if($type === "data-uninstall") {

						$source = $file_info["src"];

						// Uninstall files are stored in module config path at installation time
						if(file_exists($module_config_path."/uninstall/".$source)) {

							$digest = $this->getFileDigest($module_config_path."/uninstall/".$source);
							if($delete_modified_files || $digest === preg_replace("/".$this->digest_algo."\:/", "", $file_info["digest"])) {
								$uninstall_messages[] = $this->moduleSql($module_config_path."/uninstall/".$source);
							}
							else {
								$install_messages[] = ["message" => "Digest mismatch for file: $source", "type" => "error"];
							}

						}
						else {
							$uninstall_messages[] = ["message" => "Uninstall SQL file is missing: ".$source, "type" => "error"];
						}

					}
					
					// File contains queries to delete all data – and delete all flag is set
					else if($delete_data && $type === "data-uninstall-all") {

						$source = $file_info["src"];

						// Uninstall files are stored in module config path at installation time
						if(file_exists($module_config_path."/uninstall-data/".$source)) {

							$digest = $this->getFileDigest($module_config_path."/uninstall-data/".$source);
							if($delete_modified_files || $digest === preg_replace("/".$this->digest_algo."\:/", "", $file_info["digest"])) {
								$uninstall_messages[] = $this->moduleSql($module_config_path."/uninstall-data/".$source);
							}
							else {
								$install_messages[] = ["message" => "Digest mismatch for file: $source", "type" => "error"];
							}

						}
						else {
							$uninstall_messages[] = ["message" => "Uninstall data SQL file is missing: ".$source, "type" => "error"];

						}

					}

					// Standard installed file
					else if(isset($file_info["dest"])) {

						$destination = $file_info["dest"];

						if(file_exists(LOCAL_PATH."/".$destination)) {
							// debug(["unlink", LOCAL_PATH."/".$file]);
							$digest = $this->getFileDigest(LOCAL_PATH."/".$destination);
							if($delete_modified_files || $digest === preg_replace("/".$this->digest_algo."\:/", "", $file_info["digest"])) {
								unlink(LOCAL_PATH."/".$destination);
							}
						}
						// debug([dirname(LOCAL_PATH."/".$file), filesystem()->files(dirname(LOCAL_PATH."/".$file))]);

						// Is this leaving an empty folder, then delete it
						if(!filesystem()->files(dirname(LOCAL_PATH."/".$destination))) {
							// debug(["removeDirRecursively", dirname(LOCAL_PATH."/".$file)]);
							filesystem()->removeDirRecursively(dirname(LOCAL_PATH."/".$destination));
						}


						// Remove custom controllers if delete modified files flag is set
						if($type === "controller" && $module_group_id === "item") {

							$controllers = filesystem()->files(LOCAL_PATH."/www", [
								"deny_folders" => "js,css,img,assets,janitor", 
								"allow_extensions" => "php"
							]);
							// debug(["controllers to check for deletion", $controllers]);

							$read_access = true;

							foreach($controllers as $controller) {
								$controller_type = false;
								$controller_itemtype = false;

								include($controller);

								// debug(["controller to check", $controller, $controller_type, $controller_itemtype, $module_id]);
								if($controller_type === "item" && $controller_itemtype && $controller_itemtype === $module_id) {

									// debug(["controller to delete", $controller, $delete_modified_files]);

									$digest = $this->getFileDigest($controller);
									if($delete_modified_files || $digest === preg_replace("/".$this->digest_algo."\:/", "", $file_info["digest"])) {
										unlink($controller);
									}

									// Clean out empty folders
									filesystem()->removeEmptyDirRecursively(LOCAL_PATH."/www");

								}

							}

						}

					}

				}

			}

			// Get library with composer
			if(isset($manifest["composer"])) {

				// debug([$manifest["composer"]]);

				foreach($manifest["composer"] as $composer_info) {

					$destination = $composer_info["dest"];
					filesystem()->removeDirRecursively(dirname(LOCAL_PATH."/".$destination));

				}

			}


			// debug(["uninstall_messages", $uninstall_messages]);
			$error = arrayKeyValue("type", "error", $uninstall_messages);
			if($error !== false) {

				return false;

			}

			// debug(["removeDirRecursively", $module_config_path]);
			$removed = filesystem()->removeDirRecursively($module_config_path);
			// debug(["removed", $removed, $module_config_path]);

			// Check Module constant
			$this->updateModuleGroupConstant($module_group_id, "unin");


			return true;
		}

		return false;
	}


	function updateModuleGroupConstant($module_group_id, $state = "ins") {
		// debug(["updateModuleGroupConstant", $state]);

		if($module_group_id) {

			$constants = [
				"item" => "SITE_ITEMS",
				"shop" => "SITE_SHOP",
				"payment" => "SITE_PAYMENTS"
			];

			$constant = isset($constants[$module_group_id]) ? $constants[$module_group_id] : false;
			// debug(["constant", $constant, defined($constant) ? constant($constant) : "undefined"]);
			if($constant) {

				$files = filesystem()->files(LOCAL_PATH."/config/modules/$module_group_id");
				$current_constant_value = $this->getConstant($constant);


				// Constant exists, but no modules exist
				// Deactivate module group constant
				if($current_constant_value && !$files) {

					$this->setConstant($constant, 0);
		
				}
				// Files exist, but no constant exists
				// Activate module group constant
				else if(!$current_constant_value && $files) {

					$this->setConstant($constant, 1);

				}

			}

		}

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
			return $this->installModule($module_group_id, $module_id, $_options);

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

				// TODO:
				// Validation failed
				// Should constants and connect files be disabled, until settings are valid??


			}

		}

		message()->addMessage("Settings could not be updated", ["type" => "error"]);
		return false;

	}

	// Generic updater – gets values based on module adapter class and just updates these values
	function updateSettings($module_group_id, $module_id, $values) {
		// debug(["updateSettings", $values]);

		$connect_info = file_get_contents(LOCAL_PATH."/config/modules/$module_group_id/$module_id/connect_$module_group_id.php");

		foreach($values as $key => $value) {
			if(preg_match("/###".strtoupper($key)."###/", $connect_info)) {
				$connect_info = preg_replace("/###".strtoupper($key)."###/", $value, $connect_info);
				unset($values[$key]);
			}
		}
		// debug([$values, $connect_info]);
		$connect_info = file_put_contents(LOCAL_PATH."/config/connect_$module_group_id.php", $connect_info);


		// Add remaining values as constants in config
		foreach($values as $key => $value) {
			$this->setConstant($key, $value);
		}


		return true;
	}


	// Set constant in config.php
	function setConstant($name, $value) {


		// Get config
		$config_info = file_get_contents(LOCAL_PATH."/config/config.php");


		// Constant does not exist
		if(!defined($name)) {

			// debug(["constant missing"]);

			// Line is commented out
			if(preg_match("/\/\/[ ]*define\(\"".$name."\", [\"]?(0|1|true|false)[\"]?/", $config_info, $matches)) {
				// debug(["matches", $matches]);
				$config_info = preg_replace("/\/\/[ ]*define\(\"".$name."\", [\"]?(0|1|true|false)[\"]?/", "define(\"".$name."\", $1", $config_info);
				define($name, $matches[1]);
			}
			// Append constant to end of file
			else {
				$config_info .= "\ndefine(\"".$name."\", 0);\n";
				define($name, 0);
			}

		}

		$proper_value = is_numeric($value) || is_bool($value) ? $value : '"'.$value.'"';

		$config_info = preg_replace("/define\(\"".$name."\"[^\n]+/", "define(\"".$name."\", $proper_value);" , $config_info);

		// Remove excessive linebreaks
		$config_info = preg_replace("/[\n]{3,}/", "\n\n" , $config_info);


		// Write config file
		file_put_contents(LOCAL_PATH."/config/config.php", trim($config_info)."\n\n");

	}
	// Read value of constant in config.php (could be rewritten during runtime, so that it differs from current runtime constant)
	function getConstant($name) {

		$config_info = file_get_contents(LOCAL_PATH."/config/config.php");
		if(preg_match("/define\(\"".$name."\", [\"]?([a-zA-Z0-9\-_\/\.])[\"]?/", $config_info, $constant_matches)) {
			return $constant_matches[1];
		}

		return "";
	}


	// Get item controller by searching document root for matching php files
	// Used in item module settings
	function getItemControllers($module_id) {

		$available_controllers = filesystem()->files(LOCAL_PATH."/www", [
			"deny_folders" => "js,css,img,assets,janitor", 
			"allow_extensions" => "php"
		]);
		$controllers = [];

		$read_access = true;
		foreach($available_controllers as $controller) {

			$access_item = [];
			$controller_itemtype = false;

			include($controller);
			if($controller_itemtype && $controller_itemtype === $module_id) {
				$controllers[] = str_replace(".php", "", str_replace(LOCAL_PATH."/www", "", $controller));
			}
		}

		return $controllers;
	}

	// Get main item controller, which is currently the first one found
	function getMainControllerPath($module_id) {

		$product_controllers = module()->getItemControllers($module_id);
		if($product_controllers) {
			return $product_controllers[0];
		}

		return false;
	}


	// Add an additional item controller
	// For items where more than one endpoint may be relevant from a seo perspective, such as pages
	function API_addController($action) {

		$controller_path = getPost("controller_path");

		if(count($action) === 4 && $controller_path) {

			$module_group_id = $action[2];
			$module_id = $action[3];


			if($module_group_id && $module_id) {

				$result = $this->addController([
					"module_group_id" => $module_group_id,
					"module_id" => $module_id,

					"controller_path" => $controller_path
				]);

				if($result) {
					message()->addMessage("Controller added");
					return $result;
				}

			}

		}

		message()->addMessage("Controller could not be added", ["type" => "error"]);
		return false;

	}

	function addController($_options = false) {

		$module_group_id = false;
		$module_id = false;

		$controller_path = false;


		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "module_group_id"          : $module_group_id           = $_value; break;
					case "module_id"                : $module_id                 = $_value; break;

					case "controller_path"          : $controller_path           = $_value; break;

				}
			}
		}

		if($module_group_id && $module_id && $controller_path) {

			if(!preg_match("/\.php$/", $controller_path)) {
				$controller_path .= ".php";
			}

			$module_config_path = LOCAL_PATH."/config/modules/$module_group_id/$module_id";
			if(file_exists($module_config_path."/controller.php") && !file_exists(LOCAL_PATH."/www".$controller_path)) {
				filesystem()->copy($module_config_path."/controller.php", LOCAL_PATH."/www".$controller_path);

				return $controller_path;
			}

		}

		return false;
	}


	// Delete an item controller
	// Only available for multi controller items
	function API_deleteController($action) {

		$controller_path = getPost("controller_path");

		if(count($action) === 4 && $controller_path) {

			$module_group_id = $action[2];
			$module_id = $action[3];

			if($module_group_id && $module_id) {

				$result = $this->deleteController([
					"module_group_id" => $module_group_id,
					"module_id" => $module_id,

					"controller_path" => $controller_path
				]);

				if($result) {
					message()->addMessage("Controller deleted");
					return $result;
				}

			}

		}

		message()->addMessage("Controller could not be deleted", ["type" => "error"]);
		return false;

	}

	function deleteController($_options = false) {

		$module_group_id = false;
		$module_id = false;

		$controller_path = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "module_group_id"          : $module_group_id           = $_value; break;
					case "module_id"                : $module_id                 = $_value; break;

					case "controller_path"          : $controller_path           = $_value; break;

				}
			}
		}

		if($module_group_id && $module_id && $controller_path) {

			if(file_exists(LOCAL_PATH."/www".$controller_path)) {

				$read_access = true;
				$controller_itemtype = false;

				include(LOCAL_PATH."/www".$controller_path);
				if($controller_itemtype && $controller_itemtype === $module_id) {
					unlink(LOCAL_PATH."/www".$controller_path);

					// Update cannonical urls
					model($controller_itemtype)->syncCannonical([
						"itemtype" => $controller_itemtype,
						"controller_deleted" => $controller_path,
					]);

					return true;
				}

			}

		}

		return false;
	}


	// For one controller items, allow renaming existing controller
	// For items where only one controller is meaningful seo wise
	function API_renameController($action) {

		$controller_path = getPost("controller_path");

		if(count($action) === 4 && $controller_path) {

			$module_group_id = $action[2];
			$module_id = $action[3];


			if($module_group_id && $module_id) {

				$result = $this->renameController([
					"module_group_id" => $module_group_id,
					"module_id" => $module_id,

					"controller_path" => $controller_path
				]);

				if($result) {
					message()->addMessage("Controller renamed");
					return $result;
				}

			}

		}

		message()->addMessage("Controller could not be renamed", ["type" => "error"]);
		return false;

	}

	function renameController($_options = false) {

		$module_group_id = false;
		$module_id = false;

		$controller_path = false;


		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "module_group_id"          : $module_group_id           = $_value; break;
					case "module_id"                : $module_id                 = $_value; break;

					case "controller_path"          : $controller_path           = $_value; break;

				}
			}
		}

		if($module_group_id && $module_id && $controller_path) {

			if(!preg_match("/\.php$/", $controller_path)) {
				$controller_path .= ".php";
			}


			// Get existing controllers to remove
			$existing_controllers = $this->getItemControllers($module_id);


			// Create new controller before deleting, since it will be needed for syncing existing cannonical urls
			$module_config_path = LOCAL_PATH."/config/modules/$module_group_id/$module_id";
			if(file_exists($module_config_path."/controller.php") && !file_exists(LOCAL_PATH."/www".$controller_path)) {
				filesystem()->copy($module_config_path."/controller.php", LOCAL_PATH."/www".$controller_path);
			}


			foreach($existing_controllers as $existing_controller) {
				// debug(["remove existing controller", LOCAL_PATH."/www".$existing_controller]);
				unlink(LOCAL_PATH."/www".$existing_controller);

				model($module_id)->syncCannonical([
					"itemtype" => $module_id,
					"controller_deleted" => $existing_controller,
				]);

			}



			return str_replace(".php", "", $controller_path);

		}

		return false;
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
				$message = basename($sql_file)." imported";
				$type = "success";
			}
			else if($query->dbErrorNo() === 1050) {
				$message = basename($sql_file)." import skipped: Table already exists.";
				$type = "table_exists";
			}
			else {
				$message = basename($sql_file)." import failed: ".$query->dbError();
				$type = "error";
			}
		}
		// could not find SQL file
		else {
			$message = "Could not find sql file, " . basename($sql_file) . ".";
			$type = "error";
		}

		return array("type" => $type, "message" => $message);
	}

}

?>
