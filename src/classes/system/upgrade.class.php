<?php



class Upgrade extends Model {


	function __construct() {

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


	// Check Database structure for v0_8 requirements
	function fullUpgrade() {

		// Upgrade can take some time - allow it to take the time it needs
		set_time_limit(0);


		global $model;

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



			// TODO: Pull the latest Janitor version
			// Requires some sort of reload to continue upgrade on updated codebase



			// Updating controller code syntax to work with PHP7
			$fs = new FileSystem();
			$controllers = $fs->files(LOCAL_PATH."/www", array("allow_extensions" => "php"));
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
			// lighten the burden
			$file = null;
			$controller = null;
			$controllers = null;



			// UPDATING SYSTEM TABLES

			// PREFIX OLD SYSTEM TABLES WITH "SYSTEM"
			// RENAME (LIKELY) EXISTING TABLES (TABLES MAY NOT EXIST - SO THIS IS NOT CRITICAL)
			$this->process($this->renameTable(SITE_DB.".languages", "system_languages"));
			$this->process($this->renameTable(SITE_DB.".countries", "system_countries"));
			$this->process($this->renameTable(SITE_DB.".currencies", "system_currencies"));

			if((defined("SITE_SHOP") && SITE_SHOP) || (defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS)) {
				$this->process($this->renameTable(SITE_DB.".payment_methods", "system_payment_methods"));
			}
			if(defined("SITE_SHOP") && SITE_SHOP) {
				$this->process($this->renameTable(SITE_DB.".vatrates", "system_vatrates"));
			}
			if((defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS)) {
				$this->process($this->renameTable(SITE_DB.".subscription_methods", "system_subscription_methods"));
			}


			// Update newsletters to maillists
			if(defined("SITE_SIGNUP") && SITE_SIGNUP) {
				$this->process($this->renameTable(SITE_DB.".system_newsletters", "system_maillists"));
			}


			// CREATE ANY MISSING SYSTEM TABLES (CRITICAL)
			$this->process($this->createTableIfMissing(UT_LANGUAGES), true);
			$this->process($this->createTableIfMissing(UT_CURRENCIES), true);
			$this->process($this->createTableIfMissing(UT_COUNTRIES), true);

			if(defined("SITE_SIGNUP") && SITE_SIGNUP) {
				// CREATE MAILLIST TABLE
				$this->process($this->createTableIfMissing(UT_MAILLISTS), true);
				// CREATE AGREEMENTS
				$this->process($this->createTableIfMissing(SITE_DB.".user_log_agreements"), true);
			}
			// CREATE PAYMENT METHOD TABLE
			if((defined("SITE_SHOP") && SITE_SHOP) || (defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS)) {
				$this->process($this->createTableIfMissing(UT_PAYMENT_METHODS), true);
			}
			// CREATE SHOP TABLES
			if((defined("SITE_SHOP") && SITE_SHOP)) {
				$this->process($this->createTableIfMissing(UT_VATRATES), true);

				// SHOP
				$this->process($this->createTableIfMissing(SITE_DB.".user_addresses"), true);
				$this->process($this->createTableIfMissing(SITE_DB.".shop_carts"), true);
				$this->process($this->createTableIfMissing(SITE_DB.".shop_cart_items"), true);
				$this->process($this->createTableIfMissing(SITE_DB.".shop_orders"), true);
				$this->process($this->createTableIfMissing(SITE_DB.".shop_order_items"), true);
				$this->process($this->createTableIfMissing(SITE_DB.".shop_payments"), true);
			}
			if((defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS)) {
				$this->process($this->createTableIfMissing(UT_SUBSCRIPTION_METHODS), true);
				// ITEM SUBSCRIPTION METHOD
				$this->process($this->createTableIfMissing(UT_ITEMS_SUBSCRIPTION_METHOD), true);
			}


			// CHECK DEFAULT VALUES
			$this->process($this->checkDefaultValues(UT_LANGUAGES), true);
			$this->process($this->checkDefaultValues(UT_CURRENCIES), true);
			$this->process($this->checkDefaultValues(UT_COUNTRIES), true);

			if((defined("SITE_SHOP") && SITE_SHOP)) {
				$this->process($this->checkDefaultValues(UT_VATRATES), true);
			}
			if((defined("SITE_SHOP") && SITE_SHOP) || (defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS)) {
				$this->process($this->addColumn(UT_PAYMENT_METHODS, "classname", "varchar(50) DEFAULT NULL", "name"), true);
				$this->process($this->addColumn(UT_PAYMENT_METHODS, "gateway", "varchar(50) DEFAULT NULL", "description"), true);
				$this->process($this->addColumn(UT_PAYMENT_METHODS, "position", "int(11) DEFAULT '0'", "gateway"), true);

				$this->process($this->checkDefaultValues(UT_PAYMENT_METHODS), true);
			}
			if((defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS)) {
				$this->process($this->checkDefaultValues(UT_SUBSCRIPTION_METHODS), true);
			}



			// CHECK EXTENDED ITEMS TABLES

			// ITEM COMMENTS
			$this->process($this->createTableIfMissing(UT_ITEMS_COMMENTS), true);

			// ITEM RATINGS
			$this->process($this->createTableIfMissing(UT_ITEMS_RATINGS), true);



			// USER/ITEM

			// READSTATES
			$this->process($this->renameTable(SITE_DB.".items_readstate", "user_item_readstates"));
			$this->process($this->createTableIfMissing(SITE_DB.".user_item_readstates"), true);


			// PASSWORDS
			
			// ADD UPGRADE COLUMN, IF IT DOES NOT EXIST
			$user_passwords_table = $this->tableInfo(SITE_DB.".user_passwords");
			if($user_passwords_table && !isset($user_passwords_table["columns"]["upgrade_password"])) {

				// move password to password_upgrade
				$this->process($this->renameColumn(SITE_DB.".user_passwords", "password", "upgrade_password"), true);

				// add new password column
				$this->process($this->addColumn(SITE_DB.".user_passwords", "password", "varchar(255) NOT NULL DEFAULT ''", "user_id"), true);

			}


			# SUBSCRIPTIONS

			if((defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS)) {

				$this->process($this->createTableIfMissing(SITE_DB.".user_item_subscriptions"), true);

			}



			# MEMBERS

			if(defined("SITE_MEMBERS") && SITE_MEMBERS) {

				// MEMBERS
				$this->process($this->createTableIfMissing(SITE_DB.".user_members"), true);

			}


			# SIGNUP
			if(defined("SITE_SIGNUP") && SITE_SIGNUP) {

				// VERIFICATION LINKS 
				$user_log_activation_reminders = $this->tableInfo(SITE_DB.".user_log_activation_reminders");
				if ($user_log_activation_reminders) {
					$this->process($this->renameColumn(SITE_DB.".user_log_activation_reminders", "created_at", "reminded_at"), true);
					$this->process($this->addColumn(SITE_DB.".user_log_activation_reminders", "username_id", "int(11) DEFAULT NULL", "user_id"), true);

					// retrieve username_ids from user_ids and insert them in the new username_id column
					$query->sql("SELECT user_id FROM ".SITE_DB.".user_log_activation_reminders GROUP BY user_id");
					$user_ids = $query->results("user_id");

					if($user_ids) {
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
					}

					$this->process($this->renameTable(SITE_DB.".user_log_activation_reminders", "user_log_verification_links"), true);

				}



				# NEWSLETTERS TO MAILLISTS

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

				// create the table if missing
				$this->process($this->createTableIfMissing(SITE_DB.".user_maillists"), true);

			}



			// ORDERS

			// Add billing_name from user info if not already set
			if((defined("SITE_SHOP") && SITE_SHOP)) {

				$orders = $SC->getOrders();
				if($orders) {
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

				}

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
			if(file_exists(PROJECT_PATH."/submodules/js-merger")) {
				exec("cd ".PROJECT_PATH."/submodules/js-merger && git config core.filemode false");
			}
			if(file_exists(PROJECT_PATH."/submodules/css-merger")) {
				exec("cd ".PROJECT_PATH."/submodules/css-merger && git config core.filemode false");
			}
			if(file_exists(PROJECT_PATH."/submodules/asset-builder")) {
				exec("cd ".PROJECT_PATH."/submodules/asset-builder && git config core.filemode false");
			}

			$this->process(["message" => "Git filemode updated to false", "success" => true]);



			// CODE SYNTAX JANITOR
			// get all php files in theme
			$php_files = $fs->files(LOCAL_PATH, ["allow_extensions" => "php"]);
			foreach($php_files as $php_file) {

				$is_code_altered = false;
				$code_lines = file($php_file);
				foreach($code_lines as $line_no => $line) {

					if(preg_match("/[\$](page|this)\-\>mail\(/", $line)) {

						$new_code_line = preg_replace("/[\$](page|this)\-\>mail\(/", "mailer()->send(", $line);
						if($code_lines[$line_no] != $new_code_line) {

							$code_lines[$line_no] = $new_code_line;
							$this->process(["success" => false, "message" => "FOUND AND REPLACED OLD CODE IN " . $php_file . " in line " . ($line_no+1)]);
							$is_code_altered = true;


//							print '<li class="notice">'.</li>';
						}
						else {
							$this->process(["success" => false, "message" => "FOUND OLD CODE IN " . $php_file . " in line " . ($line_no+1)], true);
//							print '<li class="error">'."FOUND OLD CODE IN " . $php_file . ' in line '.($line_no+1).'</li>';
							
						}

					}

				}

				// Should we write
				if($is_code_altered) {
					file_put_contents($php_file, implode("", $code_lines));
				}

			}
//			print_r($php_files);

			


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



	// Replace all user-emails with ADMIN_EMAIL
	// to create local dev version without triggering emails to real users
	function replaceEmails() {

		$query = new Query();
		mailer()->init_adapter();

		try {

			// change emails for all users (during test)
			if($query->sql("SELECT * FROM ".SITE_DB.".user_usernames WHERE type='email'")) {
				$usernames = $query->results();
				foreach($usernames as $username) {
					if($query->sql("UPDATE ".SITE_DB.".user_usernames SET username = '".ADMIN_EMAIL."' WHERE id = ".$username["id"])) {
						$this->process(array("success" => true, "message" => "Replaced ". $username["username"] . " with " . ADMIN_EMAIL), true);
					}
					else {
						$this->process(array("success" => false), true);
					}
				}
			}


			// Upgrade complete
			print '<li class="done">REPLACEMENT COMPLETE</li>';

		}
		catch(Exception $exception) {}

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


	// 0.7 to v 0.9 UPGRADE HELPERS


	function moveFilesColumnToItems($itemtype, $variant = false, $column = "files") {

		$query = new Query();
		$IC = new Items();
		$fs = new FileSystem();

		$query->checkDbExistence(UT_ITEMS_MEDIAE);


		$sql = "SELECT * FROM ".SITE_DB.".item_".$itemtype;
		$query->sql($sql);
		$results = $query->results();

//		print_r($results);

		foreach($results as $result) {

		//	print $result["files"] . "<br>";
			if($result[$column]) {

				$item_id = $result["item_id"];
				$format = $result[$column];

				if(file_exists(PRIVATE_FILE_PATH."/".$item_id."/".$format)) {
					$file = PRIVATE_FILE_PATH."/".$item_id."/".$format;
				}
				else if(file_exists(PRIVATE_FILE_PATH."/".$item_id."/".$variant."/".$format)) {
					$file = PRIVATE_FILE_PATH."/".$item_id."/".$variant."/".$format;
				}
				else {
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

				$variant = $variant ? $variant : randomKey(8);
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
				}
				$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id);

			}
		}
		print "You can now delete '$column' column in ".SITE_DB.".item_".$itemtype."<br>";

	}



	// V 0.7 to v 0.8 UPGRADE HELPERS

	function moveMediaeToItems($itemtype) {

		$query = new Query();
		$IC = new Items();

		$query->checkDbExistence(UT_ITEMS_MEDIAE);

		$sql = "SELECT * FROM ".SITE_DB.".item_".$itemtype."_mediae";
		print $sql."<br>\n";

		$query->sql($sql);
		$mediae = $query->results();

		foreach($mediae as $media) {

			$item_id = $media["item_id"];
			$item_format = $media["format"];
			$item_variant = isset($media["variant"]) ? $media["variant"] : "";


			if(!$item_variant) {
				print "missing variant - create variant and move file??<br>\n";
			}


			$file = PRIVATE_FILE_PATH."/".$item_id.($item_variant ? "/".$item_variant : "")."/".$item_format;

//			print_r($media);

			if(file_exists($file)) {
				print "valid file: $file<br>\n";

				$item_name = (isset($media["name"]) && $media["name"]) ? $media["name"] : $item_format;
				$item_filesize = (isset($media["filesize"]) && $media["filesize"]) ? $media["filesize"] : filesize($file);
				$item_position = (isset($media["position"]) && $media["position"]) ? $media["position"] : 0;

				if(preg_match("/jpg|png/", $item_format)) {
					$image = new Imagick($file);
					$item_width = (isset($media["width"]) && $media["width"]) ? $media["width"] : $image->getImageWidth();
					$item_height = (isset($media["height"]) && $media["height"]) ? $media["height"] : $image->getImageHeight();
				}
				else {
					$item_width = (isset($media["width"]) && $media["width"]) ? $media["width"] : 0;
					$item_height = (isset($media["height"]) && $media["height"]) ? $media["height"] : 0;
			
				}



				$sql = "INSERT INTO ".UT_ITEMS_MEDIAE." SET item_id=$item_id, format='$item_format', variant='$item_variant', name='$item_name', filesize=$item_filesize, width='$item_width', height='$item_height', position='$item_position'";
				print $sql."<br>\n";
				$query->sql($sql);
			}
			else {
				print "invalid file:" . $file ."<br>\n";
			}



		}
		
	}





	// HELPER functions


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

//		print "SYNC table:$table<br>\n";

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

//			print "REAL TABLE<br>\n";

			$sql_file = $this->getSQLFile($table);
			if($sql_file) {
				$sql_file_content = file_get_contents($sql_file);

				// remove SITE_DB placeholders
				$sql_file_content = preg_replace("/\`SITE_DB\`\./", "", $sql_file_content);

				// Get table structure
				$reference_info = $this->parseCreateSQL($sql_file_content);
			}
		}


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
			print "ALTER TABLE: " . $alter_sql."<br>\n";
			print "#".$query->sql($alter_sql)."#";
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