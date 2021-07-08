<?php



class UpgradeCore extends Model {


	function __construct() {

		$this->current_janitor_version = "0.7.9.2";

	}


	// process upgrade task
	function process($result, $critical = false) {

		// Critical task failed
		if(!$result["success"] && $critical) {

			// print message
			print '<li class="error">'.$result["message"].'</li>'."\n";
		}
		// Non-critical task failed
		else if(!$result["success"]) {

			// print message
			print '<li class="notice">'.$result["message"].' – AND THAT IS OK</li>'."\n";
		}
		// Task successful
		else {

			print '<li>'.$result["message"].'</li>'."\n";
		}


		// end process on critical error
		if(!$result["success"] && $critical) {

			print '<li class="error fatal">UPGRADE PROCESS STOPPED DUE TO CRITICAL ERRORS</li>';

			throw new Exception();
		}
	}


	function dump($array) {
		print "<li><pre>".print_r($array, true)."</pre></li>";
	}

	// Check Database structure for v0_8 requirements
	function fullUpgrade() {

		// Upgrade can take some time - allow it to take the time it needs
		set_time_limit(0);


		global $model;
		global $page;

		$query = new Query();
		$IC = new Items();
		include_once("classes/users/superuser.class.php");
		$UC = new SuperUser();

		if((defined("SITE_SHOP") && SITE_SHOP)) {
			include_once("classes/shop/supershop.class.php");
			$SC = new SuperShop();
		}


		try {

			if(!$model->readWriteTest()) {
				$result["message"] = "<p>You need to allow Apache to modify files in your project folder.<br />Run this command in your terminal to continue:</p>";
				$result["message"] .= "<code>sudo chown -R ".$model->get("system", "apache_user").":".$model->get("system", "deploy_user")." ".PROJECT_PATH."</code>";
				$result["success"] = false;
				$this->process($result, true);
			}


			// Run any project specific pre-upgade tasks
			if(method_exists($this, "preUpgrade")) {
				$this->preUpgrade();
			}



			// Gradual codebase upgrade


			// Old version – before using embedded versioning
			if(!defined("VERSION")) {

				$this->updatePHPSyntaxToPHP7();
				$this->restructureItemsMediae();

				define("VERSION", 0);
			}

			// Codebase less than 0.7.9
			if(version_compare(VERSION, "0.7.9") < 0) {

				$this->updateSyntaxTo078();

			}

			// Codebase less than 0.8
			if(version_compare(VERSION, "0.8") < 0) {

				$this->updateConstantUsageSyntax08();
				$this->moveJanitorIndexToTemplate08();
				$this->updateConfigFileSyntax08();
				$this->updateConnectFilesSyntax08();
				$this->updateModelDeclarations08();
				$this->updateSyntaxTo08();

			}



			// Gradual database upgrade

			// Get DB version
			$db_version = $query->getDbVersion();


			if(version_compare($db_version, "0.7.9") < 0) {

				$this->prefixSystemTables079();
				$this->newsletterToMaillistUpdate079();
				$this->updateEventModel079();
				$this->upgradePasswords079();

			}

			if(version_compare($db_version, "0.8") < 0) {

				$this->addMembershipPriceTypes08();
				$this->updatePaymentTypes08();
				$this->updateMediaeSyntax08();
				$this->addPaymentId08();
				$this->updateItemPrices08();
				$this->updateConstantUsageSyntax08();
				$this->updateUserSubscriptionPaymentMethods08();
				$this->updateActivationReminders08();
				$this->addBillingNameToOrders08();
				
				$this->removeDeletedItemsFromOrderItems08();

			}

			// Run any project specific post-upgade tasks
			if(method_exists($this, "postUpgrade")) {
				$this->postUpgrade();
			}



			// UPDATING SYSTEM TABLES

			// CREATE ANY MISSING SYSTEM TABLES (CRITICAL)
			$this->process($this->createTableIfMissing(UT_LANGUAGES), true);

			if(defined("SITE_SIGNUP") && SITE_SIGNUP) {
				$this->process($this->createTableIfMissing(UT_MAILLISTS), true);
				$this->process($this->createTableIfMissing(SITE_DB.".user_maillists"), true);
			}

			if((defined("SITE_ITEMS") && SITE_ITEMS)) {
				$this->process($this->createTableIfMissing(UT_ITEMS), true);
				$this->process($this->createTableIfMissing(UT_TAG), true);
				$this->process($this->createTableIfMissing(UT_TAGGINGS), true);

				$this->process($this->createTableIfMissing(UT_ITEMS_COMMENTS), true);
				$this->process($this->createTableIfMissing(UT_ITEMS_RATINGS), true);
				$this->process($this->createTableIfMissing(UT_ITEMS_MEDIAE), true);

				$this->process($this->createTableIfMissing(SITE_DB.".user_item_readstates"), true);
			}

			if((defined("SITE_SHOP") && SITE_SHOP)) {
				$this->process($this->createTableIfMissing(UT_CURRENCIES), true);
				$this->process($this->createTableIfMissing(UT_COUNTRIES), true);
				$this->process($this->createTableIfMissing(UT_PRICE_TYPES), true);
				$this->process($this->createTableIfMissing(UT_VATRATES), true);
				$this->process($this->createTableIfMissing(UT_PAYMENT_METHODS), true);
				$this->process($this->createTableIfMissing(SITE_DB.".user_payment_methods"), true);

				$this->process($this->createTableIfMissing(SITE_DB.".user_addresses"), true);
				$this->process($this->createTableIfMissing(SITE_DB.".shop_carts"), true);
				$this->process($this->createTableIfMissing(SITE_DB.".shop_cart_items"), true);
				$this->process($this->createTableIfMissing(SITE_DB.".shop_orders"), true);
				$this->process($this->createTableIfMissing(SITE_DB.".shop_order_items"), true);
				$this->process($this->createTableIfMissing(SITE_DB.".shop_payments"), true);

				$this->process($this->createTableIfMissing(UT_ITEMS_PRICES), true);
			}

			if((defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS)) {
				$this->process($this->createTableIfMissing(UT_SUBSCRIPTION_METHODS), true);
				$this->process($this->createTableIfMissing(UT_ITEMS_SUBSCRIPTION_METHOD), true);

				$this->process($this->createTableIfMissing(SITE_DB.".user_item_subscriptions"), true);
			}

			if(defined("SITE_MEMBERS") && SITE_MEMBERS) {
				$this->process($this->createTableIfMissing(SITE_DB.".user_members"), true);
			}



			// CHECK DEFAULT VALUES
			if((defined("SITE_SIGNUP") && SITE_SIGNUP)) {
				$this->process($this->checkDefaultValues(UT_LANGUAGES), true);
			}

			if((defined("SITE_SHOP") && SITE_SHOP)) {
				$this->process($this->checkDefaultValues(UT_CURRENCIES), true);
				$this->process($this->checkDefaultValues(UT_COUNTRIES), true);
				$this->process($this->checkDefaultValues(UT_PRICE_TYPES), true);
				$this->process($this->checkDefaultValues(UT_VATRATES), true);
				$this->process($this->checkDefaultValues(UT_PAYMENT_METHODS), true);

			}
			if((defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS)) {
				$this->process($this->checkDefaultValues(UT_SUBSCRIPTION_METHODS), true);
			}



			// Compares tables with sql files
			$query->sql("SELECT TABLE_NAME AS tables FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '".SITE_DB."'");
			$tables = $query->results("tables");
			if($tables) {
				foreach($tables as $table) {
					$this->process($this->synchronizeTable($table));
				}
			}



			//
			// GIT SETTINGS
			//
			// create git ignore
			if(!file_exists(PROJECT_PATH."/.gitignore") && file_exists(FRAMEWORK_PATH."/config/gitignore.template")) {
				copy(FRAMEWORK_PATH."/config/gitignore.template", PROJECT_PATH."/.gitignore");

				// Make sure file remains writeable even if it is edited manually
				chmod(PROJECT_PATH."/.gitignore", 0777);

				$this->process(["message" => "Git ignore file added (.gitignore)", "success" => true]);
			}

			// Add Git attributes
			if(!file_exists(PROJECT_PATH."/.gitattributes") && file_exists(FRAMEWORK_PATH."/config/gitattributes.template")) {
				copy(FRAMEWORK_PATH."/config/gitattributes.template", PROJECT_PATH."/.gitattributes");

				// Make sure file remains writeable even if it is edited manually
				chmod(PROJECT_PATH."/.gitattributes", 0777);

				$this->process(["message" => "Git attributes file added (.gitattributes)", "success" => true]);
			}


			// Tell git to ignore file permission changes
			exec("cd ".PROJECT_PATH." && git config core.filemode false");
			exec("cd ".PROJECT_PATH."/submodules/janitor && git config core.filemode false");

			if(file_exists(PROJECT_PATH."/submodules/asset-builder")) {
				exec("cd ".PROJECT_PATH."/submodules/asset-builder && git config core.filemode false");
			}

			$this->process(["message" => "Git filemode updated to false", "success" => true]);


			if(VERSION == 0 || $db_version == 0) {

				if(file_exists(PROJECT_PATH."/submodules/js-merger")) {
					exec("cd ".PROJECT_PATH."/submodules/js-merger && git config core.filemode false");
				}
				if(file_exists(PROJECT_PATH."/submodules/css-merger")) {
					exec("cd ".PROJECT_PATH."/submodules/css-merger && git config core.filemode false");
				}
			
				// TODO: Upgrade to asset builder if js-merger or css-merger exists (including full removel of js-merger and/or css-merger)

			}



			// set file permissions
			if($model->get("system", "os") == "win") {
				$tasks["completed"][] = "File permissions left untouched for Windows development environment";
			}
			else if($model->recurseFilePermissions(PROJECT_PATH,
				$model->get("system", "apache_user"),
				$model->get("system", "deploy_user"),
				0777)
			) {
				$this->process(["message" => "File permissions set for development environment", "success" => true]);
			}
			else {
				$this->process(["message" => "File permissions could not be set for development environment", "success" => false]);
			}



			// Update code base version
			$config_info = file_get_contents(LOCAL_PATH."/config/config.php");
			$config_info = preg_replace("/define\(\"VERSION\", \"[0-9\.a-zA-Z]*\"\);/", "define(\"VERSION\", \"".$this->current_janitor_version."\");", $config_info);
			if(file_put_contents(LOCAL_PATH."/config/config.php", $config_info)) {
				$this->process(["success" => true, "message" => "Janitor version updated"], false);
			}
			else {
				$this->process(["success" => false, "message" => "Janitor version updated"], true);
			}


			// Update db version
			$query->updateDbVersion($this->current_janitor_version);



			// Upgrade complete
			print '<li class="done">UPGRADE COMPLETE</li>';

			if($model->get("system", "os") != "win" && !preg_match("/\.local$/", SITE_URL)) {
				print '<li class="note">';
				print '	<h3>File permissions for live site</h3>';
				print '	<p>';
				print '		If you are upgrading a production site you need to set <span class="system_warning">file permissions</span>';
				print '		on your project manually.';
				print '	</p>';
				print '	<p>';
				print '		Copy this into your terminal to set file permissions to production settings. You want to make';
				print '		sure this is done to protect your files from unintended manipulation.';
				print '	</p>';
				print '	<code>sudo chown -R root:'.$model->get("system", "deploy_user").' '.PROJECT_PATH.' && sudo chmod -R 755 '.PROJECT_PATH.' && sudo chown -R '.$model->get("system", "apache_user").':'.$model->get("system", "deploy_user").' '.LOCAL_PATH.'/library && sudo chmod -R 770 '.LOCAL_PATH.'/library</code>';
				print '</li>';
			}

			// clear system messages
			message()->resetMessages();

		}
		catch(Exception $exception) {}

	}


	// 0.7 to v 0.9 UPGRADE HELPERS


	// Version 0.7

	function restructureItemsMediae() {

		$query = new Query();
		$IC = new Items();
		$fs = new FileSystem();

		$query->checkDbExistence(UT_ITEMS_MEDIAE);

		$query->sql("SELECT itemtype FROM ".UT_ITEMS." GROUP BY itemtype");
		$itemtypes = $query->results("itemtype");

		// $this->dump($itemtypes);

		foreach($itemtypes as $itemtype) {

			$item_table = $this->tableInfo(SITE_DB.".item_".$itemtype);
			// $this->dump($item_table);
			if($item_table && isset($item_table["columns"]["files"])) {
				$this->dump("Files column found for $itemtype");


				$sql = "SELECT * FROM ".SITE_DB.".item_".$itemtype;
				$query->sql($sql);
				$results = $query->results();

		//		print_r($results);

				foreach($results as $result) {

					if($result["files"]) {

						$item_id = $result["item_id"];
						$format = $result["files"];
						$variant = "main";

						if(file_exists(PRIVATE_FILE_PATH."/".$item_id."/".$format)) {
							$file = PRIVATE_FILE_PATH."/".$item_id."/".$format;
						}
						else if(file_exists(PRIVATE_FILE_PATH."/".$item_id."/".$variant."/".$format)) {
							$file = PRIVATE_FILE_PATH."/".$item_id."/".$variant."/".$format;
						}
						else {
							$this->process(["message" => "File not found for item_id: $item_id", "success" => false], true);
							continue;
						}


						$new_file = PRIVATE_FILE_PATH."/".$item_id."/".$variant."/".$format;

						if(preg_Match("/^(png|jpg)$/", $format)) {
							$image = new Imagick($file);

							// check if we can get relevant info about image
							$width = $image->getImageWidth();
							$height = $image->getImageHeight();
						}
						else if(preg_Match("/^(mov|mp4)$/", $format)) {
							include_once("classes/helpers/video.class.php");
							$VC = new Video();
							$info = $VC->info($file);
							$width = $info["width"];
							$height = $info["height"];
						}
						else {
							$width = 0;
							$height = 0;
						}

						$variant = $variant ."-". randomKey(8);
						$name = $format;
						$filesize = filesize($file);

						// insert into new table
						$sql = "INSERT INTO ".UT_ITEMS_MEDIAE." VALUES(DEFAULT, $item_id, '$name', '$format', '$variant', '$width', '$height', '$filesize', 0)";
		//				print $sql."<br>";

						$query->sql($sql);

						// move image to new folder
						$fs->makeDirRecursively(dirname($new_file));

						if($file != $new_file) {
							copy($file, PRIVATE_FILE_PATH."/".$item_id."/".$variant."/".$format);
							unlink(PRIVATE_FILE_PATH."/".$item_id."/".$format);

							$this->process(["message" => "Media for item_id: $item_id transferred to items_mediae", "success" => true], true);
						}
						$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id);

					}
				}

				$this->process($this->dropColumn(SITE_DB.".item_".$itemtype, "files"), true);

			}


			$item_table = $this->tableInfo(SITE_DB.".item_".$itemtype."_mediae");
			if($item_table) {
				$this->dump("Mediae table found for $itemtype");


				$sql = "SELECT * FROM ".SITE_DB.".item_".$itemtype."_mediae";
				$query->sql($sql);
				$mediae = $query->results();

				// $this->dump($mediae);

				foreach($mediae as $media) {

					$item_id = $media["item_id"];
					$item_format = $media["format"];
					$item_variant = (isset($media["variant"]) ? $media["variant"] : "mediae");
					$new_item_variant = $item_variant."-".randomKey(8);


					$file = PRIVATE_FILE_PATH."/".$item_id."/".$item_variant."/".$item_format;
					// $this->dump($file);

					if(file_exists($file)) {
						// $this->dump("valid file:". $file);

						$item_name = (isset($media["name"]) && $media["name"]) ? $media["name"] : $item_format;
						$item_filesize = (isset($media["filesize"]) && $media["filesize"]) ? $media["filesize"] : filesize($file);
						$item_position = (isset($media["position"]) && $media["position"]) ? $media["position"] : 0;

						if(preg_match("/jpg|png/", $item_format)) {
							$image = new Imagick($file);
							$item_width = $image->getImageWidth();
							$item_height = $image->getImageHeight();
							// $item_width = (isset($media["width"]) && $media["width"]) ? $media["width"] : $image->getImageWidth();
							// $item_height = (isset($media["height"]) && $media["height"]) ? $media["height"] : $image->getImageHeight();
						}
						else if(preg_Match("/^(mov|mp4)$/", $format)) {
							include_once("classes/helpers/video.class.php");
							$VC = new Video();
							$info = $VC->info($file);
							$width = $info["width"];
							$height = $info["height"];
						}
						else {
							$item_width = (isset($media["width"]) && $media["width"]) ? $media["width"] : 0;
							$item_height = (isset($media["height"]) && $media["height"]) ? $media["height"] : 0;
						}

						$new_file = PRIVATE_FILE_PATH."/".$item_id."/".$new_item_variant."/".$item_format;

						$fs->makeDirRecursively(dirname($new_file));

						if(copy($file, $new_file)) {
							$fs->removeDirRecursively(PRIVATE_FILE_PATH."/".$item_id."/".$item_variant);

							$sql = "INSERT INTO ".UT_ITEMS_MEDIAE." SET item_id=$item_id, format='$item_format', variant='$new_item_variant', name='$item_name', filesize=$item_filesize, width=$item_width, height=$item_height, position='$item_position'";
							// $this->dump($sql);
							$query->sql($sql);

							$this->process(["message" => "Media for item_id: $item_id, variant: $item_variant transferred to items_mediae", "success" => true], true);
						}
						$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id);

					}
					else {
						$this->process(["message" => "Media file for item_id: $item_id, variant: $item_variant NOT FOUND", "success" => false], true);
						
						// $this->dump("invalid file:" . $file);
					}



				}

				$this->dropTable(SITE_DB.".item_".$itemtype."_mediae");
				// Delete mediae table
			}
	
		}

		

	}


	// Version 0.7.8


	// Code update
	function updatePHPSyntaxToPHP7() {

		// Updating controller code syntax to work with PHP7
		$fs = new FileSystem();
		$controllers = $fs->files(LOCAL_PATH."/www", ["allow_extensions" => "php", "include_tempfiles" => true]);
		$file = "";
		foreach($controllers as $controller) {

			$file = @file_get_contents($controller);
			if($file) {

				if(preg_match("/->\\\$action\[[0-9]+\]/", $file, $matches)) {
					// replace with valid syntax
					$file = preg_replace("/->\\\$action\[([0-9]+)\]/", "->{\\\$action[$1]}", $file);
					// save file
					if(@file_put_contents($controller, $file)) {
						$this->process(array("success" => true, "message" => "Controller updated to PHP7 syntax: ".basename($controller)), true);
					}
					else {
						$this->process(array("success" => false, "message" => "Write permission denied in controller: ".basename($controller)), true);
					}

				}
			}
			else {
				$this->process(array("success" => false, "message" => "Read permission denied in controller: ".basename($controller)), true);
			}

		}

	}

	// Code update
	function updateSyntaxTo078() {

		$fs = new FileSystem();
		// get all php files in theme
		$php_files = $fs->files(LOCAL_PATH, ["allow_extensions" => "php", "include_tempfiles" => true]);
		foreach($php_files as $php_file) {

			$is_code_altered = false;
			$code_lines = file($php_file);
			foreach($code_lines as $line_no => $line) {

				// Change page->mail to mailer()->send
				if(preg_match("/[\$](page|this)\-\>mail\(/", $line)) {

					$line = preg_replace("/[\$](page|this)\-\>mail\(/", "mailer()->send(", $line);
					if($code_lines[$line_no] != $line) {

						$code_lines[$line_no] = $line;
						$this->process(["success" => false, "message" => "FOUND AND REPLACED OLD CODE (mail) IN " . $php_file . " in line " . ($line_no+1)]);
						$is_code_altered = true;


//							print '<li class="notice">'.</li>';
					}
					else {
						$this->process(["success" => false, "message" => "FOUND OLD CODE (mail) IN " . $php_file . " in line " . ($line_no+1)], true);
//							print '<li class="error">'."FOUND OLD CODE IN " . $php_file . ' in line '.($line_no+1).'</li>';

					}

				}

				// Change ->sliceMedia( to ->sliceMediae(
				if(preg_match("/\-\>sliceMedia\(/", $line)) {
					$line = preg_replace("/\-\>sliceMedia\(/", "->sliceMediae(", $line);
					if($code_lines[$line_no] !== $line) {

						$code_lines[$line_no] = $line;
						$this->process(["success" => false, "message" => "FOUND AND REPLACED OLD CODE (sliceMedia) IN " . $php_file . " in line " . ($line_no+1)]);
						$is_code_altered = true;


//							print '<li class="notice">'.</li>';
					}
					else {
						$this->process(["success" => false, "message" => "FOUND OLD CODE (sliceMedia) IN " . $php_file . " in line " . ($line_no+1)], true);
//							print '<li class="error">'."FOUND OLD CODE IN " . $php_file . ' in line '.($line_no+1).'</li>';

					}

				}

				// Change JML->editMedia( to JML->editMediae(
				if(preg_match("/JML\-\>editMedia\(/", $line)) {
					$line = preg_replace("/JML\-\>editMedia\(/", "JML->editMediae(", $line);
					if($code_lines[$line_no] !== $line) {

						$code_lines[$line_no] = $line;
						$this->process(["success" => false, "message" => "FOUND AND REPLACED OLD CODE (JML->editMedia) IN " . $php_file . " in line " . ($line_no+1)]);
						$is_code_altered = true;

					}
					else {
						$this->process(["success" => false, "message" => "FOUND OLD CODE (JML->editMedia) IN " . $php_file . " in line " . ($line_no+1)], true);
//							print '<li class="error">'."FOUND OLD CODE IN " . $php_file . ' in line '.($line_no+1).'</li>';

					}

				}

			}

			// Should we write
			if($is_code_altered) {
				file_put_contents($php_file, implode("", $code_lines));
			}

		}

	}



	// Version 0.7.9

	function prefixSystemTables079() {

		// PREFIX OLD SYSTEM TABLES WITH "SYSTEM"
		// RENAME (LIKELY) EXISTING TABLES (TABLES MAY NOT EXIST - SO THIS IS NOT CRITICAL)

		if(defined("SITE_ITEMS") && SITE_ITEMS) {
			$this->process($this->renameTable(SITE_DB.".languages", "system_languages"));
		}

		if(defined("SITE_SHOP") && SITE_SHOP) {
			$this->process($this->renameTable(SITE_DB.".countries", "system_countries"));
			$this->process($this->renameTable(SITE_DB.".currencies", "system_currencies"));
			$this->process($this->renameTable(SITE_DB.".vatrates", "system_vatrates"));
		}

		if((defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS)) {
			$this->process($this->renameTable(SITE_DB.".subscription_methods", "system_subscription_methods"));
		}

		if((defined("SITE_SHOP") && SITE_SHOP) || (defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS)) {
			$this->process($this->renameTable(SITE_DB.".payment_methods", "system_payment_methods"));
		}

	}

	function newsletterToMaillistUpdate079() {

		// Update newsletters to maillists
		if(defined("SITE_SIGNUP") && SITE_SIGNUP) {
			$this->process($this->renameTable(SITE_DB.".system_newsletters", "system_maillists"));

			# NEWSLETTERS TO MAILLISTS
			// CREATE MAILLIST TABLE
			$this->process($this->createTableIfMissing(UT_MAILLISTS), true);

			// USER NEWSLETTER SUBSCRIPTIONS (DEPRECATED)
			$user_newsletters_table = $this->tableInfo(SITE_DB.".user_newsletters");
			if($user_newsletters_table) {

				// Drop contraints and keys, to be able to update keys and columns freely
				$this->process($this->dropConstraints(SITE_DB.".user_newsletters"), true);
				$this->process($this->dropKeys(SITE_DB.".user_newsletters"), true);



				// Get all existing newsletters from original (1. version) newsletter table which had a newsletter column
				$query->sql("SELECT * FROM ".SITE_DB.".user_newsletters GROUP BY newsletter");
				$all_newsletters = $query->results();
				// does newsletter result contain old newsletter column (otherwise is has already been updated)
				if($all_newsletters && isset($all_newsletters[0]["newsletter"])) {

					// Create newsletters in new system table
					foreach($all_newsletters as $newsletter) {
						if(!$query->sql("SELECT * FROM ".UT_MAILLISTS." WHERE name = '".$newsletter["newsletter"]."'")) {
							$query->sql("INSERT INTO ".UT_MAILLISTS." set name = '".$newsletter["newsletter"]."'");
						}
					}

					// get all subscribers
					$query->sql("SELECT * FROM ".SITE_DB.".user_newsletters");
					$newsletter_subscribers = $query->results();
	 
					// get all the newsletters from new system table
					$query->sql("SELECT * FROM ".UT_MAILLISTS);
					$newsletters = $query->results();

					// Add newsletter_id column to original table
					$this->process($this->addColumn(SITE_DB.".user_newsletters", "newsletter_id", "int(11) NOT NULL", "user_id"), true);

					// Add newsletter_id's
					foreach($newsletter_subscribers as $subscriber) {
						if($subscriber["newsletter"]) {
							$newsletter_key = arrayKeyValue($newsletters, "name", $subscriber["newsletter"]);
							if($newsletter_key !== false) {
								$query->sql("UPDATE ".SITE_DB.".user_newsletters SET newsletter_id = ".$newsletters[$newsletter_key]["id"]." WHERE user_id = ".$subscriber["user_id"]." AND id = ".$subscriber["id"]);
							}
						}
					}

					// drop newsletter column from orginal table
					$this->process($this->dropColumn(SITE_DB.".user_newsletters", "newsletter"), true);

				}


				// rename "user_newsletters" table to "user_maillists"
				$this->process($this->renameTable(SITE_DB.".user_newsletters", "user_maillists"), true);

				// rename "newsletter_id" column to "maillist_id"
				$this->process($this->renameColumn(SITE_DB.".user_maillists", "newsletter_id", "maillist_id"), true);

				// Re-applying constraints will be done in synchronization
			}

		}

	}

	function updateEventModel079() {
		
		$item_event_hosts = $this->tableInfo(SITE_DB.".item_event_hosts");
		if($item_event_hosts) {

			// rename columns
			$this->process($this->renameColumn(SITE_DB.".item_event_hosts", "host", "location"), true);
			$this->process($this->renameColumn(SITE_DB.".item_event_hosts", "host_address1", "location_address1"), true);
			$this->process($this->renameColumn(SITE_DB.".item_event_hosts", "host_address2", "location_address2"), true);
			$this->process($this->renameColumn(SITE_DB.".item_event_hosts", "host_city", "location_city"), true);
			$this->process($this->renameColumn(SITE_DB.".item_event_hosts", "host_postal", "location_postal"), true);
			$this->process($this->renameColumn(SITE_DB.".item_event_hosts", "host_country", "location_country"), true);
			$this->process($this->renameColumn(SITE_DB.".item_event_hosts", "host_googlemaps", "location_googlemaps"), true);
			$this->process($this->renameColumn(SITE_DB.".item_event_hosts", "host_comment", "location_comment"), true);

			// Rename event hosts to locations
			$this->process($this->renameTable(SITE_DB.".item_event_hosts", "item_event_locations"));

		}

		$item_events = $this->tableInfo(SITE_DB.".item_event");
		if($item_events) {

			// rename columns
			$this->process($this->renameColumn(SITE_DB.".item_event", "host", "location"), true);

		}


		// think.dk specific – move event owner and backers to event editors
		$item_events = $this->tableInfo(SITE_DB.".item_event");
		if($item_events && isset($item_events["columns"]["event_owner"])) {

			// CREATE EVENT EDITORS TABLE
			$this->process($this->createTableIfMissing(SITE_DB.".item_event_editors"), true);

			$event_items = $IC->getItems(["itemtype" => "event", "extend" => true]);
			foreach($event_items as $event_item) {

				$event_item_id = $event_item["id"];

				if($event_item["event_owner"]) {
					$sql = "INSERT INTO ".SITE_DB.".item_event_editors SET user_id = ".$event_item["event_owner"].", item_id = $event_item_id"; 
					$query->sql($sql);
				}

				if($event_item["backer_1"]) {
					$sql = "INSERT INTO ".SITE_DB.".item_event_editors SET user_id = ".$event_item["backer_1"].", item_id = $event_item_id"; 
					$query->sql($sql);
				}

				if($event_item["backer_2"]) {
					$sql = "INSERT INTO ".SITE_DB.".item_event_editors SET user_id = ".$event_item["backer_2"].", item_id = $event_item_id"; 
					$query->sql($sql);
				}

			}

		}
		
	}

	function upgradePasswords079() {

		// ADD UPGRADE COLUMN, IF IT DOES NOT EXIST
		$user_passwords_table = $this->tableInfo(SITE_DB.".user_passwords");
		if($user_passwords_table && !isset($user_passwords_table["columns"]["upgrade_password"])) {

			// move password to password_upgrade
			$this->process($this->renameColumn(SITE_DB.".user_passwords", "password", "upgrade_password"), true);

			// add new password column
			$this->process($this->addColumn(SITE_DB.".user_passwords", "password", "varchar(255) NOT NULL DEFAULT ''", "user_id"), true);

		}
		
	}



	// Version 0.8

	// Code update
	function updateConstantUsageSyntax08() {

		$fs = new FileSystem();
		// get all php files in theme
		$php_files = $fs->files(LOCAL_PATH, ["allow_extensions" => "php", "include_tempfiles" => true]);
		foreach($php_files as $php_file) {

			$is_code_altered = false;
			$code_lines = file($php_file);
			foreach($code_lines as $line_no => $line) {

				// Change href="<?= SITE_SIGNUP \?\>" to a href="< ?= SITE_SIGNUP_URL \?\>"
				if(preg_match("/href\=\"\<\?\= SITE_SIGNUP \?\>\"/", $line)) {

					$line = preg_replace("/href\=\"\<\?\= SITE_SIGNUP \?\>\"/", "href=\"<?= SITE_SIGNUP_URL ?>\"", $line);
					if($code_lines[$line_no] != $line) {

						$code_lines[$line_no] = $line;
						$this->process(["success" => false, "message" => "FOUND AND REPLACED OLD CODE (href=\"SITE_SIGNUP\") IN " . $php_file . " in line " . ($line_no+1)]);
						$is_code_altered = true;

					}
					else {

						$this->process(["success" => false, "message" => "FOUND OLD CODE (href=\"SITE_SIGNUP\") IN " . $php_file . " in line " . ($line_no+1)], true);

					}

				}

				// Change
				// 	<li class="keynav user nofollow"><a href="?logoff=true">
				// 		to
				// 	<li class="keynav user logoff nofollow"><a href="?logoff=true">
				if(preg_match("/\<li class\=\"keynav user nofollow\"\>\<a href\=\"\?logoff\=true\"\>/", $line)) {

					$line = preg_replace("/\<li class\=\"keynav user nofollow\"\>\<a href\=\"\?logoff\=true\"\>/", "<li class=\"keynav user logoff nofollow\"><a href=\"?logoff=true\">", $line);
					if($code_lines[$line_no] != $line) {

						$code_lines[$line_no] = $line;
						$this->process(["success" => false, "message" => "FOUND AND REPLACED OLD CODE (logoff class) IN " . $php_file . " in line " . ($line_no+1)]);
						$is_code_altered = true;

					}
					else {

						$this->process(["success" => false, "message" => "FOUND OLD CODE (logoff class) IN " . $php_file . " in line " . ($line_no+1)], true);

					}

				}

				// Change
				// 	<li class="keynav user nofollow"><a href="/login">
				// 	to
				// 	<li class="keynav user login nofollow"><a href="< ?= SITE_LOGIN_URL ? >">
				if(preg_match("/\<li class\=\"keynav user nofollow\"\>\<a href\=\"\/login\"\>/", $line)) {

					$line = preg_replace("/\<li class\=\"keynav user nofollow\"\>\<a href\=\"\/login\"\>/", "<li class=\"keynav user login nofollow\"><a href=\"<?= SITE_LOGIN_URL ?>\">", $line);
					if($code_lines[$line_no] != $line) {

						$code_lines[$line_no] = $line;
						$this->process(["success" => false, "message" => "FOUND AND REPLACED OLD CODE (href=\"SITE_SIGNUP\") IN " . $php_file . " in line " . ($line_no+1)]);
						$is_code_altered = true;

					}
					else {

						$this->process(["success" => false, "message" => "FOUND OLD CODE (href=\"SITE_SIGNUP\") IN " . $php_file . " in line " . ($line_no+1)], true);

					}

				}

			}

			// Should we write
			if($is_code_altered) {
				file_put_contents($php_file, implode("", $code_lines));
			}

		}

	}

	// Code update
	function moveJanitorIndexToTemplate08() {

		$fs = new FileSystem();

		if(file_exists(LOCAL_PATH."/www/janitor/index.php")) {

			$index_file = file_get_contents(LOCAL_PATH."/www/janitor/index.php");

			// Does file contain standard header line
			if(preg_match("/\<\? \\\$page-\>header\(array\(\"type\" \=\> \"janitor\"\)\) \?\>/", $index_file)) {

				// Check endline semi-colon after header (typically not present)
				if(preg_match("/\\\$page-\>pageTitle\([^$]+\)\n/", $index_file, $title_match)) {
					$index_file = preg_replace("/(\\\$page-\>pageTitle\([^$]+\))/", "$1;", $index_file);
				}

				// Extract template content
				preg_match("/\?\>\n\<\? \\\$page-\>header\(array\(\"type\" \=\> \"janitor\"\)\) \?\>([^$]+)\<\? \\\$page-\>footer\(array\(\"type\" \=\> \"janitor\"\)\) \?\>/", $index_file, $match);

				if($match) {
					// $this->dump($match);
					// Replace header/template content/footer with regular page template 
					$index_file = preg_replace("/\?\>\n\<\? \\\$page-\>header\(array\(\"type\" \=\> \"janitor\"\)\) \?>[^$]+\<\? \\\$page-\>footer\(array\(\"type\" \=\> \"janitor\"\)\) \?\>/", "\n\$page->page(array(\n\t\"type\" => \"janitor\",\n\t\"templates\" => \"janitor/front/index.php\"\n));\nexit();\n\n", $index_file);

					// Write updated controller
					file_put_contents(LOCAL_PATH."/www/janitor/index.php", $index_file);

					// Evaluate template content
					// Non standard content – move it to local template file
					if(preg_replace("/[\n\r\t ]+/", "", trim($match[1])) != "<divclass=\"scenefront\"><h1><?=SITE_NAME?>Admin</h1></div>") {

						$fs->makeDirRecursively(LOCAL_PATH."/templates/janitor/front");
						if(file_put_contents(LOCAL_PATH."/templates/janitor/front/index.php", $match[1])) {
							$this->process(["success" => false, "message" => "/www/janitor/index.php converted to controller using template templates/janitor/front/index.php"], false);
						}
						else {
							$this->process(["success" => false, "message" => "Could not finish controller conversion. You should check /www/janitor/index.php and templates/janitor/front/index.php for errors before you continue"], true);
						}

					}
					else {
						$this->process(["success" => false, "message" => "/www/janitor/index.php converted to controller using default Janitor template"], false);
					}
				}
				else {

					$this->process(["success" => false, "message" => "Could not convert Janitor index controller to controller/template. You should consider doing this manually."], true);

				}


			}

		}

	}

	// Code update
	function updateConfigFileSyntax08() {

		// Get config
		$config_info = file_get_contents(LOCAL_PATH."/config/config.php");


		// Remove closing PHP tag
		if(preg_match("/\?\>[ \n\t]+$/", $config_info)) {
			$config_info = preg_replace("/\?\>[ \n\t]+$/", "\n", $config_info);
		}
		// Or add new line to safely append more info to file
		else {
			$config_info .= "\n";
		}



		// Check version constant
		if(!defined("VERSION") || VERSION === 0) {

			// Line is commented out
			if(preg_match("/\/\/[ ]*define\(\"VERSION\"/", $config_info)) {
				$config_info = preg_replace("/\/\/[ ]*define\(\"VERSION\"/", "define(\"VERSION\"" , $config_info);
			}
			// Insert line
			else {
				// Insert after error_reporting
				if(preg_match("/error_reporting\(E_ALL\);/", $config_info)) {
					$config_info = preg_replace("/(error_reporting\(E_ALL\);)/", "$1\n\ndefine(\"VERSION\", \"\");\n" , $config_info);
				}
				// Append to file
				else {
					$config_info .= "\ndefine(\"VERSION\", \"\");\n";
				}
			}

			$this->process(["success" => false, "message" => "Missing VERSION constant added to config.php"], false);

		}

		// Correct package comment
		$config_info = preg_replace("/package Config Dummy file/", "package Config", $config_info);

		// Remove known comments 
		$config_info = preg_replace("/\/\*\*\n\* Required site information\n\*\//", "", $config_info);
		$config_info = preg_replace("/\/\*\*\n\* Site name\n\*\//", "", $config_info);
		$config_info = preg_replace("/\/\*\*\n\* Optional constants\n\*\//", "", $config_info);
		$config_info = preg_replace("/\/\/ Enable [a-z]+ model[^\n]*/i", "", $config_info);
		$config_info = preg_replace("/\/\/ Define current version[^\n]*/i", "", $config_info);


		// Check page description constant
		if(!defined("DEFAULT_PAGE_DESCRIPTION")) {
			// Line is commented out
			if(preg_match("/\/\/[ ]*define\(\"DEFAULT_PAGE_DESCRIPTION\"/", $config_info)) {
				$config_info = preg_replace("/\/\/[ ]*define\(\"DEFAULT_PAGE_DESCRIPTION\"/", "define(\"DEFAULT_PAGE_DESCRIPTION\"" , $config_info);
			}
			// Insert line
			else {
				// Insert after SITE_EMAIL
				if(preg_match("/define\(\"SITE_EMAIL\"[^\n]+/", $config_info)) {
					$config_info = preg_replace("/(define\(\"SITE_EMAIL\"[^\n]+)/", "$1\ndefine(\"DEFAULT_PAGE_DESCRIPTION\", \"\");\n" , $config_info);
				}
				// Append to file
				else {
					$config_info .= "\ndefine(\"DEFAULT_PAGE_DESCRIPTION\", \"\");\n";
				}
			}

			$this->process(["success" => true, "message" => "Missing DEFAULT_PAGE_DESCRIPTION constant added to config.php"], false);
		}
		// Check line position
		if(preg_match("/define\(\"DEFAULT_PAGE_DESCRIPTION\"[^\n]+/", $config_info, $line_match)) {
			$config_info = preg_replace("/define\(\"DEFAULT_PAGE_DESCRIPTION\"[^\n]+/", "" , $config_info);
			$config_info = preg_replace("/(define\(\"SITE_EMAIL\"[^\n]+)/", "$1\n\n".$line_match[0]."\n" , $config_info);
		}

		// Check page image constant
		if(!defined("DEFAULT_PAGE_IMAGE")) {
			// Line is commented out
			if(preg_match("/\/\/[ ]*define\(\"DEFAULT_PAGE_IMAGE\"/", $config_info)) {
				$config_info = preg_replace("/\/\/[ ]*define\(\"DEFAULT_PAGE_IMAGE\"/", "define(\"DEFAULT_PAGE_IMAGE\"" , $config_info);
			}
			// Insert line
			else {
				// Insert after DEFAULT_PAGE_DESCRIPTION
				if(preg_match("/define\(\"DEFAULT_PAGE_DESCRIPTION\"[^\n]+/", $config_info)) {
					$config_info = preg_replace("/(define\(\"DEFAULT_PAGE_DESCRIPTION\"[^\n]+)/", "$1\ndefine(\"DEFAULT_PAGE_IMAGE\", \"/img/logo.png\");\n" , $config_info);
				}
				// Append to file
				else {
					$config_info .= "\ndefine(\"DEFAULT_PAGE_IMAGE\", \"/img/logo.png\");\n";
				}
			}

			$this->process(["success" => true, "message" => "Missing DEFAULT_PAGE_IMAGE constant added to config.php"], false);
		}
		// Check line position
		if(preg_match("/define\(\"DEFAULT_PAGE_IMAGE\"[^\n]+/", $config_info, $line_match)) {
			$config_info = preg_replace("/define\(\"DEFAULT_PAGE_IMAGE\"[^\n]+/", "" , $config_info);
			$config_info = preg_replace("/(define\(\"DEFAULT_PAGE_DESCRIPTION\"[^\n]+)/", "$1\n".$line_match[0]."\n" , $config_info);
		}


		// Check language constant
		if(!defined("DEFAULT_LANGUAGE_ISO")) {
			// Line is commented out
			if(preg_match("/\/\/[ ]*define\(\"DEFAULT_LANGUAGE_ISO\"/", $config_info)) {
				$config_info = preg_replace("/\/\/[ ]*define\(\"DEFAULT_LANGUAGE_ISO\"/", "define(\"DEFAULT_LANGUAGE_ISO\"" , $config_info);
			}
			// Insert line
			else {
				// Insert after DEFAULT_PAGE_IMAGE
				if(preg_match("/define\(\"DEFAULT_PAGE_IMAGE\"[^\n]+/", $config_info)) {
					$config_info = preg_replace("/(define\(\"DEFAULT_PAGE_IMAGE\"[^\n]+)/", "$1\n\ndefine(\"DEFAULT_LANGUAGE_ISO\", \"EN\");\n" , $config_info);
				}
				// Append to file
				else {
					$config_info .= "\ndefine(\"DEFAULT_LANGUAGE_ISO\", \"EN\");\n";
				}
			}
			$this->process(["success" => true, "message" => "Missing DEFAULT_LANGUAGE_ISO constant added to config.php"], false);
		}
		// Check line position
		if(preg_match("/define\(\"DEFAULT_LANGUAGE_ISO\"[^\n]+/", $config_info, $line_match)) {
			$config_info = preg_replace("/define\(\"DEFAULT_LANGUAGE_ISO\"[^\n]+/", "" , $config_info);
			$config_info = preg_replace("/(define\(\"DEFAULT_PAGE_IMAGE\"[^\n]+)/", "$1\n\n".$line_match[0]."\n" , $config_info);
		}

		// Check country constant
		if(!defined("DEFAULT_COUNTRY_ISO")) {
			// Line is commented out
			if(preg_match("/\/\/[ ]*define\(\"DEFAULT_COUNTRY_ISO\"/", $config_info)) {
				$config_info = preg_replace("/\/\/[ ]*define\(\"DEFAULT_COUNTRY_ISO\"/", "define(\"DEFAULT_COUNTRY_ISO\"" , $config_info);
			}
			// Insert line
			else {
				// Insert after DEFAULT_LANGUAGE_ISO
				if(preg_match("/define\(\"DEFAULT_LANGUAGE_ISO\"[^\n]+/", $config_info)) {
					$config_info = preg_replace("/(define\(\"DEFAULT_LANGUAGE_ISO\"[^\n]+)/", "$1\ndefine(\"DEFAULT_COUNTRY_ISO\", \"DK\");\n" , $config_info);
				}
				// Append to file
				else {
					$config_info .= "\ndefine(\"DEFAULT_COUNTRY_ISO\", \"DK\");\n";
				}
			}
			$this->process(["success" => true, "message" => "Missing DEFAULT_COUNTRY_ISO constant added to config.php"], false);
		}
		// Check line position
		if(preg_match("/define\(\"DEFAULT_COUNTRY_ISO\"[^\n]+/", $config_info, $line_match)) {
			$config_info = preg_replace("/define\(\"DEFAULT_COUNTRY_ISO\"[^\n]+/", "" , $config_info);
			$config_info = preg_replace("/(define\(\"DEFAULT_LANGUAGE_ISO\"[^\n]+)/", "$1\n".$line_match[0]."\n" , $config_info);
		}

		// Check currency constant
		if(!defined("DEFAULT_CURRENCY_ISO")) {

			// Line is commented out
			if(preg_match("/\/\/[ ]*define\(\"DEFAULT_CURRENCY_ISO\"/", $config_info)) {
				$config_info = preg_replace("/\/\/[ ]*define\(\"DEFAULT_CURRENCY_ISO\"/", "define(\"DEFAULT_CURRENCY_ISO\"" , $config_info);
			}
			// Insert line
			else {
				// Insert after DEFAULT_COUNTRY_ISO
				if(preg_match("/define\(\"DEFAULT_COUNTRY_ISO\"[^\n]+/", $config_info)) {
					$config_info = preg_replace("/(define\(\"DEFAULT_COUNTRY_ISO\"[^\n]+)/", "$1\ndefine(\"DEFAULT_CURRENCY_ISO\", \"DKK\");\n" , $config_info);
				}
				// Append to file
				else {
					$config_info .= "\ndefine(\"DEFAULT_CURRENCY_ISO\", \"DKK\");\n";
				}
			}
			$this->process(["success" => true, "message" => "Missing DEFAULT_CURRENCY_ISO constant added to config.php"], false);
		}
		// Check line position
		if(preg_match("/define\(\"DEFAULT_CURRENCY_ISO\"[^\n]+/", $config_info, $line_match)) {
			$config_info = preg_replace("/define\(\"DEFAULT_CURRENCY_ISO\"[^\n]+/", "" , $config_info);
			$config_info = preg_replace("/(define\(\"DEFAULT_COUNTRY_ISO\"[^\n]+)/", "$1\n".$line_match[0]."\n" , $config_info);
		}


		// Check login url constant
		if(!defined("SITE_LOGIN_URL")) {

			// Check for obvious frontend login controller
			$project_login_controller = file_exists(LOCAL_PATH."/www/login.php");

			// Line is commented out
			if(preg_match("/\/\/[ ]*define\(\"SITE_LOGIN_URL\"/", $config_info)) {
				$config_info = preg_replace("/\/\/[ ]*define\(\"SITE_LOGIN_URL\", [^\)]+/", "define(\"SITE_LOGIN_URL\", \"".($project_login_controller ? "/login" : "/janitor/admin/login")."\"" , $config_info);
			}
			// Insert line
			else {
				// Insert after DEFAULT_CURRENCY_ISO
				if(preg_match("/define\(\"DEFAULT_CURRENCY_ISO\"[^\n]+/", $config_info)) {
					$config_info = preg_replace("/(define\(\"DEFAULT_CURRENCY_ISO\"[^\n]+)/", "$1\n\ndefine(\"SITE_LOGIN_URL\", \"".($project_login_controller ? "/login" : "/janitor/admin/login")."\");\n" , $config_info);
				}
				// Append to file
				else {
					$config_info .= "\ndefine(\"SITE_LOGIN_URL\", \"".($project_login_controller ? "/login" : "/janitor/admin/login")."\");\n";
				}
			}
			$this->process(["success" => true, "message" => "Missing SITE_LOGIN_URL constant added to config.php"], false);
		}
		// Check line position
		if(preg_match("/define\(\"SITE_LOGIN_URL\"[^\n]+/", $config_info, $line_match)) {
			$config_info = preg_replace("/define\(\"SITE_LOGIN_URL\"[^\n]+/", "" , $config_info);
			$config_info = preg_replace("/(define\(\"DEFAULT_CURRENCY_ISO\"[^\n]+)/", "$1\n\n".$line_match[0]."\n" , $config_info);
		}


		// Check singup constant
		if(!defined("SITE_SIGNUP")) {
			// Line is commented out
			if(preg_match("/\/\/[ ]*define\(\"SITE_SIGNUP\"/", $config_info)) {
				$config_info = preg_replace("/\/\/[ ]*define\(\"SITE_SIGNUP\", [^\)]+/", "define(\"SITE_SIGNUP\", false" , $config_info);
			}
			// Insert line
			else {
				// Insert after SITE_LOGIN_URL
				if(preg_match("/define\(\"SITE_LOGIN_URL\"[^\n]+/", $config_info)) {
					$config_info = preg_replace("/(define\(\"SITE_LOGIN_URL\"[^\n]+)/", "$1\n\ndefine(\"SITE_SIGNUP\", false);\n" , $config_info);
				}
				// Append to file
				else {
					$config_info .= "\ndefine(\"SITE_SIGNUP\", false);\n";
				}
			}
			$this->process(["success" => true, "message" => "Missing SITE_SIGNUP constant added to config.php"], false);
		}
		// Check line position
		if(preg_match("/define\(\"SITE_SIGNUP\"[^\n]+/", $config_info, $line_match)) {
			$config_info = preg_replace("/define\(\"SITE_SIGNUP\"[^\n]+/", "" , $config_info);
			$config_info = preg_replace("/(define\(\"SITE_LOGIN_URL\"[^\n]+)/", "$1\n\n".$line_match[0]."\n" , $config_info);
		}

		// Check signup url constant
		if(!defined("SITE_SIGNUP_URL")) {
			// Line is commented out
			if(preg_match("/\/\/[ ]*define\(\"SITE_SIGNUP_URL\"/", $config_info)) {
				$config_info = preg_replace("/\/\/[ ]*define\(\"SITE_SIGNUP_URL\"/", "define(\"SITE_SIGNUP_URL\"" , $config_info);
			}
			// Insert line
			else {
				// Insert after SITE_SIGNUP
				if(preg_match("/define\(\"SITE_SIGNUP\"[^\n]+/", $config_info)) {
					$config_info = preg_replace("/(define\(\"SITE_SIGNUP\"[^\n]+)/", "$1\ndefine(\"SITE_SIGNUP_URL\", \"/signup\");\n" , $config_info);
				}
				// Append to file
				else {
					$config_info .= "\ndefine(\"SITE_SIGNUP_URL\", \"signup\");\n";
				}
			}
			$this->process(["success" => true, "message" => "Missing SITE_SIGNUP_URL constant added to config.php"], false);
		}
		// Check line position
		if(preg_match("/define\(\"SITE_SIGNUP_URL\"[^\n]+/", $config_info, $line_match)) {
			$config_info = preg_replace("/define\(\"SITE_SIGNUP_URL\"[^\n]+/", "" , $config_info);
			$config_info = preg_replace("/(define\(\"SITE_SIGNUP\"[^\n]+)/", "$1\n".$line_match[0]."\n" , $config_info);
		}


		// Check items constant
		if(!defined("SITE_ITEMS")) {
			// Line is commented out
			if(preg_match("/\/\/[ ]*define\(\"SITE_ITEMS\"/", $config_info)) {
				$config_info = preg_replace("/\/\/[ ]*define\(\"SITE_ITEMS\",[^\)]+/", "define(\"SITE_ITEMS\", false" , $config_info);
			}
			// Insert line
			else {
				// Insert after SITE_SIGNUP_URL
				if(preg_match("/define\(\"SITE_SIGNUP_URL\"[^\n]+/", $config_info)) {
					$config_info = preg_replace("/(define\(\"SITE_SIGNUP_URL\"[^\n]+)/", "$1\n\ndefine(\"SITE_ITEMS\", false);\n" , $config_info);
				}
				// Append to file
				else {
					$config_info .= "\ndefine(\"SITE_ITEMS\", false);\n";
				}
			}
			$this->process(["success" => true, "message" => "Missing SITE_ITEMS constant added to config.php"], false);
		}
		// Check line position
		if(preg_match("/define\(\"SITE_ITEMS\"[^\n]+/", $config_info, $line_match)) {
			$config_info = preg_replace("/define\(\"SITE_ITEMS\"[^\n]+/", "" , $config_info);
			$config_info = preg_replace("/(define\(\"SITE_SIGNUP_URL\"[^\n]+)/", "$1\n\n".$line_match[0]."\n" , $config_info);
		}


		// Check shop constant
		if(!defined("SITE_SHOP")) {
			// Line is commented out
			if(preg_match("/\/\/[ ]*define\(\"SITE_SHOP\"/", $config_info)) {
				$config_info = preg_replace("/\/\/[ ]*define\(\"SITE_SHOP\",[^\)]+/", "define(\"SITE_SHOP\", false" , $config_info);
			}
			// Insert line
			else {
				// Insert after SITE_ITEMS
				if(preg_match("/define\(\"SITE_ITEMS\"[^\n]+/", $config_info)) {
					$config_info = preg_replace("/(define\(\"SITE_ITEMS\"[^\n]+)/", "$1\n\ndefine(\"SITE_SHOP\", false);\n" , $config_info);
				}
				// Append to file
				else {
					$config_info .= "\ndefine(\"SITE_SHOP\", false);\n";
				}
			}
			$this->process(["success" => true, "message" => "Missing SITE_SHOP constant added to config.php"], false);
		}
		// Check line position
		if(preg_match("/define\(\"SITE_SHOP\"[^\n]+/", $config_info, $line_match)) {
			$config_info = preg_replace("/define\(\"SITE_SHOP\"[^\n]+/", "" , $config_info);
			$config_info = preg_replace("/(define\(\"SITE_ITEMS\"[^\n]+)/", "$1\n\n".$line_match[0]."\n" , $config_info);
		}

		// Check order notifies constant
		if(!defined("SHOP_ORDER_NOTIFIES")) {
			// Line is commented out
			if(preg_match("/\/\/[ ]*define\(\"SHOP_ORDER_NOTIFIES\"/", $config_info)) {
				$config_info = preg_replace("/\/\/[ ]*define\(\"SHOP_ORDER_NOTIFIES\"/", "define(\"SHOP_ORDER_NOTIFIES\"" , $config_info);
			}
			// Insert line
			else {
				// Insert after SITE_SHOP
				if(preg_match("/define\(\"SITE_SHOP\"[^\n]+/", $config_info)) {
					$config_info = preg_replace("/(define\(\"SITE_SHOP\"[^\n]+)/", "$1\ndefine(\"SHOP_ORDER_NOTIFIES\", \"\");\n" , $config_info);
				}
				// Append to file
				else {
					$config_info .= "\ndefine(\"SHOP_ORDER_NOTIFIES\", \"\");\n";
				}
			}
			$this->process(["success" => true, "message" => "Missing SHOP_ORDER_NOTIFIES constant added to config.php"], false);
		}
		// Check line position
		if(preg_match("/define\(\"SHOP_ORDER_NOTIFIES\"[^\n]+/", $config_info, $line_match)) {
			$config_info = preg_replace("/define\(\"SHOP_ORDER_NOTIFIES\"[^\n]+/", "" , $config_info);
			$config_info = preg_replace("/(define\(\"SITE_SHOP\"[^\n]+)/", "$1\n".$line_match[0]."\n" , $config_info);
		}


		// Check subscription constant
		if(!defined("SITE_SUBSCRIPTIONS")) {
			// Line is commented out
			if(preg_match("/\/\/[ ]*define\(\"SITE_SUBSCRIPTIONS\"/", $config_info)) {
				$config_info = preg_replace("/\/\/[ ]*define\(\"SITE_SUBSCRIPTIONS\", [^\)]+/", "define(\"SITE_SUBSCRIPTIONS\", false" , $config_info);
			}
			// Insert line
			else {
				// Insert after SITE_EMAIL
				if(preg_match("/define\(\"SHOP_ORDER_NOTIFIES\"[^\n]+/", $config_info)) {
					$config_info = preg_replace("/(define\(\"SHOP_ORDER_NOTIFIES\"[^\n]+)/", "$1\n\ndefine(\"SITE_SUBSCRIPTIONS\", false);\n" , $config_info);
				}
				// Append to file
				else {
					$config_info .= "\ndefine(\"SITE_SUBSCRIPTIONS\", false);\n";
				}
			}
			$this->process(["success" => true, "message" => "Missing SITE_SUBSCRIPTIONS constant added to config.php"], false);
		}
		// Check line position
		if(preg_match("/define\(\"SITE_SUBSCRIPTIONS\"[^\n]+/", $config_info, $line_match)) {
			$config_info = preg_replace("/define\(\"SITE_SUBSCRIPTIONS\"[^\n]+/", "" , $config_info);
			$config_info = preg_replace("/(define\(\"SHOP_ORDER_NOTIFIES\"[^\n]+)/", "$1\n\n".$line_match[0]."\n" , $config_info);
		}


		// Check members constant
		if(!defined("SITE_MEMBERS")) {
			// Line is commented out
			if(preg_match("/\/\/[ ]*define\(\"SITE_MEMBERS\"/", $config_info)) {
				$config_info = preg_replace("/\/\/[ ]*define\(\"SITE_MEMBERS\", [^\)]+/", "define(\"SITE_MEMBERS\", false" , $config_info);
			}
			// Insert line
			else {
				// Insert after SITE_EMAIL
				if(preg_match("/define\(\"SITE_SUBSCRIPTIONS\"[^\n]+/", $config_info)) {
					$config_info = preg_replace("/(define\(\"SITE_SUBSCRIPTIONS\"[^\n]+)/", "$1\n\ndefine(\"SITE_MEMBERS\", false);\n" , $config_info);
				}
				// Append to file
				else {
					$config_info .= "\ndefine(\"SITE_MEMBERS\", false);\n";
				}
			}
			$this->process(["success" => true, "message" => "Missing SITE_MEMBERS constant added to config.php"], false);
		}
		// Check line position
		if(preg_match("/define\(\"SITE_MEMBERS\"[^\n]+/", $config_info, $line_match)) {
			$config_info = preg_replace("/define\(\"SITE_MEMBERS\"[^\n]+/", "" , $config_info);
			$config_info = preg_replace("/(define\(\"SITE_SUBSCRIPTIONS\"[^\n]+)/", "$1\n\n".$line_match[0]."\n" , $config_info);
		}


		// Check collect notifications constant
		if(!defined("SITE_COLLECT_NOTIFICATIONS")) {
			// Line is commented out
			if(preg_match("/\/\/[ ]*define\(\"SITE_COLLECT_NOTIFICATIONS\"/", $config_info)) {
				$config_info = preg_replace("/\/\/[ ]*define\(\"SITE_COLLECT_NOTIFICATIONS\"/", "define(\"SITE_COLLECT_NOTIFICATIONS\"" , $config_info);
			}
			// Insert line
			else {
				// Insert after SITE_EMAIL
				if(preg_match("/define\(\"SITE_MEMBERS\"[^\n]+/", $config_info)) {
					$config_info = preg_replace("/(define\(\"SITE_MEMBERS\"[^\n]+)/", "$1\n\ndefine(\"SITE_COLLECT_NOTIFICATIONS\", \"50\");\n" , $config_info);
				}
				// Append to file
				else {
					$config_info .= "\ndefine(\"SITE_COLLECT_NOTIFICATIONS\", \"50\");\n";
				}
			}
			$this->process(["success" => true, "message" => "Missing SITE_COLLECT_NOTIFICATIONS constant added to config.php"], false);
		}
		// Check line position
		if(preg_match("/define\(\"SITE_COLLECT_NOTIFICATIONS\"[^\n]+/", $config_info, $line_match)) {
			$config_info = preg_replace("/define\(\"SITE_COLLECT_NOTIFICATIONS\"[^\n]+/", "" , $config_info);
			$config_info = preg_replace("/(define\(\"SITE_MEMBERS\"[^\n]+)/", "$1\n\n".$line_match[0]."\n" , $config_info);
		}


		// Remove leftover comments
		$config_info = preg_replace("/([^:])\/\/[^\n]*/", "$1", $config_info);

		// Remove repeated newlines
		$config_info = preg_replace("/(\n){3,100}/", "\n\n", $config_info);


		file_put_contents(LOCAL_PATH."/config/config.php", trim($config_info)."\n\n");

	}

	// Code update
	function updateConnectFilesSyntax08() {

		// correct mail config
		if(file_exists(LOCAL_PATH."/config/connect_mail.php")) {
			$config_info = file_get_contents(LOCAL_PATH."/config/connect_mail.php");

			// Remove closing PHP tag
			if(preg_match("/\?\>[ \n\t]+$/", $config_info)) {
				$config_info = preg_replace("/\?\>[ \n\t]+$/", "\n", $config_info);

				$this->process(["success" => true, "message" => "connect_mail.php updated"], false);
			}

			file_put_contents(LOCAL_PATH."/config/connect_mail.php", trim($config_info)."\n\n");
		}

		// correct db config
		if(file_exists(LOCAL_PATH."/config/connect_db.php")) {
			$config_info = file_get_contents(LOCAL_PATH."/config/connect_db.php");

			// Remove closing PHP tag
			if(preg_match("/\?\>[ \n\t]+$/", $config_info)) {
				$config_info = preg_replace("/\?\>[ \n\t]+$/", "\n", $config_info);

				$this->process(["success" => true, "message" => "connect_db.php updated"], false);
			}

			file_put_contents(LOCAL_PATH."/config/connect_db.php", trim($config_info)."\n\n");
		}

		// correct payment config
		if(file_exists(LOCAL_PATH."/config/connect_payment.php")) {
			$config_info = file_get_contents(LOCAL_PATH."/config/connect_payment.php");

			// Remove closing PHP tag
			if(preg_match("/\?\>[ \n\t]+$/", $config_info)) {
				$config_info = preg_replace("/\?\>[ \n\t]+$/", "\n", $config_info);

				$this->process(["success" => true, "message" => "connect_payment.php updated"], false);
			}

			file_put_contents(LOCAL_PATH."/config/connect_payment.php", trim($config_info)."\n\n");
		}

	}

	// Code update
	function updateModelDeclarations08() {

		$IC = new Items();
		$fs = new FileSystem();

		// Look through itemtype classes and check that model is wellformed
		$itemtype_classes = $fs->files(LOCAL_PATH."/classes/items", ["allow_extensions" => "php"]);
		$models_changed = false;

		if($itemtype_classes) {
			foreach($itemtype_classes as $itemtype_class) {

				preg_match("/type\.([A-Za-z]+)\.class/", $itemtype_class, $match);
				$itemtype["name"] = $match[1];
				$itemtype["classfile"] = $itemtype_class;

				$model = $IC->typeObject($itemtype["name"]);
				$model_entities = $model->getModel();

				$class_content = file_get_contents($itemtype["classfile"]);
				$class_updated = false;

				foreach($model_entities as $name => $model_entity) {
					// $this->dump($name);
					// $this->dump($model_entity);

					if(!isset($model_entity["type"])) {
						if($name == "html") {
							$type = "html";
						}
						else if($name == "mediae" || $name == "single_media") {
							$type = "files";
						}
						else if($name == "published_at") {
							$type = "datetime";
						}
						else {
							$this->process(["success" => false, "message" => "MISSING MODEL TYPE FOR UNKNOWN ENTITY ($name) IN " . $itemtype["classfile"]], true);
						}

						$class_content = preg_replace("/(\\\$this-\>addToModel\(\"".$name."\"[^\n]+)/", "$1\n\t\t\t\"type\" => \"$type\",", $class_content);
						$class_updated = true;
					}

					if(!isset($model_entity["label"])) {
						if($name == "html") {
							$label = "HTML";
						}
						else if($name == "mediae" || $name == "single_media") {
							$label = "Add media here";
						}
						else if($name == "published_at") {
							$label = "Publish date (yyyy-mm-dd hh:mm)";
						}
						else {
							$this->process(["success" => false, "message" => "MISSING MODEL LABEL FOR UNKNOWN ENTITY ($name) IN " . $itemtype["classfile"]], true);
						}

						$class_content = preg_replace("/(\\\$this-\>addToModel\(\"".$name."\"[^\n]+\n\t\t\t\"type\"[^\n]+)/", "$1\n\t\t\t\"label\" => \"$label\",", $class_content);
						$class_updated = true;
					}

					if(!isset($model_entity["hint_message"])) {
						if($name == "html") {
							$hint = "Write!";
						}
						else if($name == "mediae" || $name == "single_media") {
							$hint = "Add images or videos here. Use png or jpg.";
						}
						else if($name == "published_at") {
							$hint = "Publish date (yyyy-mm-dd hh:mm)";
						}
						else {
							$this->process(["success" => false, "message" => "MISSING MODEL HINT MESSAGE FOR UNKNOWN ENTITY ($name) IN " . $itemtype["classfile"]], true);
						}

						if(preg_match("/\\\$this-\>addToModel\(\"".$name."[^$]+?(?=\)\))/", $class_content, $insert_after)) {
							// Check for comma in end of insertion point
							if(preg_match("/,$/", trim($insert_after[0]))) {
								$class_content = preg_replace("/(\\\$this-\>addToModel\(\"".$name."\"[^$]+?(?=\)\)))/", "$1\t\"hint_message\" => \"$hint\",\n\t\t", $class_content);
							}
							else {
								$class_content = preg_replace("/(\\\$this-\>addToModel\(\"".$name."\"[^$]+?(?=\)\)))/", trim($insert_after[0]).",\n\t\t\t\"hint_message\" => \"$hint\",\n\t\t", $class_content);
							}
							$class_updated = true;
						}
					}

					if(!isset($model_entity["error_message"])) {
						if($name == "html") {
							$error = "No words? How weird.";
						}
						else if($name == "mediae" || $name == "single_media") {
							$error = "Media does not fit requirements.";
						}
						else if($name == "published_at") {
							$error = "Datetime must be of format yyyy-mm-dd hh:mm";
						}
						else {
							$error = ucfirst($name)." is invalid.";
						}

						if(preg_match("/\\\$this-\>addToModel\(\"".$name."[^$]+?(?=\)\))/", $class_content, $insert_after)) {
							// Check for comma in end of insertion point
							if(preg_match("/,$/", trim($insert_after[0]))) {
								$class_content = preg_replace("/(\\\$this-\>addToModel\(\"".$name."\"[^$]+?(?=\)\)))/", "$1\t\"error_message\" => \"$error\",\n\t\t", $class_content);
							}
							else {
								$class_content = preg_replace("/(\\\$this-\>addToModel\(\"".$name."\"[^$]+?(?=\)\)))/", trim($insert_after[0]).",\n\t\t\t\"error_message\" => \"$error\",\n\t\t", $class_content);
							}
							$class_updated = true;
						}
					}

					// Special properties that might be missing
					if($name == "html") {
						if(!isset($model_entity["allowed_tags"])) {
							$class_content = preg_replace("/(\\\$this-\>addToModel\(\"".$name."\"[^$]+?(?=\"hint_message\" \=\>))/", "$1\"allowed_tags\" => \"p,h3,h4,download\",\n\t\t\t", $class_content);
							$class_updated = true;
						}
					}
					if($name == "mediae") {
						if(!isset($model_entity["max"])) {
							$class_content = preg_replace("/(\\\$this-\>addToModel\(\"".$name."\"[^$]+?(?=\"hint_message\" \=\>))/", "$1\"max\" => 20,\n\t\t\t", $class_content);
							$class_updated = true;
						}
					}
					if($name == "single_media") {
						if(!isset($model_entity["max"])) {
							$class_content = preg_replace("/(\\\$this-\>addToModel\(\"".$name."\"[^$]+?(?=\"hint_message\" \=\>))/", "$1\"max\" => 1,\n\t\t\t", $class_content);
							$class_updated = true;
						}
					}
					if($name == "mediae" || $name == "single_media") {
						if(!isset($model_entity["allowed_formats"])) {
							$class_content = preg_replace("/(\\\$this-\>addToModel\(\"".$name."\"[^$]+?(?=\"hint_message\" \=\>))/", "$1\"max\" => \"png,jpg\",\n\t\t\t", $class_content);
							$class_updated = true;
						}
					}

				}


				// Catch previously fully inherited types by checking content of edit template
				// html / mediae / single_media / published_at
				if(file_exists(LOCAL_PATH."/templates/janitor/".$itemtype["name"]."/edit.php")) {
					$template_content = file_get_contents(LOCAL_PATH."/templates/janitor/".$itemtype["name"]."/edit.php");

					if(preg_match("/(inputHTML|input)\(\"html\"/", $template_content)) {
						if(!isset($model_entities["html"])) {
							$class_content = preg_replace("/(\\\$this-\>addToModel\([^$]+\)\);)/", "$1\n\n\t\t// HTML\n\t\t\$this->addToModel(\"html\", array(\n\t\t\t\"type\" => \"html\",\n\t\t\t\"label\" => \"HTML\",\n\t\t\t\"allowed_tags\" => \"p,h2,h3,h4,ul,ol,download,jpg,png\",\n\t\t\t\"hint_message\" => \"Write!\",\n\t\t\t\"error_message\" => \"No words? How weird.\",\n\t\t));", $class_content, 1);
							$class_updated = true;
						}
					}

					if(preg_match("/editMediae\(\\\$item/", $template_content)) {

						// Check for custom variant
						if(preg_match_all("/editMediae\(\\\$item[^$]+\"variant\" \=\> \"([a-zA-Z0-9\-_]+)\"/", $template_content, $variant_match)) {
							$media_variants = $variant_match[1];
							foreach($media_variants as $media_variant) {

								if(!isset($model_entities[$media_variant])) {
									$class_content = preg_replace("/(\\\$this-\>addToModel\([^$]+\)\);)/", "$1\n\n\t\t// $media_variant media\n\t\t\$this->addToModel(\"$media_variant\", array(\n\t\t\t\"type\" => \"files\",\n\t\t\t\"label\" => \"Add media here\",\n\t\t\t\"max\" => 20,\n\t\t\t\"allowed_formats\" => \"jpg,png\",\n\t\t\t\"hint_message\" => \"Add images or videos here. Use png or jpg.\",\n\t\t\t\"error_message\" => \"Media does not fit requirements.\",\n\t\t));", $class_content, 1);
									$class_updated = true;
								}

							}
						}
						// Default variant
						else if(!isset($model_entities["mediae"])) {
							$class_content = preg_replace("/(\\\$this-\>addToModel\([^$]+\)\);)/", "$1\n\n\t\t// Mediae\n\t\t\$this->addToModel(\"mediae\", array(\n\t\t\t\"type\" => \"files\",\n\t\t\t\"label\" => \"Add media here\",\n\t\t\t\"max\" => 20,\n\t\t\t\"allowed_formats\" => \"jpg,png\",\n\t\t\t\"hint_message\" => \"Add images or videos here. Use png or jpg.\",\n\t\t\t\"error_message\" => \"Media does not fit requirements.\",\n\t\t));", $class_content, 1);
							$class_updated = true;
						}
					}

					if(preg_match("/editSingleMedia\(\\\$item/", $template_content)) {

						// Check for custom variant
						if(preg_match_all("/editSingleMedia\(\\\$item[^$]+\"variant\" \=\> \"([a-zA-Z0-9\-_]+)\"/", $template_content, $variant_match)) {
							$media_variants = $variant_match[1];
							foreach($media_variants as $media_variant) {

								if(!isset($model_entities[$media_variant])) {
									$class_content = preg_replace("/(\\\$this-\>addToModel\([^$]+\)\);)/", "$1\n\n\t\t// $media_variant media\n\t\t\$this->addToModel(\"$media_variant\", array(\n\t\t\t\"type\" => \"files\",\n\t\t\t\"label\" => \"Add media here\",\n\t\t\t\"max\" => 1,\n\t\t\t\"allowed_formats\" => \"jpg,png\",\n\t\t\t\"hint_message\" => \"Add images or videos here. Use png or jpg.\",\n\t\t\t\"error_message\" => \"Media does not fit requirements.\",\n\t\t));", $class_content, 1);
									$class_updated = true;
								}

							}
						}
						// Default variant
						else if(!isset($model_entities["single_media"])) {
							$class_content = preg_replace("/(\\\$this-\>addToModel\([^$]+\)\);)/", "$1\n\n\t\t// Single media\n\t\t\$this->addToModel(\"single_media\", array(\n\t\t\t\"type\" => \"files\",\n\t\t\t\"label\" => \"Add media here\",\n\t\t\t\"max\" => 1,\n\t\t\t\"allowed_formats\" => \"jpg,png\",\n\t\t\t\"hint_message\" => \"Add images or videos here. Use png or jpg.\",\n\t\t\t\"error_message\" => \"Media does not fit requirements.\",\n\t\t));", $class_content, 1);
							$class_updated = true;
						}
					}

					if(preg_match("/input\(\"published_at\"/", $template_content)) {
						if(!isset($model_entities["published_at"])) {
							$class_content = preg_replace("/(\\\$this-\>addToModel\([^$]+\)\);)/", "$1\n\n\t\t// Published at\n\t\t\$this->addToModel(\"published_at\", array(\n\t\t\t\"type\" => \"datetime\",\n\t\t\t\"label\" => \"Publish date (yyyy-mm-dd hh:mm)\",\n\t\t\t\"hint_message\" => \"Publishing date of the item. Leave empty for current time.\",\n\t\t\t\"error_message\" => \"Datetime must be of format yyyy-mm-dd hh:mm\",\n\t\t));", $class_content, 1);
							$class_updated = true;
						}
					}

				}


				file_put_contents($itemtype["classfile"], $class_content);

				if($class_updated) {
					$this->process(["success" => true, "message" => $itemtype["name"]." MODEL UPDATED"], true);
					$models_changed = true;
				}

			}
		}
		
		if($models_changed) {
			$this->process(["success" => false, "message" => "MODELS HAVE BEEN UPDATED – REFRESH THIS PAGE TO FINISH UPGRADE"], false);
			throw new Exception();
		}
	}
	
	// Code update
	function updateSyntaxTo08() {

		$fs = new FileSystem();
		// get all php files in theme
		$php_files = $fs->files(LOCAL_PATH."/templates", ["allow_extensions" => "php", "include_tempfiles" => true]);
		foreach($php_files as $php_file) {

			$is_code_altered = false;
			$code_lines = file($php_file);
			foreach($code_lines as $line_no => $line) {

				// Change $JML->jsMedia to $HTML->jsMedia
				if(preg_match("/\\\$JML\-\>jsMedia\(/", $line)) {

					$line = preg_replace("/\\\$JML\-\>jsMedia\(/", "\$HTML->jsMedia(", $line);
					if($code_lines[$line_no] != $line) {
						$code_lines[$line_no] = $line;
						$this->process(["success" => false, "message" => "FOUND AND REPLACED OLD CODE (JML->jsMedia) IN " . $php_file . " in line " . ($line_no+1)]);
						$is_code_altered = true;
					}
					else {
						$this->process(["success" => false, "message" => "FOUND OLD CODE (JML->jsMedia) IN " . $php_file . " in line " . ($line_no+1)], true);
					}
				}

				// Change $JML->jsData to $HTML->jsData
				if(preg_match("/\\\$JML\-\>jsData\(/", $line)) {

					$line = preg_replace("/\\\$JML\-\>jsData\(/", "\$HTML->jsData(", $line);
					if($code_lines[$line_no] != $line) {
						$code_lines[$line_no] = $line;
						$this->process(["success" => false, "message" => "FOUND AND REPLACED OLD CODE (JML->jsData) IN " . $php_file . " in line " . ($line_no+1)]);
						$is_code_altered = true;
					}
					else {
						$this->process(["success" => false, "message" => "FOUND OLD CODE (JML->jsData) IN " . $php_file . " in line " . ($line_no+1)], true);
					}
				}

				// Change $JML->jsMedia to $HTML->jsMedia
				if(preg_match("/\-\>inputHTML\(/", $line)) {

					$line = preg_replace("/\-\>inputHTML\(/", "->input(", $line);
					if($code_lines[$line_no] != $line) {
						$code_lines[$line_no] = $line;
						$this->process(["success" => false, "message" => "FOUND AND REPLACED OLD CODE (inputHTML) IN " . $php_file . " in line " . ($line_no+1)]);
						$is_code_altered = true;
					}
					else {
						$this->process(["success" => false, "message" => "FOUND OLD CODE (inputHTML) IN " . $php_file . " in line " . ($line_no+1)], true);
					}
				}

			}

			// Should we write
			if($is_code_altered) {
				file_put_contents($php_file, implode("", $code_lines));
			}

		}

	}
	

	function updateUserSubscriptionPaymentMethods08() {
		
		if((defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS)) {

			$IC = new Items();
			$query = new Query();

			// Move payment method from user subscriptions table to payment methods
			$user_item_subscription_table = $this->tableInfo(SITE_DB.".user_item_subscriptions");
			if($user_item_subscription_table && isset($user_item_subscription_table["columns"]["payment_method"])) {

				$this->process(["message" => "PAYMENT METHOD UPGRADE user_item_subscriptions – MOVE TO user_payment_methods", "success" => true], true);

				// Get all subscriptions
				$sql = "SELECT * FROM ".SITE_DB.".user_item_subscriptions";
				if($query->sql($sql)) {
					$subscriptions = $query->results();

					foreach($subscriptions as $subscription) {

						// if subscription has payment_method, then move info to payment methods
						if($subscription["payment_method"]) {

							$sql = "INSERT INTO ".SITE_DB.".user_payment_methods SET user_id = ".$subscription["user_id"].", payment_method_id = ".$subscription["payment_method"].", created_at = '".$subscription["created_at"]."', default_method = 1";
							$query->sql($sql);

						}

					}
				}

				// Remove payment method column
				$this->process($this->dropConstraints(SITE_DB.".user_item_subscriptions", "payment_method"), true);
				$this->process($this->dropKeys(SITE_DB.".user_item_subscriptions", "payment_method"), true);
				$this->process($this->dropColumn(SITE_DB.".user_item_subscriptions", "payment_method"), true);

				cache()->reset("payment_methods");

			}

		}
		
	}

	function addMembershipPriceTypes08() {

		if((defined("SITE_SHOP") && SITE_SHOP)) {

			$query = new Query();
			$IC = new Items();

			// create price types for existing membership items
			$membership_items = $IC->getItems(["itemtype" => "membership", "extend" => true]);
			foreach($membership_items as $membership_item) {
				$membership_item_id = $membership_item["id"];
				$membership_item_name = $membership_item["name"];
				$normalized_membership_item_name = superNormalize(substr($membership_item_name, 0, 60));
				
				$sql = "SELECT * FROM ".UT_PRICE_TYPES." WHERE item_id = $membership_item_id";
				if (!$query->sql($sql)) {

					$sql = "INSERT INTO ".UT_PRICE_TYPES." (item_id, name, description) VALUES($membership_item_id, '$normalized_membership_item_name', 'Price for \\'$membership_item_name\\' members')"; 
					$query->sql($sql);
				}
			}
		}
		
	}

	function updatePaymentTypes08() {

		if((defined("SITE_SHOP") && SITE_SHOP)) {

			$query = new Query();
			$IC = new Items();

			$payment_method_table = $this->tableInfo(UT_PAYMENT_METHODS);
			if($payment_method_table && !isset($payment_method_table["columns"]["state"])) {

				$this->process($this->addColumn(UT_PAYMENT_METHODS, "state", "varchar(10) NULL DEFAULT NULL", "gateway"), true);

				// Move state values from classname column
				$sql = "SELECT * FROM ".UT_PAYMENT_METHODS;
				if ($query->sql($sql)) {

					$payment_methods = $query->results();
					foreach($payment_methods as $payment_method) {

						// Has disabled class
						if($payment_method["classname"] === "disabled") {

							// remove classname
							$sql = "UPDATE ".UT_PAYMENT_METHODS." SET classname = NULL WHERE id = ".$payment_method["id"];
							$query->sql($sql);

						}
						// Does not have disabled class
						else {

							// add public state
							$sql = "UPDATE ".UT_PAYMENT_METHODS." SET state = 'public' WHERE id = ".$payment_method["id"];
							$query->sql($sql);

						}
					}

				}

			}

		}

	}

	function updateMediaeSyntax08() {

		if((defined("SITE_ITEMS") && SITE_ITEMS)) {

			$query = new Query();
			$IC = new Items();
			$fs = new FileSystem();

			// Update mediae tables with extended variant names
			// - cross-reference stored mediae with HTML-value – does all "HTML" mediae exist in HTML-text
			// - fix "free-text" name classVars (containing spaces)
			$sql = "SELECT * FROM ".UT_ITEMS_MEDIAE." ORDER BY item_id";
			// debug([$sql]);
			if($query->sql($sql)) {
				$mediae = $query->results();

				// Ensure items_mediae variant definition has been updated to fit the new variant names
				$this->synchronizeTable("items_mediae");

				// $this->dump($mediae);

				foreach($mediae as $media) {

					// $this->dump("media");
					// $this->dump($media);

					// Get related item
					$item = $IC->getItem(["id" => $media["item_id"], "extend" => true]);

					// Get model for related item
					$item_model = $IC->typeObject($item["itemtype"]);
					$model_entities = $item_model->getModel();
					// $this->dump($model_entities);


					// OLD HTML editor media
					if(preg_match("/^HTML\-/", $media["variant"])) {

						$found = false;
						// debug([$item]);

						// Look for HTML inputs to check values for media occurence
						foreach($item as $name => $value) {


							if(isset($model_entities[$name]) && $model_entities[$name]["type"] === "html") {

								// Is variant used in this HTML input (item can have several HTML inputs)
								if(preg_match("/variant:".$media["variant"]."( |$)/", $value)) {

									$new_variant = "HTMLEDITOR-".$name."-".randomKey(8);
									$new_value = str_replace($media["variant"], $new_variant, $value);
									$new_value = str_replace($media["name"], urlencode($media["name"]), $new_value);
									$sql = "UPDATE ".UT_ITEMS_MEDIAE." SET variant = '".$new_variant."' WHERE id = ".$media["id"];
									// $this->dump($sql);
									if($query->sql($sql)) {

										$sql = "UPDATE ".$item_model->db." SET $name = '".preg_replace("/'/", "\'", $new_value)."' WHERE item_id = ".$media["item_id"];
										// $this->dump($sql);
										if($query->sql($sql)) {

											$fs->copy(PRIVATE_FILE_PATH."/".$media["item_id"]."/".$media["variant"], PRIVATE_FILE_PATH."/".$media["item_id"]."/".$new_variant);
											$fs->removeDirRecursively(PRIVATE_FILE_PATH."/".$media["item_id"]."/".$media["variant"]);
											$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$media["item_id"]."/".$media["variant"]);
										}
									}

									$media["variant"] = $new_variant;
									$found = true;

								}

							}

						}

						// Media not found – must be a leftover – clean up
						if(!$found) {

							$sql = "DELETE FROM ".UT_ITEMS_MEDIAE." WHERE id = ".$media["id"];
							// debug([$sql]);
							if($query->sql($sql)) {
								$fs->removeDirRecursively(PRIVATE_FILE_PATH."/".$media["item_id"]."/".$media["variant"]);
								$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$media["item_id"]."/".$media["variant"]);

								$this->process(array("success" => true, "message" => "Deleted HTML media remnant: " . $media["id"]), true);
								$media = false;
								continue;
							}

						}

					}
					// Regular media
					else if(!preg_match("/^HTMLEDITOR\-/", $media["variant"])) {

						$found = false;
						$file_inputs = [];
					
						// Look for HTML inputs to check values for media occurence
						foreach($model_entities as $name => $value) {

							// Entity name matches variant
							if(isset($model_entities[$name]) && ($name === $media["variant"] || preg_match("/^".$name."\-/", $media["variant"]))) {

								$found = true;

							}

						}
					
						if(!$found) {

							// If not found, then assign media to mediae input
							$new_variant = "mediae-".randomKey(8);
							$sql = "UPDATE ".UT_ITEMS_MEDIAE." SET variant = '".$new_variant."' WHERE id = ".$media["id"];
							// $this->dump($sql);

							if($query->sql($sql)) {

								// Copy to new location and remove old
								// debug([PRIVATE_FILE_PATH."/".$media["item_id"]."/".$media["variant"], PRIVATE_FILE_PATH."/".$media["item_id"]."/".$new_variant]);
								$fs->copy(PRIVATE_FILE_PATH."/".$media["item_id"]."/".$media["variant"], PRIVATE_FILE_PATH."/".$media["item_id"]."/".$new_variant);
								$fs->removeDirRecursively(PRIVATE_FILE_PATH."/".$media["item_id"]."/".$media["variant"]);
								$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$media["item_id"]."/".$media["variant"]);

								$this->process(array("success" => true, "message" => "Assign media to mediae-variant: " . $media["id"]), true);

							}
							$media = false;
							continue;

						}

					}
					// UPDATED HTML media – CHECK for leftovers (deleted mediea, that wasn't deleted properly)
					else if(preg_match("/^HTMLEDITOR\-/", $media["variant"])) {

						$found = false;
						// debug([$item]);

						// Look for HTML inputs to check values for media occurence
						foreach($item as $name => $value) {

							if(isset($model_entities[$name]) && $model_entities[$name]["type"] === "html") {

								// Is variant used in this HTML input (item can have several HTML inputs)
								if(preg_match("/variant:".$media["variant"]."( |$)/", $value)) {

									$found = true;

								}

							}

						}

						// Media not found – must be a leftover – clean up
						if(!$found) {

							// debug(["SHOULD DELETE:" . $media["variant"]]);
							$sql = "DELETE FROM ".UT_ITEMS_MEDIAE." WHERE id = ".$media["id"];
							// debug([$sql]);
							if($query->sql($sql)) {
								$fs->removeDirRecursively(PRIVATE_FILE_PATH."/".$media["item_id"]."/".$media["variant"]);
								$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$media["item_id"]."/".$media["variant"]);

								$this->process(array("success" => true, "message" => "Deleted HTMLEDITOR media remnant: " . $media["id"]), true);
								$media = false;
								continue;
							}


						}
						// else {
						// 	debug(["SHOULD KEEP:" . $media["id"] . ", " . $media["variant"]]);
						//
						// }
					}


					// ADJUST 0 WIDTH AND HEIGHT VALUES TO DEFAULT (NULL)
					if(!$media["width"] || !$media["height"]) {

						// SHOULD NOT HAVE WIDTH/HEIGHT
						if(!preg_match("/^(jpg|png|gif|mp4|mov)$/", $media["format"])) {
							$query->sql("UPDATE ".UT_ITEMS_MEDIAE." SET width = DEFAULT, height = DEFAULT WHERE id = ".$media["id"]);
						}

						// DATA / TYPE MISMATCH
						else {
							$this->process(["message" => "Media property mismatch (missing width or height on media_id: ".$media["id"].")", "success" => false], true);
						}

					}

					// Rename previously uncompressed files (downloadable files)
					$files = $fs->files(PRIVATE_FILE_PATH."/".$media["item_id"]."/".$media["variant"]);
					// $this->dump("files:");
					// $this->dump($files);
					if($files && (!file_exists(PRIVATE_FILE_PATH."/".$media["item_id"]."/".$media["variant"]."/".$media["format"]) || count($files) > 1)) {
						if(count($files) > 1) {
							$this->process(["message" => "MULTIPLE PRIVATE FILES FOR media_id: ".$media["id"]." - SOMETHING IS WRONG)", "success" => false], true);
						}
						else {
							$file = array_pop($files);

							// zip, pdf, jpg, gif, png file
							if(preg_match("/\.(zip|pdf|png|gif|jpg)$/", $file, $match)) {

								// Rename
								if($fs->copy($file, PRIVATE_FILE_PATH."/".$media["item_id"]."/".$media["variant"]."/".$media["format"])) {
									unlink($file);

									// Just rename
									$this->process(["success" => true, "message" => "RENAMED PRIVATE FILE: media_id: ".$media["id"]." - ".basename($file)." -> ".$match[1]], true);
								}
								// Rename failed
								else {
									$this->process(["success" => false, "message" => "FAILED RENAMING PRIVATE FILE: media_id: ".$media["id"]." - ".basename($file)." -> ".$match[1]], true);
								}

							}
							else {

								// Zip file as "zip"
								$zip = new ZipArchive();
								$zip->open(PRIVATE_FILE_PATH."/".$media["item_id"]."/".$media["variant"]."/zip", ZipArchive::CREATE);
								$zip->addFile($file, basename($file));
								$zip->close();

								if(file_exists(PRIVATE_FILE_PATH."/".$media["item_id"]."/".$media["variant"]."/zip")) {
									unlink($file);
									$this->process(["success" => true, "message" => "REPACKAGED PRIVATE FILE: media_id: ".$media["id"]." - ".basename($file)." -> zip"], true);
								}
								else {
									$this->process(["success" => false, "message" => "FAILED REPACKAGING PRIVATE FILE: media_id: ".$media["id"]." - ".basename($file)], true);
								}

							}

						}

					}
					// Missing file
					else if(!$files) {

						$this->process(["success" => false, "message" => "NOT FOUND - PRIVATE FILE MISSING: media_id: ".$media["id"].", item_id: ".$media["item_id"]], false);

					}

				}

				$this->process(["message" => "Media updated", "success" => true]);
			}
		}
	}

	function addPaymentId08() {

		// Payments
		if((defined("SITE_SHOP") && SITE_SHOP)) {
			$shop_payments_table = $this->tableInfo(SITE_DB.".shop_payments");
			if($shop_payments_table && isset($shop_payments_table["columns"]["payment_method"])) {

				$this->process($this->renameColumn(SITE_DB.".shop_payments", "payment_method", "payment_method_id"), true);

			}
		}

	}

	function updateItemPrices08() {

		// Update item prices with new type_id's for membership prices
		if((defined("SITE_SHOP") && SITE_SHOP)) {

			$IC = new Items();
			$query = new Query();

			// Move payment method from user subscriptions table to payment methods
			$item_prices_table = $this->tableInfo(UT_ITEMS_PRICES);
			if($item_prices_table && isset($item_prices_table["columns"]["type"])) {


				$sql = "SELECT id, type FROM ".UT_ITEMS_PRICES;
				if($query->sql($sql)) {
					$type_prices = $query->results();
			
					foreach($type_prices as $index => $type_price) {

						if($type_price["type"] == "default") {
							$type_prices[$index]["type_id"] = 1;
						}
						elseif($type_price["type"] == "offer") {
							$type_prices[$index]["type_id"] = 2;
						}
						elseif($type_price["type"] == "bulk") {
							$type_prices[$index]["type_id"] = 3;
						}
					}
			
					$this->synchronizeTable("items_prices");

					$sql = "SELECT id, type_id FROM ".UT_ITEMS_PRICES;
					if($query->sql($sql)) {

						$type_id_prices = $query->results();

						foreach($type_id_prices as $index => $type_id_price) {

							$matching_type_prices_index = array_search($type_id_price["id"], array_column($type_prices, "id"));

							$type_id = $type_prices[$matching_type_prices_index]["type_id"];

							$sql = "UPDATE ".UT_ITEMS_PRICES." SET type_id = $type_id WHERE id = ".$type_id_price["id"];
							$query->sql($sql);

						}
					}

				}

			}

		}
	}

	function updateActivationReminders08() {

		if(defined("SITE_SIGNUP") && SITE_SIGNUP) {

			$query = new Query();

			// VERIFICATION LINKS 
			$user_log_activation_reminders = $this->tableInfo(SITE_DB.".user_log_activation_reminders");
			if ($user_log_activation_reminders) {
				$this->process($this->renameColumn(SITE_DB.".user_log_activation_reminders", "created_at", "reminded_at"), true);
				$this->process($this->addColumn(SITE_DB.".user_log_activation_reminders", "username_id", "int(11) DEFAULT NULL", "user_id"), true);


				// retrieve username_ids from user_ids and insert them in the new username_id column

				$count = 0;
				$limit = 5000;
				$sql = "SELECT user_id FROM ".SITE_DB.".user_log_activation_reminders GROUP BY user_id LIMIT $count, $limit";
				// debug([$sql]);
				$query->sql($sql);
				$user_ids = $query->results("user_id");

				while($user_ids) {

					foreach ($user_ids as $user_id) {
						$query->sql("SELECT id FROM ".SITE_DB.".user_usernames WHERE type = 'email' AND user_id = $user_id");
						$username_id = $query->result(0, "id");
						if ($username_id) {
							$query->sql("UPDATE ".SITE_DB.".user_log_activation_reminders SET username_id = $username_id WHERE user_id = $user_id");
						}
						else {
							$query->sql("DELETE FROM ".SITE_DB.".user_log_activation_reminders WHERE user_id = $user_id");
						}
					}

					$count += $limit;

					$sql = "SELECT user_id FROM ".SITE_DB.".user_log_activation_reminders GROUP BY user_id LIMIT $count, $limit";
					// debug([$sql]);
					$query->sql($sql);
					$user_ids = $query->results("user_id");

				}

				$this->process($this->renameTable(SITE_DB.".user_log_activation_reminders", "user_log_verification_links"), true);

			}

		}

	}

	function addBillingNameToOrders08() {
		
		// Add billing_name from user info if not already set
		if((defined("SITE_SHOP") && SITE_SHOP)) {

			$IC = new Items();
			$query = new Query();

			$count = 0;
			$limit = 5000;
			$sql = "SELECT id, user_id, billing_name FROM ".SITE_DB.".shop_orders LIMIT $count, $limit";
			// debug([$sql]);
			$query->sql($sql);
			$orders = $query->results();

			while($orders) {

				foreach($orders as $order) {

					if(!$order["billing_name"]) {
						$user = $UC->getUsers(["user_id" => $order["user_id"]]);

						// create base data update sql
						$sql = "UPDATE ".$SC->db_orders." SET ";

						if($user["firstname"] && $user["lastname"]) {
							$sql .= "billing_name='".prepareForDB($user["firstname"]) ." ". prepareForDB($user["lastname"])."'";
						}
						else {
							$sql .= "billing_name='".prepareForDB($user["nickname"])."'";
						}

						$sql .= " WHERE id=".$order["id"];
						$query->sql($sql);
					}

				}

				$count += $limit;

				$sql = "SELECT id, user_id, billing_name FROM ".SITE_DB.".shop_orders LIMIT $count, $limit";
				// debug([$sql]);
				$query->sql($sql);
				$orders = $query->results();


			}

		}

	}

	function removeDeletedItemsFromOrderItems08() {
		
		// Add billing_name from user info if not already set
		if((defined("SITE_SHOP") && SITE_SHOP)) {

			$query = new Query();

			// set item_id = NULL for orphaned order items
			$sql = "UPDATE ".SITE_DB.".shop_order_items AS order_items SET item_id = NULL WHERE NOT EXISTS (SELECT * FROM ".SITE_DB.".items AS items WHERE items.id = order_items.item_id)";
			$query->sql($sql);

		}

	}

	// Replace all user-emails with ADMIN_EMAIL
	// to create local dev version without triggering emails to real users
	function replaceEmails($action) {

		$query = new Query();
		mailer()->init_adapter();

		$replacement = getPost("replacement", "value");
		$exclude = getPost("exclude", "value");
		$exclude_arr = explode(",", $exclude);
		$exclude = "'".implode("', '", $exclude_arr)."'";

		$user_id_suffix = getPost("user_id_suffix", "value");

		if($query->sql("SELECT * FROM ".SITE_DB.".user_usernames WHERE type='email'".($exclude ? " AND username NOT IN ($exclude)": ""))) {
			$usernames = $query->results();
			foreach($usernames as $username) {

				if($user_id_suffix) {
					$user_replacement = substr_replace($replacement, "+".$username["user_id"], strpos($replacement, "@"), 0);
				}
				else {
					$user_replacement = $replacement;
				}

				if(!$query->sql("UPDATE ".SITE_DB.".user_usernames SET username = '".$user_replacement."' WHERE id = ".$username["id"])) {

					return ["message" => "Something went wrong."];
				}
			}
			return array("message" => "Success! ". (count($usernames) - ($exclude ? 1:0)). " email addresses were replaced.");
		}

		return false;

	}

	function bulkItemRemoval($action) {
		
		global $page;


		// Get posted values to make them available for models
		// $this->getPostedEntities();


		// does values validate

		$itemtype = getPost("itemtype");
		$keep = getPost("keep");
		$real = getPost("real");

		$options = ["limit" => 5000];
		if($itemtype) {
			$options["itemtype"] = $itemtype;
		}

		$IC = new Items();
		$query = new Query();


		// $time = time();
		$items = $IC->getItems($options);
		// $t2 = time() - $time;
		// print $t2."<br>\n";

		$batch_items = count($items);

		// get total count of items
		$sql = "SELECT COUNT(id) as total FROM ".UT_ITEMS.($itemtype ? " WHERE itemtype='$itemtype'" : "");
		$query->sql($sql);
		$total_items = $query->result(0, "total");


		// $t3 = time() - $time;
		// print $t3."<br>\n";
//		set_time_limit(0);

//		print count($items) .  "<br>\n";

		if(is_numeric($keep)) {
			// print $total_items ."-". count($items)."<br>\n";
			while($items && ($batch_items - count($items) < $keep)) {
				// print $total_items ."-". count($items)."<br>\n";
				array_splice($items, rand(0, count($items) - 1), 1);
			}
		}
		// $t4 = time() - $time;
		// print $t4."<br>\n";


//		print count($items) .  "<br>\n";
		if($real) {

			foreach($items as $item) {
				$model = $IC->typeObject($item["itemtype"]);
				$model->delete(["delete", $item["id"]]);
				unset($_POST);
			}

			// get total count of items
			$sql = "SELECT COUNT(id) as total FROM ".UT_ITEMS.($itemtype ? " WHERE itemtype='$itemtype'" : "");
			$query->sql($sql);
			$total_items = $query->result(0, "total");

			return array("message" => count($items) . " deleted. You now only have ".$total_items." items left.");
		}
		else {
			return array("message" => count($items) . " out of " . $total_items . " items match your criterias and will be removed if you check the &quot;real&quot;. You'll then have ".($total_items - count($items))." left");
		}

//		print_r($items);

	}



	// DB HELPER functions


	// get table info
	// parse create table syntax and return array of structure
	function tableInfo($db_table) {
		
		$query = new Query();
		
		$sql = "SHOW CREATE TABLE ".$db_table;
		if($query->sql($sql)) {
			
			$create_syntax = $query->result(0);
//			print_r($create_syntax);

			if($create_syntax["Create Table"]) {

				return $this->parseCreateSQL($create_syntax["Create Table"]);

			}

		}

		return false;

	}

	// Parse create table syntax into array structure
	function parseCreateSQL($sql) {

		// Split sql into individual lines
		$table_details = explode("\n", $sql);

		// Prepare array for SQL structure
		$table_info = array("columns" => array(), "primary_key" => false, "unique_keys" => false, "keys" => array(), "constraints" => array());

		foreach($table_details as $detail) {

			// remove trailing comma's
			$detail = preg_replace("/,$/", "", trim($detail));

			// COLUMN
			if(preg_match("/^`/", $detail)) {
//				print "column:" . $detail."\n";
				
				preg_match("/^`(.+)` (.+)/", $detail, $column);
				if(count($column) == 3) {
					$table_info["columns"][$column[1]] = $column[2];
				}

			}

			// PRIMARY KEY
			else if(preg_match("/^PRIMARY KEY/", $detail)) {
//				print "pkey:" . $detail."\n";

				preg_match("/`(.+)`/", $detail, $pkey);
				if(count($pkey) == 2) {
					$table_info["primary_key"] = $pkey[1];
				}

			}

			// UNIQUE KEY
			else if(preg_match("/^UNIQUE KEY/", $detail)) {
//				print "ukey:" . $detail."\n";

				preg_match("/`(.+)` \(`(.+)`\)/", $detail, $ukey);
				if(count($ukey) == 3) {
					$table_info["unique_keys"][$ukey[2]][] = $ukey[1];
				}

			}

			// KEY
			else if(preg_match("/^KEY/", $detail)) {
//				print "key:" . $detail."\n";
				
				preg_match("/`(.+)` \(`(.+)`\)/", $detail, $key);
				if(count($key) == 3) {
					$table_info["keys"][$key[2]][] = $key[1];
				}

			}

			// CONSTRAINT
			else if(preg_match("/^CONSTRAINT/", $detail)) {
//				print "constraint:" . $detail."\n";

				preg_match("/`(.+)` FOREIGN KEY \(`(.+)`\) REFERENCES `(.+)` \(`(.+)`\)[ ]?(.*)/", $detail, $constraint);
				if(count($constraint) >= 5) {
//					$table_info["constraints"][$constraint[2]][$constraint[3].".".$constraint[4]] = $constraint[1];
					$table_info["constraints"][$constraint[2]][$constraint[1]] = [$constraint[3].".".$constraint[4] => isset($constraint[5]) ? $constraint[5] : ""];
//					$table_info["constraints"][$constraint[2]][$constraint[3].".".$constraint[4]][] = [$constraint[1] => isset($constraint[5]) ? $constraint[5] : ""];
				}

				
			}
		}
//		print_r($table_info);
		return $table_info;
	}


	// get sql create file matching table name
	// looks in config/db and config/items/db (LOCAL_PATH and FRAMEWORK_PATH)
	function getSQLFile($table) {

		$sql_file = false;
	
		if(file_exists(LOCAL_PATH."/config/db/".$table.".sql")) {
			$sql_file = LOCAL_PATH."/config/db/".$table.".sql";
		}
		else if(file_exists(LOCAL_PATH."/config/db/items/".$table.".sql")) {
			$sql_file = LOCAL_PATH."/config/db/items/".$table.".sql";
		}
		else if(file_exists(FRAMEWORK_PATH."/config/db/".$table.".sql")) {
			$sql_file = FRAMEWORK_PATH."/config/db/".$table.".sql";
		}
		else if(file_exists(FRAMEWORK_PATH."/config/db/items/".$table.".sql")) {
			$sql_file = FRAMEWORK_PATH."/config/db/items/".$table.".sql";
		}

		return $sql_file;

	}


	// Synchronize existing table with matching sql-file
	// Will automatically update the current table layouts
	function synchronizeTable($table) {

		// print "SYNC table:$table<br>\n";

		// Deal with versions-tables
		if(preg_match("/_versions$/", $table)) {

//			print "VERSIONS TABLE<br>\n";

			$sql_file = $this->getSQLFile(preg_replace("/_versions$/", "", $table));
			if($sql_file) {
				$sql_file_content = file_get_contents($sql_file);

				// Update syntax to reflect versions-table (to enable easy comparison)


				// Exception for older MySQL/MariaDB (> 5.6.1)
				// It does not support current_timestamp as default on two columns
				// Replace all occurences of current_timestamp with NULL
				// and leave only the versioned column to be default current_timestamp
				$query = new Query();
				$query->sql("SELECT VERSION() as version");
				$db_version = $query->result(0, "version");
				if(preg_match("/5\.([0-5]|6\.0)/", $db_version)) {

					// Also enable null where needed first
					$sql_file_content = preg_replace("/NOT NULL DEFAULT current_timestamp[\(\)]*/i", "NULL DEFAULT NULL", $sql_file_content);

					// replace where null is already allowed
					$sql_file_content = preg_replace("/current_timestamp[\(\)]*/i", "NULL", $sql_file_content);

				}


				// update constraints names
				$sql_file_content = str_replace(preg_replace("/_versions$/", "", $table), $table, $sql_file_content);

				// remove SITE_DB placeholders
				$sql_file_content = preg_replace("/\`SITE_DB\`\./", "", $sql_file_content);

				// Get table structure
				$reference_info = $this->parseCreateSQL($sql_file_content);

				// Add versioned column to reference info
				if($reference_info && isset($reference_info["columns"])) {
					$reference_info["columns"]["versioned"] = "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP";
				}

			}

		}
		else {


			$sql_file = $this->getSQLFile($table);
			// debug(["REAL TABLE", $sql_file]);

			if($sql_file) {
				$sql_file_content = file_get_contents($sql_file);

				// remove SITE_DB placeholders
				$sql_file_content = preg_replace("/\`SITE_DB\`\./", "", $sql_file_content);

				// Get table structure
				$reference_info = $this->parseCreateSQL($sql_file_content);
			}
		}

		// debug([$sql_file_content, $reference_info]);
		// do we have sufficient info
		if($sql_file && $reference_info) {

			// get structure of current table in DB
			$table_info = $this->tableInfo(SITE_DB.".".$table);

			// Syntax matches
			if($table_info === $reference_info) {
				return array("success" => true, "message" => "$table: OK");
			}
			// Out of sync
			else {

				

				// Drop contraints, to be able to update keys and columns freely
				$this->process($this->dropConstraints(SITE_DB.".".$table), true);


				// Columns are out of sync
				// could be both column order and column declarations
				if($table_info["columns"] !== $reference_info["columns"]) {

//					print "columns DIFFERS<br>\n";


					// get numbered index' to compare positions
					$table_column_index = array_keys($table_info["columns"]);
					$reference_column_index = array_keys($reference_info["columns"]);

					// Update order or add new columns
					foreach($reference_column_index as $index => $column) {

						// column is not in the right place
						if(count($table_column_index) < $index || !isset($table_column_index[$index]) || $column != $table_column_index[$index]) {

							// does column exist in different place in current table
							if(isset($table_info["columns"][$column])) {

								// move column and update declaration
								$this->process($this->modifyColumn(SITE_DB.".".$table, $column, $reference_info["columns"][$column], $reference_column_index[$index-1]), true);

							}
							// column does not exist in current table
							else {

								// insert column
								$this->process($this->addColumn(SITE_DB.".".$table, $column, $reference_info["columns"][$column], $reference_column_index[$index-1]), true);

							}

						}
						// Column is in the correct place, just update declaration
						else {

//							print "column in right place: $column<br>\n";

							// update declaration
							$this->process($this->modifyColumn(SITE_DB.".".$table, $column, $reference_info["columns"][$column]), true);

						}

					}


					// get updated structure of current table in DB (changes could have been made above)
					$table_info = $this->tableInfo(SITE_DB.".".$table);

					// Loop through existing columns and columns that are no longer a part of the reference sql
					foreach($table_info["columns"] as $column => $definition) {

						// column doesn't exist in reference sql
						if(!isset($reference_info["columns"][$column])) {

//							print "should delete $column<br>\n";

							// delete column
 							$this->process($this->dropColumn(SITE_DB.".".$table, $column), true);

						}

					}


					// get updated structure of current table in DB (changes could have been made above)
					$table_info = $this->tableInfo(SITE_DB.".".$table);

				}

				// Primary keys are out of sync
				if($table_info["primary_key"] !== $reference_info["primary_key"]) {

//					print "primary_key DIFFERS<br>\n";

					return array("success" => false, "message" => "PRIMARY KEY CANNOT BE CHANGED AUTOMATICALLY");
				}


				// Unique keys are out of sync
				if($table_info["unique_keys"] !== $reference_info["unique_keys"]) {

//					print "unique_keys DIFFERS<br>\n";

					// Drop all Unique keys
					$this->process($this->dropUniqueKeys(SITE_DB.".".$table), true);

					// Add all Unique keys from reference table
					if($reference_info["unique_keys"]) {
						foreach($reference_info["unique_keys"] as $column => $key_names) {
							foreach($key_names as $key_name) {
								$this->process($this->addUniqueKey(SITE_DB.".".$table, $column, $key_name), true);
							}
						}
					}

					// get updated structure of current table in DB (changes could have been made above)
					$table_info = $this->tableInfo(SITE_DB.".".$table);

				}


				// Primary keys or Constraints out of sync
				// These depend on each other and cannot be updated independently
				if($table_info["keys"] !== $reference_info["keys"]) {

//					print "keys DIFFERS<br>\n";

					// Drop keys
					$this->process($this->dropKeys(SITE_DB.".".$table), true);


					// Add all keys from reference table
					if($reference_info["keys"]) {
						foreach($reference_info["keys"] as $column => $key_names) {
							foreach($key_names as $key_name) {
								$this->process($this->addKey(SITE_DB.".".$table, $column, $key_name), true);
							}
						}
					}

					// get updated structure of current table in DB (changes could have been made above)
					$table_info = $this->tableInfo(SITE_DB.".".$table);

				}

				// Add all constraints from reference table
				// Constraints are always disabled on update, so always re-apply constraints
				if($reference_info["constraints"]) {
					foreach($reference_info["constraints"] as $column => $key_names) {
						foreach($key_names as $key_name => $constraint) {
							foreach($constraint as $ref_column => $action) {
								$this->process($this->addConstraint(SITE_DB.".".$table.".".$column, SITE_DB.".".$ref_column, $action, $key_name), true);
							}
						}
					}
				}

			}

			return array("success" => true, "message" => "$table synchronized: OK");

		}
		else {
			return array("success" => false, "message" => "$table: REFERENCE SQL FILE NOT FOUND");
		}

	}

	// Create DB-table if it does not already exist
	// This function is similar to $query->checkDbExistence but provides better feedback for upgrade process
	function createTableIfMissing($db_table) {

		$query = new Query();
		list($db, $table) = explode(".", $db_table);

		$message = '';
		$message .= "CREATE TABLE $table";


		// check if database exists
		$sql = "SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$db' AND TABLE_NAME = '$table'";
		if(!$query->sql($sql)) {

			// look for SQL file
			$db_file = false;

			// look for matching db sql file
			if(file_exists(LOCAL_PATH.'/config/db/'.$table.'.sql')) {
				$db_file = LOCAL_PATH.'/config/db/'.$table.'.sql';
			}
			else if(file_exists(FRAMEWORK_PATH.'/config/db/'.$table.'.sql')) {
				$db_file = FRAMEWORK_PATH.'/config/db/'.$table.'.sql';
			}
			else if(file_exists(FRAMEWORK_PATH.'/config/db/items/'.$table.'.sql')) {
				$db_file = FRAMEWORK_PATH.'/config/db/items/'.$table.'.sql';
			}

			// found SQL file
			if($db_file) {
				$sql = file_get_contents($db_file);
				$sql = str_replace("SITE_DB", SITE_DB, $sql);
				if($query->sql($sql)) {
					$message .= ": DONE";
					$success = true;
				}
				else {
					$message .= ": Failed creating database table: $db_file: ".$query->dbError();
					$success = false;
				}
			}
			// could not find SQL file
			else {
				$message .= ": Could not find sql file for $table.";
				$success = false;
			}

		}
		// table already exists
		else {
			$message .= ": ALREADY EXISTS";
			$success = true;
		}

		return array("success" => $success, "message" => $message);
	}


	// Rename a table
	function renameTable($db_table, $new_name) {

		$query = new Query();
		list($db, $table) = explode(".", $db_table);

		$message = '';
		$message .= "RENAME TABLE $table TO $new_name";

		// DOES TABLE NAME EXIST
		$sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = '".$db."' AND table_name = '".$table."'";
		if($query->sql($sql)) {

			// ATTEMPT RENAME
			$sql = "RENAME TABLE `".$db."`.`".$table."` TO `".$db."`.`".$new_name."`";
			if($query->sql($sql)) {
				$message .= ": DONE";
				$success = true;
			}
			// DOES BOTH TABLES EXIST ALREADY?
			else if($query->sql("SELECT table_name FROM information_schema.tables WHERE table_schema = '".$db."' AND table_name = '".$new_name."'")){
				$message .= ": BOTH TABLES EXIST";
				$success = false;
			}
			// RENAME FAILED
			else {
				$message .= ": FAILED";
				$success = false;
			}

		}
		// DOES NEW NAME EXIST ALREADY?
		else if($query->sql("SELECT table_name FROM information_schema.tables WHERE table_schema = '".$db."' AND table_name = '".$new_name."'")){
			$message .= ": ALREADY RENAMED";
			$success = true;
		}
		// OLD AND NEW TABLE NOT FOUND
		else {
			$message .= ": NOT FOUND";
			$success = false;
		}

		return array("success" => $success, "message" => $message);
	}

	// Drop a table
	function dropTable($db_table) {

		$query = new Query();
		list($db, $table) = explode(".", $db_table);

		$message = '';
		$message .= "DROP TABLE $table";

		// DOES TABLE NAME EXIST
		$sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = '".$db."' AND table_name = '".$table."'";
		if($query->sql($sql)) {

			// ATTEMPT DROP
			$sql = "DROP TABLE `".$db."`.`".$table."`";
			if($query->sql($sql)) {
				$message .= ": DONE";
				$success = true;
			}
			// DROP FAILED
			else {
				$message .= ": FAILED";
				$success = false;
			}

		}
		// TABLE NOT FOUND
		else {
			$message .= ": NOT FOUND";
			$success = true;
		}

		return array("success" => $success, "message" => $message);
	}


	/**
	* Add column to table
	*
	* @param $db_table = database.table (ex. parentnode_dk.item_post)
	* @param $name = table column name (ex. classname)
	* @param $declaration = Settings for table column (ex. varchar(100) NOT NULL DEFAULT '')
	* @param first_after = position of column in table. 
	* Possible values for first_after:
	* first_after = false (Add as last column) - DEFAULT
	* first_after = true (Column will be inserted as the first column in table)
	* first_after = column-name (Column will be inserted after column-name, ex. html, column will be inserted after the html column)
	*/
	function addColumn($db_table, $name, $declaration, $first_after = false) {

		$query = new Query();
		list($db, $table) = explode(".", $db_table);
		$message = '';
		$message .= "ADD COLUMN $name $declaration TO $table" . ($first_after === true ? " FIRST" : ($first_after ? " AFTER $first_after" : ""));

		$table_info = $this->tableInfo("$db.$table");
		
		// TABLE INFO AVAILABLE
		if($table_info) {

			// Column does not exist
			if(!isset($table_info["columns"]) || !isset($table_info["columns"][$name])) {

				$sql = "ALTER TABLE $db_table ADD $name $declaration" . ($first_after === true ? " FIRST" : ($first_after ? " AFTER $first_after" : ""));
				if($query->sql($sql)) {
					$message .= ": DONE";
					$success = true;
				}
				else {
					$message .= ": FAILED";
					$success = false;
				}
			}
			// COLUMN EXISTS
			else {

				// Check that column is in the right place
				$keys = array_keys($table_info["columns"]);

				// Is after true
				if($first_after === true) {
					if(array_search($name, $keys) === 0) {
						$message .= ": COLUMN EXISTS";
						$success = true;
					}
					else {
						$message .= ": COLUMN EXISTS IN WRONG PLACE";
						$success = false;
					}
				}
				// Is after defined column
				else if($first_after) {
					if(array_search($name, $keys) === array_search($first_after, $keys)+1) {
						$message .= ": COLUMN EXISTS";
						$success = true;
					}
					else {
						$message .= ": COLUMN EXISTS IN WRONG PLACE";
						$success = false;
					}
				}
				// should be the last column
				else {
					// in correct position
					if(array_search($name, $keys) == count($keys)-1) {
						$message .= ": COLUMN EXISTS";
						$success = true;
					}
					// in wrong position
					else {
						$message .= ": COLUMN EXISTS IN WRONG PLACE";
						$success = false;
					}
				}
			}
		}
		// NO TABLE INFO
		else {
			$message .= ": FAILED GETTING TABLE INFO";
			$success = false;
		}

		return array("success" => $success, "message" => $message);
	}

	// delete column
	function dropColumn($db_table, $name) {

		$query = new Query();
		list($db, $table) = explode(".", $db_table);
		$message = '';
		$message .= "DROP COLUMN $name FROM $table";

		// DOES COLUMN EXIST
		if($query->sql("SELECT DISTINCT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE column_name = '$name' AND TABLE_NAME = '$table' AND TABLE_SCHEMA = '$db'")) {
			$sql = "ALTER TABLE `$db`.`$table` DROP `$name`";
			if($query->sql($sql)) {
				$message .= ": DONE";
				$success = true;
			}
			else {
				$message .= ": FAILED";
				$success = false;
			}
		}
		// COLUMN DOES NOT EXIST
		else {
			$message .= ": COLUMN ALREADY DROPPED";
			$success = true;
		}

		return array("success" => $success, "message" => $message);
	}

	/**
	* modify column declaration and position
	*
	* @param $db_table = database.table (ex. parentnode_dk.item_post)
	* @param $name = table column name (ex. classname)
	* @param $declaration = Settings for table column (ex. varchar(100) NOT NULL DEFAULT '')
	* @param first_after = position of column in table. 
	* Possible values for first_after:
	* first_after = false (Do not change the column order) - DEFAULT
	* first_after = true (Column will be moved to become the first column in table)
	* first_after = column-name (Column will be moved to after column-name, ex. html, column will be moved to follow the html column)
	*
	* This methods also checks existing values when modifying a column.
	* If column is declared with a NOT NULL setting, existing NULL values will be replaced by DEFAULT value or '' if no DEFAULT is declared
	* If column is declared with a NULL (allow NULL) setting, existing '' will be replaced by NULL
	*/
	function modifyColumn($db_table, $name, $declaration, $first_after = false) {

		$query = new Query();
		list($db, $table) = explode(".", $db_table);
		$message = '';
		$message .= "MODIFY COLUMN $name $declaration IN $table" . ($first_after === true ? " FIRST" : ($first_after ? " AFTER $first_after" : ""));

		// DOES COLUMN EXIST IN TABLE
		if($query->sql("SELECT DISTINCT TABLE_NAME, COLUMN_NAME, COLUMN_DEFAULT, DATA_TYPE, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE column_name = '".$name."' AND TABLE_NAME = '".$table."' AND TABLE_SCHEMA = '".$db."'")) {

			// get current NULL state for column
			$data_type = $query->result(0, "DATA_TYPE");
			$is_nullable = $query->result(0, "IS_NULLABLE") === "YES" ? true : false;

			// check content of column before updating declaration to avoid declaraion block by invalid values
			$allow_null = preg_match("/NOT NULL/i", $declaration) ? false : true;
			$new_default_value = "";
			// match any default value in declaration
			if(preg_match("/ DEFAULT ('[^']+'|[^ $]+)$/", $declaration, $matches)) {
				// remove 's from string default value
				$new_default_value = preg_replace("/'/", "", $matches[1]);
//				print "DEFAULT VALUE:".$new_default_value."#<br>\n";
			}

			// If NULL is not allowed, replace all NULL and empty values with default value
			if(!$allow_null) {

				// if datatype changes in this update
				if(
					(preg_match("/text|varchar/", $declaration) && !preg_match("/text|varchar/", $data_type))
						||
					(preg_match("/current_timestamp/", $declaration) && !preg_match("/current_timestamp/", $data_type))
				) {

					// Modify column without NULL OR DEFAULT declaration
					$alter_sql = "ALTER TABLE $db_table MODIFY $name ". preg_replace("/(NOT NULL[ ]?|DEFAULT [^$]+)/", "", $declaration);
//					print "ALTER TABLE: " . $alter_sql."<br>\n";
					$query->sql($alter_sql);
				}

				$sql = "UPDATE $db_table SET ";
				// value is function (current_timestamp) or (new) column is integer
				if(preg_match("/current_timestamp/i", $new_default_value) || preg_match("/int\([\d]+\)/i", $declaration)) {
					$sql .=	"$name = $new_default_value";
				}
				// string - encapsulate column value in quotes (they have been stripped in parsing)
				else {
					$sql .=	"$name = '$new_default_value'";
				}
				$sql .= " WHERE $name IS NULL";
				// don't look for empty strings on int columns
				if(preg_match("/text|varchar/i", $declaration)) {
					$sql .= " OR $name = ''";
				}
//				print "NOT NULL: " . $sql."<br>\n";
				$query->sql($sql);
			}
			// If NULL is allowed, replace all empty values with default value (which might be NULL)
			else if($allow_null) {

				// Is column currently NOT NULLABLE? Then values cannot be updated until it has been made NULLABLE
				if(!$is_nullable && $new_default_value === "NULL") {

					// Modify column with full declaration
					$alter_sql = "ALTER TABLE $db_table MODIFY $name $declaration";
		//			print "ALTER TABLE: " . $alter_sql."<br>\n";
					$query->sql($alter_sql);

				}

				$sql = "UPDATE $db_table SET $name = ".($new_default_value === "NULL" ? "NULL" : "'$new_default_value'")." WHERE $name = ''";
//				print "NULL: " . $sql."<br>\n";
				$query->sql($sql);
			}


			// Modify column with full declaration
			$alter_sql = "ALTER TABLE $db_table MODIFY $name $declaration" . ($first_after === true ? " FIRST" : ($first_after ? " AFTER $first_after" : ""));
			// print "ALTER TABLE: " . $alter_sql."<br>\n";
			// print "#".$query->sql($alter_sql)."#";
			if($query->sql($alter_sql)) {
				$message .= ": DONE";
				$success = true;
			}
			// Could not modify column
			else {
				$message .= ": FAILED";
				$success = false;
			}
		}
		// COLUMN DOES NOT EXIST
		else {
			$message .= ": COLUMN MISSING";
			$success = false;
		}

		return array("success" => $success, "message" => $message);
	}

	// Rename a table column
	function renameColumn($db_table, $column, $new_name) {

		$query = new Query();
		list($db, $table) = explode(".", $db_table);
		$message = '';
		$message .= "RENAME COLUMN $column TO $new_name ON $table";

		$table_info = $this->tableInfo("$db.$table");

		// TABLE INFO AVAILABLE
		if($table_info) {

			// Column exists
			if(isset($table_info["columns"]) && isset($table_info["columns"][$column])) {

				$sql = "ALTER TABLE $db_table CHANGE COLUMN `$column` `$new_name` ".$table_info["columns"][$column];
				if($query->sql($sql)) {
					$message .= ": DONE";
					$success = true;
				}
				else {
					$message .= ": FAILED";
					$success = false;
				}
			}
			else if(isset($table_info["columns"]) && isset($table_info["columns"][$new_name])) {
				$message .= ": ALREADY RENAMED";
				$success = true;
			}
			// column does not exist
			else {
				$message .= ": COLUMN DOES NOT EXIST";
				$success = false;
			}
		}
		// NO TABLE INFO
		else {
			$message .= ": FAILED GETTING TABLE INFO";
			$success = false;
		}

		return array("success" => $success, "message" => $message);
	}



	// add constraint to table
	function addConstraint($db_table_column, $ref_db_table_column, $action, $constraint_name = false) {

		$query = new Query();
		list($db, $table, $column) = explode(".", $db_table_column);
		list($ref_db, $ref_table, $ref_column) = explode(".", $ref_db_table_column);

		$message = '';
		$message .= "ADD $table.$column -> $ref_table.$ref_column CONSTRAINT";

		$table_info = $this->tableInfo("$db.$table");
		
		// TABLE INFO AVAILABLE
		if($table_info) {

			// Constraint does not exist
			if(!isset($table_info["constraints"]) || !isset($table_info["constraints"][$column]) || !isset($table_info["constraints"][$column]["$ref_table.$ref_column"])) {

				$sql = "ALTER TABLE $db.$table ADD CONSTRAINT".($constraint_name ? " `$constraint_name`" : "")." FOREIGN KEY (`$column`) REFERENCES $ref_db.$ref_table(`$ref_column`) $action";
				// print $sql."<br>\n";
				if($query->sql($sql)) {
					$message .= ": CONSTRAINT ADDED";
					$success = true;
				}
				else {
					$message .= ": FAILED";
					$success = false;
				}
			}
			// Constraint already exist
			else {
				$message .= ": CONSTRAINT EXISTS";
				$success = true;
			}			
		}
		// NO TABLE INFO
		else {
			$message .= ": FAILED GETTING TABLE INFO";
			$success = false;
		}

		return array("success" => $success, "message" => $message);
	}

	// drop constraints/foreign keys on table
	// optional only drop constraints for specific column
	function dropConstraints($db_table, $column = false) {

		$query = new Query();
		list($db, $table) = explode(".", $db_table);
		$message = '';
		$message .= "DROP ".($column ? $column." " : "")."CONSTRAINTS ON $table";

		$table_info = $this->tableInfo($db_table);

		// TABLE INFO AVAILABLE
		if($table_info) {

			// TABLE HAS CONSTRAINTS
			if($table_info["constraints"]) {

				$total_constraints = 0;
				$deleted_constraints = 0;

				// Only drop for specific column
				if($column) {

					// constraints available for column
					if(isset($table_info["constraints"][$column])) {
						foreach($table_info["constraints"][$column] as $constraint_name => $constraint) {
							$total_constraints++;

							$sql = "ALTER TABLE $db_table DROP FOREIGN KEY $constraint_name";
//							print $sql."<br>\n";
							if($query->sql($sql)) {
								$deleted_constraints++;
							}
						}
					}

				}
				// drop all constraints
				else {

					$total_constraints = 0;
					$deleted_constraints = 0;

					foreach($table_info["constraints"] as $column => $column_constraints) {
						foreach($column_constraints as $constraint_name => $constraint) {
							$total_constraints++;

							$sql = "ALTER TABLE $db_table DROP FOREIGN KEY $constraint_name";
//							print $sql."<br>\n";
							if($query->sql($sql)) {
								$deleted_constraints++;
							}
						}
					}
				}

				// if all constraints was deleted correctly
				if($total_constraints == $deleted_constraints) {
					$message .= ": DONE";
					$success = true;
				}
				else {
					$message .= ": ALL CONSTRAINTS COULD NOT BE DELETED";
					$success = false;
				}

			}
			// NO CONSTRAINTS
			else {
				$message .= ": NO CONSTRAINTS EXIST";
				$success = true;
			}

		}
		// NO TABLE INFO
		else {
			$message .= ": FAILED GETTING TABLE INFO";
			$success = false;
		}
		
		return array("success" => $success, "message" => $message);
	}

	
	// add key to table - default index key, optional unique key
	// only add if key does not exist already
	function addKey($db_table, $column, $name = false) {

		$query = new Query();
		list($db, $table) = explode(".", $db_table);
		$message = '';
		$message .= "ADD $column KEY TO $table";

		$table_info = $this->tableInfo($db_table);
		
		// TABLE INFO AVAILABLE
		if($table_info) {
			// Key does not exist
			if(!isset($table_info["keys"]) || !isset($table_info["keys"][$column])) {
				$sql = "ALTER TABLE $db_table ADD KEY".($name ? " $name" : "")." (`$column`)";
				// Add key
				if($query->sql($sql)) {
					$message .= ": KEY ADDED";
					$success = true;
				}
				// Adding key failed
				else {
					$message .= ": FAILED";
					$success = false;
				}
			}
			// Key already exist
			else {
				$message .= ": KEY EXISTS";
				$success = true;
			}			
		}
		// NO TABLE INFO
		else {
			$message .= ": FAILED GETTING TABLE INFO";
			$success = false;
		}
		
		return array("success" => $success, "message" => $message);
	}

	// drop keys on table
	// optional only drop keys for specific column
	function dropKeys($db_table, $column = false) {

		$query = new Query();
		list($db, $table) = explode(".", $db_table);
		$message = '';
		$message .= "DROP ".($column ? $column." " : "")."KEYS ON $table";

		$table_info = $this->tableInfo($db_table);

		// TABLE INFO AVAILABLE
		if($table_info) {

			// TABLE HAS KEYS
			if($table_info["keys"]) {

				$total_keys = 0;
				$deleted_keys = 0;

				// Only drop for specific column
				if($column) {

					// keys available for column
					if(isset($table_info["keys"][$column])) {
						foreach($table_info["keys"][$column] as $key) {
							$total_keys++;

							$sql = "ALTER TABLE $db_table DROP KEY $key";
							if($query->sql($sql)) {
								$deleted_keys++;
							}
						}
					}

				}
				// drop all keys
				else {

					$total_keys = 0;
					$deleted_keys = 0;

					foreach($table_info["keys"] as $column_keys) {
						foreach($column_keys as $key) {
							$total_keys++;

							$sql = "ALTER TABLE $db_table DROP KEY $key";
							if($query->sql($sql)) {
								$deleted_keys++;
							}
						}
					}
				}

				// if all keys was deleted correctly
				if($total_keys == $deleted_keys) {
					$message .= ": DONE";
					$success = true;
				}
				else {
					$message .= ": ALL KEYS COULD NOT BE DELETED";
					$success = false;
				}

			}
			// NO CONSTRAINTS
			else {
				$message .= ": NO KEYS EXIST";
				$success = true;
			}

		}
		// NO TABLE INFO
		else {
			$message .= ": FAILED GETTING TABLE INFO";
			$success = false;
		}

		return array("success" => $success, "message" => $message);
	}


	// Add unique key to table
	function addUniqueKey($db_table, $column, $name = false) {
		$query = new Query();
		list($db, $table) = explode(".", $db_table);
		$message = '';
		$message .= "ADD UNIQUE $column KEY TO $table";

		$table_info = $this->tableInfo($db_table);
		
		// TABLE INFO AVAILABLE
		if($table_info) {
			// Key does not exist
			if(!isset($table_info["unique_keys"]) || !isset($table_info["unique_keys"][$column])) {
				$sql = "ALTER TABLE $db_table ADD UNIQUE".($name ? " $name" : "")." (`$column`)";
				// Add key
				if($query->sql($sql)) {
					$message .= ": UNIQUE KEY ADDED";
					$success = true;
				}
				// Adding key failed
				else {
					$message .= ": FAILED";
					$success = false;
				}
			}
			// Key already exist
			else {
				$message .= ": UNIQUE KEY EXISTS";
				$success = true;
			}			
		}
		// NO TABLE INFO
		else {
			$message .= ": FAILED GETTING TABLE INFO";
			$success = false;
		}
		
		return array("success" => $success, "message" => $message);
	}

	// drop unique keys on table
	// optional only drop unique keys for specific column
	function dropUniqueKeys($db_table, $column = false) {

		$query = new Query();
		list($db, $table) = explode(".", $db_table);
		$message = '';
		$message .= "DROP UNIQUE ".($column ? $column." " : "")."KEYS ON $table";

		$table_info = $this->tableInfo($db_table);

		// TABLE INFO AVAILABLE
		if($table_info) {

			// TABLE HAS KEYS
			if($table_info["unique_keys"]) {

				$total_keys = 0;
				$deleted_keys = 0;

				// Only drop for specific column
				if($column) {

					// keys available for column
					if(isset($table_info["unique_keys"][$column])) {
						foreach($table_info["unique_keys"][$column] as $key_name) {
							$total_keys++;

							$sql = "ALTER TABLE $db_table DROP INDEX $key_name";
//							print $sql."<br>\n";
							if($query->sql($sql)) {
								$deleted_keys++;
							}
						}
					}

				}
				// drop all keys
				else {

					$total_keys = 0;
					$deleted_keys = 0;

					foreach($table_info["unique_keys"] as $column => $key_names) {
						foreach($key_names as $key_name) {
							$total_keys++;

							$sql = "ALTER TABLE $db_table DROP INDEX $key_name";
//							print $sql."<br>\n";
							if($query->sql($sql)) {
								$deleted_keys++;
							}
						}
					}
				}

				// if all keys was deleted correctly
				if($total_keys == $deleted_keys) {
					$message .= ": DONE";
					$success = true;
				}
				else {
					$message .= ": ALL UNIQUE KEYS COULD NOT BE DELETED";
					$success = false;
				}

			}
			// NO CONSTRAINTS
			else {
				$message .= ": NO UNIQUE KEYS EXIST";
				$success = true;
			}

		}
		// NO TABLE INFO
		else {
			$message .= ": FAILED GETTING TABLE INFO";
			$success = false;
		}

		return array("success" => $success, "message" => $message);
	}



	// check if acceptable default values exist in table
//	function checkDefaultValues($db_table, $values, $accept_row) {
	function checkDefaultValues($db_table) {

		$query = new Query();
		list($db, $table) = explode(".", $db_table);

		$default_data = false;


		$message = '';
		$message .= "CHECK DEFAULT VALUE OF $table";

		$sql = "SELECT * FROM $db_table";
		if(!$query->sql($sql)) {
			
			if(file_exists(LOCAL_PATH."/config/db/default_data/$table.sql")) {
				$default_data = file_get_contents(LOCAL_PATH."/config/db/default_data/$table.sql");
			}
			else if(file_exists(FRAMEWORK_PATH."/config/db/default_data/$table.sql")) {
				$default_data = file_get_contents(FRAMEWORK_PATH."/config/db/default_data/$table.sql");
			}

			if($default_data) {

				$default_data = preg_replace("/SITE_DB/", SITE_DB, $default_data);
				if($query->sql($default_data)) {
					
					$message .= ": VALUES ADDED";
					$success = true;
					
				}
				else {
					
					$message .= ": VALUES COULD NOT BE ADDED";
					$success = false;
					
				}
				
			}
			else {
				
				$message .= ": NO DEFAULT VALUES";
				$success = true;
				
			}
			
		}
		else {
			
			$message .= ": VALUES EXIST";
			$success = true;
			
		}
		
		return array("success" => $success, "message" => $message);
	}
	
}

?>