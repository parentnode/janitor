<?php



class Upgrade {


	function __construct() {

		// global $page;
		// print "loading db config";
		// $page->loadDBConfiguration();
		//
		//
		//
		// include_once("classes/items/items.core.class.php");
		// include_once("classes/items/items.class.php");

	}


	// process upgrade task
	function process($result, $critical = false) {

		// print message
		print '<li'.(!$result["success"] ? ' class="error"' : '').'>'.$result["message"].'</li>';

		// end process on critical error
		if(!$result["success"] && $critical) {

			print '<li class="error fatal">UPGRADE PROCESS STOPPED DUE TO CRITICAL ERRORS</li>';

			throw new Exception();
		}
	}


	// Check Database structure for v0_8 requirements
	function fullUpgrade() {
		
		$query = new Query();
		$IC = new Items();
		include_once("classes/users/superuser.class.php");
		$UC = new SuperUser();

		if((defined("SITE_SHOP") && SITE_SHOP)) {
			include_once("classes/shop/supershop.class.php");
			$SC = new SuperShop();
		}


		try {



			// TODO: Pull the latest Janitor version



			// Updating controller code syntax to work with PHP7
			$fs = new FileSystem();
			$controllers = $fs->files(LOCAL_PATH."/www", array("allow_extensions" => "php"));
			$file = "";
			foreach($controllers as $controller) {
				$file = file_get_contents($controller);
				if(preg_match("/->\\\$action\[[0-9]+\]/", $file, $matches)) {
					// replace with valid syntax 
					$file = preg_replace("/->\\\$action\[([0-9]+)\]/", "->{\\\$action[$1]}", $file);
					// save file
					file_put_contents($controller, $file);
				}
			}
			// lighten the burden
			$file = null;



			// TODO: start stopknappen specific
			$topic_table = $this->tableInfo(SITE_DB.".item_topic");
			// if table exists and still has "problem"-column
			if($topic_table && isset($topic_table["columns"]["problem"])) {

//				print_r($topic_table);

				if($query->sql("SELECT * FROM ".SITE_DB.".item_topic")) {
					$topics = $query->results();

					foreach($topics as $topic) {

						// combine all text columns in "problem"-column
						$problem_headline = '<h2 class="problem">'.$topic["problem_headline"].'</h2>';
						$problem_text = $topic["problem"];
						$solution_text = '<h2 class="solution">Løsningen</h2>'."\n".$topic["solution"];
						$details = '<h3 class="details">Detaljer</h3>'."\n".$topic["details"];

						$html = $problem_headline."\n".$problem_text."\n".$solution_text."\n".$details;

						// update problem field with new text
						$sql = "UPDATE ".SITE_DB.".item_topic SET problem = '".prepareForDB($html)."' WHERE id = ".$topic["id"];
						$query->sql($sql);

					}

					// remove excess columns
					$this->process($this->dropColumn(SITE_DB.".item_topic", "problem_headline"), true);
					$this->process($this->dropColumn(SITE_DB.".item_topic", "solution"), true);
					$this->process($this->dropColumn(SITE_DB.".item_topic", "details"), true);

					// rename "problem" column to "html"
					$this->process($this->renameColumn(SITE_DB.".item_topic", "problem", "html"), true);
				}

				$topic_table_versions = $this->tableInfo(SITE_DB.".item_topic_versions");
				if($topic_table_versions && isset($topic_table_versions["columns"]["problem"])) {

					if($query->sql("SELECT * FROM ".SITE_DB.".item_topic_versions")) {
						$topics = $query->results();

						foreach($topics as $topic) {

							// combine all text columns in "problem"-column
							$problem_headline = '<h2 class="problem">'.$topic["problem_headline"].'</h2>';
							$problem_text = $topic["problem"];
							$solution_text = '<h2 class="solution">Løsningen</h2>'."\n".$topic["solution"];
							$details = '<h3 class="details">Detaljer</h3>'."\n".$topic["details"];

							$html = $problem_headline."\n".$problem_text."\n".$solution_text."\n".$details;

							// update problem field with new text
							$sql = "UPDATE ".SITE_DB.".item_topic_versions SET problem = '".prepareForDB($html)."' WHERE id = ".$topic["id"];
							$query->sql($sql);

						}

						// remove excess columns
						$this->process($this->dropColumn(SITE_DB.".item_topic_versions", "problem_headline"), true);
						$this->process($this->dropColumn(SITE_DB.".item_topic_versions", "solution"), true);
						$this->process($this->dropColumn(SITE_DB.".item_topic_versions", "details"), true);

						// rename "problem" column to "html"
						$this->process($this->renameColumn(SITE_DB.".item_topic_versions", "problem", "html"), true);
					}

				}
			}
			// TODO: end stopknappen specific



			$qna_table = $this->tableInfo(SITE_DB.".item_qna");
			if($qna_table && !isset($qna_table["columns"]["question"])) {


				// add about item id column
				$this->process($this->addColumn(SITE_DB.".item_qna", "about_item_id", "int(11) DEFAULT NULL", "name"), true);
				$this->process($this->addKey(SITE_DB.".item_qna", "about_item_id"), true);
				$this->process($this->addConstraint(SITE_DB.".item_qna.about_item_id", SITE_DB.".items.id", "ON DELETE CASCADE ON UPDATE CASCADE"), true);

				$this->process($this->addColumn(SITE_DB.".item_qna", "question", "text NOT NULL", "about_item_id"), true);
				$this->process($this->modifyColumn(SITE_DB.".item_qna", "answer", "text NULL"), true);

				if($query->sql("SELECT * FROM ".SITE_DB.".item_qna")) {
					$qnas = $query->results();

					foreach($qnas as $qna) {

						$about_item_id = false;
						// try to find related item based on tag
						$tags = $IC->getTags(array("item_id" => $qna["item_id"], "tag_context" => "qna"));
						if($tags) {
							$related_items = $IC->getItems(array("itemtype" => "topic", "tags" => "qna:".$tags[0]["value"], "limit" => 1));
							if($related_items) {
								$about_item_id = $related_items[0]["id"];
							}
						}

						// adjust values
						$question = $qna["name"];
						$name = cutString($question, 45);

						// update name, about_item_id and question field with new values
						$sql = "UPDATE ".SITE_DB.".item_qna SET ".($about_item_id ? "about_item_id = $about_item_id, " : "").(!$qna["answer"] ? "answer = NULL, " : "")."name = '".prepareForDB($name)."', question = '".prepareForDB($question)."' WHERE id = ".$qna["id"];
						$query->sql($sql);

					}

					$this->process($this->modifyColumn(SITE_DB.".item_qna", "name", "varchar(50) NOT NULL"), true);
					$this->process($this->modifyColumn(SITE_DB.".item_qna", "answer", "text DEFAULT NULL"), true);


				}

				$qna_table_versions = $this->tableInfo(SITE_DB.".item_qna_versions");
				if($qna_table_versions && !isset($qna_table_versions["columns"]["question"])) {

					// add about item id column
					$this->process($this->addColumn(SITE_DB.".item_qna_versions", "about_item_id", "int(11) DEFAULT NULL", "name"), true);
					$this->process($this->addKey(SITE_DB.".item_qna_versions", "about_item_id"), true);
					$this->process($this->addConstraint(SITE_DB.".item_qna_versions.about_item_id", SITE_DB.".items.id", "ON DELETE CASCADE ON UPDATE CASCADE"), true);

					$this->process($this->addColumn(SITE_DB.".item_qna_versions", "question", "text NOT NULL", "name"), true);

					if($query->sql("SELECT * FROM ".SITE_DB.".item_qna_versions")) {
						$qnas = $query->results();

						foreach($qnas as $qna) {

							$about_item_id = false;
							// try to find related item based on tag
							$tags = $IC->getTags(array("item_id" => $qna["item_id"], "context" => "qna"));
							if($tags) {
								$related_items = $IC->getItems(array("itemtype" => "topic", "tags" => "qna:".$tags[0]["value"], "limit" => 1));
								if($related_items) {
									$about_item_id = $related_items[0]["id"];
								}
							}

							// adjust values
							$question = $qna["name"];
							$name = cutString($question, 45);

							// update name, about_item_id and question field with new values
							$sql = "UPDATE ".SITE_DB.".item_qna_versions SET ".($about_item_id ? "about_item_id = $about_item_id, " : "").(!$qna["answer"] ? "answer = NULL, " : "")."name = '".prepareForDB($name)."', question = '".prepareForDB($question)."' WHERE id = ".$qna["id"];
							$query->sql($sql);

						}
					}

					$this->process($this->modifyColumn(SITE_DB.".item_qna_versions", "name", "varchar(50) NOT NULL"), true);
					$this->process($this->modifyColumn(SITE_DB.".item_qna_versions", "answer", "text DEFAULT NULL"), true);

				}

				$tags = $IC->getTags(array("context" => "qna"));
				if($tags) {

					// delete all qna tags
					foreach($tags as $tag) {
						// delete QNA tag
						$TC = new Tag();
						$TC->deleteTag(["deleteTag", $tag["id"]]);
					}
				}
			}


			$post_table = $this->tableInfo(SITE_DB.".item_post");
			if($post_table && !isset($post_table["columns"]["classname"])) {

				// add about item id column
				$this->process($this->addColumn(SITE_DB.".item_post", "classname", "varchar(100) DEFAULT NULL", "name"), true);

				$post_table_versions = $this->tableInfo(SITE_DB.".item_post_versions");
				if($post_table_versions && !isset($post_table_versions["columns"]["classname"])) {

					// add about item id column
					$this->process($this->addColumn(SITE_DB.".item_post_versions", "classname", "int(11) DEFAULT NULL", "name"), true);
				}

			}


			// SYSTEM

			// RENAME (LIKELY) EXISTING TABLES (TABLES MAY NOT EXIST - SO THIS IS NOT CRITICAL)
			$this->process($this->renameTable(SITE_DB.".languages", "system_languages"));
			$this->process($this->renameTable(SITE_DB.".countries", "system_countries"));
			$this->process($this->renameTable(SITE_DB.".currencies", "system_currencies"));

			if(defined("SITE_SHOP") && SITE_SHOP) {
				$this->process($this->renameTable(SITE_DB.".vatrates", "system_vatrates"));
			}
			if((defined("SITE_SHOP") && SITE_SHOP) || (defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS)) {
				$this->process($this->renameTable(SITE_DB.".payment_methods", "system_payment_methods"));
			}
			if((defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS)) {
				$this->process($this->renameTable(SITE_DB.".subscription_methods", "system_subscription_methods"));
			}

			// CREATE ANY MISSING SYSTEM TABLES (CRITICAL)
			$this->process($this->createTableIfMissing(UT_LANGUAGES), true);
			$this->process($this->createTableIfMissing(UT_CURRENCIES), true);
			$this->process($this->createTableIfMissing(UT_COUNTRIES), true);


			if((defined("SITE_SHOP") && SITE_SHOP) || (defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS)) {
				$this->process($this->createTableIfMissing(UT_PAYMENT_METHODS), true);
			}
			if((defined("SITE_SHOP") && SITE_SHOP)) {
				$this->process($this->createTableIfMissing(UT_VATRATES), true);

				// SHOP
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
			$this->process($this->createTableIfMissing(UT_NEWSLETTERS), true);



			// CHECK DEFAULT VALUES
			$this->process($this->checkDefaultValues(UT_LANGUAGES, "'DA','Dansk'", "id = 'DA'"), true);
			$this->process($this->checkDefaultValues(UT_CURRENCIES, "'DKK', 'Kroner (Denmark)', 'DKK', 'after', 2, ',', '.'", "id = 'DKK'"), true);
			$this->process($this->checkDefaultValues(UT_COUNTRIES, "'DK', 'Danmark', '45', '#### ####', 'DA', 'DKK'", "id = 'DK'"), true);

			if((defined("SITE_SHOP") && SITE_SHOP)) {
				$this->process($this->checkDefaultValues(UT_VATRATES, "1, 'No VAT', 0, 'DK'", "id = 1"), true);
				$this->process($this->checkDefaultValues(UT_VATRATES, "2, '25%', 25, 'DK'", "id = 2"), true);
			}
			if((defined("SITE_SHOP") && SITE_SHOP) || (defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS)) {
				$this->process($this->addColumn(UT_PAYMENT_METHODS, "classname", "varchar(50) DEFAULT NULL", "name"), true);
				$this->process($this->addColumn(UT_PAYMENT_METHODS, "gateway", "varchar(50) DEFAULT NULL", "description"), true);
				$this->process($this->addColumn(UT_PAYMENT_METHODS, "position", "int(11) DEFAULT '0'", "gateway"), true);

				$this->process($this->checkDefaultValues(UT_PAYMENT_METHODS, "DEFAULT, 'Bank transfer', 'banktransfer', 'Regular bank transfer. Preferred option.', NULL, 1", "classname = 'banktransfer'"), true);
				$this->process($this->checkDefaultValues(UT_PAYMENT_METHODS, "DEFAULT, 'Credit Card', 'disabled', 'Coming very soon.', NULL, 4", "classname = 'disabled'"), true);
				$this->process($this->checkDefaultValues(UT_PAYMENT_METHODS, "DEFAULT, 'PayPal', 'paypal', 'Pay to our paypal account.', NULL, 3", "classname = 'paypal'"), true);
				$this->process($this->checkDefaultValues(UT_PAYMENT_METHODS, "DEFAULT, 'Cash', 'cash', 'Pay in cash on your next visit.', NULL, 3", "classname = 'cash'"), true);
			}
			if((defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS)) {
				$this->process($this->checkDefaultValues(UT_SUBSCRIPTION_METHODS, "1, 'Month', 'monthly', DEFAULT", "id = 1"), true);
				$this->process($this->checkDefaultValues(UT_SUBSCRIPTION_METHODS, "2, 'Never expires', '*', DEFAULT", "id = 2"), true);
			}



			// CHECK EXTENDED ITEMS TABLES

			// ITEM COMMENTS
			$this->process($this->createTableIfMissing(UT_ITEMS_COMMENTS), true);

			// update user_id declaration and constraint
			$this->process($this->dropConstraints(UT_ITEMS_COMMENTS, "user_id"), true);
			$this->process($this->dropKeys(UT_ITEMS_COMMENTS, "user_id"), true);

			$this->process($this->modifyColumn(UT_ITEMS_COMMENTS, "user_id", "int(11) NOT NULL", "item_id"), true);

			$this->process($this->addKey(UT_ITEMS_COMMENTS, "user_id"), true);
			$this->process($this->addConstraint(UT_ITEMS_COMMENTS.".user_id", SITE_DB.".users.id", "ON UPDATE CASCADE"), true);


			if((defined("SITE_SHOP") && SITE_SHOP)) {

				// ITEM PRICES
				$this->process($this->createTableIfMissing(UT_ITEMS_PRICES), true);

				// add price type and quantity
				$this->process($this->addColumn(UT_ITEMS_PRICES, "type", "varchar(20) DEFAULT NULL", "vatrate_id"), true);
				$this->process($this->addColumn(UT_ITEMS_PRICES, "quantity", "int(11) DEFAULT NULL", "type"), true);
				$query->sql("UPDATE ".UT_ITEMS_PRICES." SET type = 'default' WHERE type = '' OR type IS NULL");

			}




			// USER/ITEM

			// READSTATES
			$result = $this->renameTable(SITE_DB.".items_readstate", "user_item_readstates");
			$this->process($result);

			// old readstates table exists, update structure
			if($result["success"]) {

				// move item_id column
				$this->process($this->modifyColumn(SITE_DB.".user_item_readstates", "item_id", "int(11) NOT NULL", "user_id"), true);

				// update user_id declaration and constraint
				$this->process($this->dropConstraints(SITE_DB.".user_item_readstates", "user_id"), true);
				$this->process($this->dropKeys(SITE_DB.".user_item_readstates", "user_id"), true);

				$this->process($this->modifyColumn(SITE_DB.".user_item_readstates", "user_id", "int(11) NOT NULL"), true);

				$this->process($this->addKey(SITE_DB.".user_item_readstates", "user_id"), true);
				$this->process($this->addConstraint(SITE_DB.".user_item_readstates.user_id", SITE_DB.".users.id", "ON DELETE CASCADE ON UPDATE CASCADE"), true);

			}
			// table doesn't exist
			else {

				$this->process($this->createTableIfMissing(SITE_DB.".user_item_readstates"), true);

			}


			if((defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS)) {

				// SPECIAL CASES - PRERELEASE UPGRADE

				// SUBSCRIPTIONS
				$result = $this->renameTable(SITE_DB.".user_subscriptions", "user_item_subscriptions");
				$this->process($result);

				// old subscriptions table exists, update structure
				if($result["success"]) {

					$this->process($this->dropColumn(SITE_DB.".user_item_subscriptions", "comment"), true);
					$this->process($this->dropColumn(SITE_DB.".user_item_subscriptions", "status"), true);


					$this->process($this->addColumn(SITE_DB.".user_item_subscriptions", "renewed_at", "TIMESTAMP NULL DEFAULT NULL", "modified_at"), true);
					$this->process($this->addColumn(SITE_DB.".user_item_subscriptions", "expires_at", "TIMESTAMP NULL DEFAULT NULL", "renewed_at"), true);

					// add payment_method column
					$this->process($this->addColumn(SITE_DB.".user_item_subscriptions", "order_id", "int(11) DEFAULT NULL", "item_id"), true);
					$this->process($this->addColumn(SITE_DB.".user_item_subscriptions", "payment_method", "int(11) DEFAULT NULL", "order_id"), true);
					$this->process($this->addKey(SITE_DB.".user_item_subscriptions", "payment_method"), true);
					$this->process($this->addConstraint(SITE_DB.".user_item_subscriptions.payment_method", UT_PAYMENT_METHODS.".id", "ON UPDATE CASCADE"), true);



					// transfer old subscriptions model to new membership model
					$item_subscription = $this->tableInfo(SITE_DB.".item_subscription");
					if($item_subscription && isset($item_subscription["columns"]["renewal"])) {

						// change layout
						$this->process($this->addColumn(SITE_DB.".item_subscription", "introduction", "text NOT NULL", "description"), true);
						$this->process($this->dropColumn(SITE_DB.".item_subscription", "renewal"), true);

						if($this->tableInfo(SITE_DB.".item_subscription_versions")) {

							// change layout
							$this->process($this->addColumn(SITE_DB.".item_subscription_versions", "introduction", "text NOT NULL", "description"), true);
							$this->process($this->dropColumn(SITE_DB.".item_subscription_versions", "renewal"), true);
						}

						$query->sql("SELECT * FROM ".UT_ITEMS." WHERE itemtype = 'subscription'");
						$items = $query->results();
						if($items) {
							foreach($items as $item) {
								$query->sql("UPDATE ".UT_ITEMS." SET itemtype = 'membership' WHERE itemtype = 'subscription' AND id = ".$item["id"]);
							}
						}

						// rename
						$this->process($this->renameTable(SITE_DB.".item_subscription", "item_membership"), true);

						// rename
						$this->process($this->renameTable(SITE_DB.".item_subscription_versions", "item_membership_versions"), true);

						
						$model = $IC->typeObject("membership");
						$items = $IC->getItems(array("itemtype" => "membership"));

						foreach($items as $item) {
							if($item["sindex"] == "curious-cat") {
								$_POST["item_subscription_method"] = 2;
							}
							else {
								$_POST["item_subscription_method"] = 1;
							}
							$model->updateSubscriptionMethod(array("updateSubscriptionMethod", $item["id"]));
							unset($_POST);

							// ADD CORRECT VAT SETTING
							$query->sql("UPDATE ".SITE_DB.".items_prices SET vatrate_id = 2 WHERE item_id = ".$item["id"]);

						}

					}



					if(defined("SITE_MEMBERS") && SITE_MEMBERS) {
						// MEMBERS
						$this->process($this->createTableIfMissing(SITE_DB.".user_members"), true);

						// CONVERT USERS/SUBSCRIBERS TO MEMBERS
						$sql = "SELECT * FROM ".$UC->db;
						if($query->sql($sql)) {
							$users = $query->results();

							foreach($users as $user) {

								if($user["id"] != 1) {
									// add members
									$_POST["user_id"] = $user["id"];
									$UC->addMembership(array("addMembership"));
									unset($_POST);

									// update member creation date
									$query->sql("UPDATE ".SITE_DB.".user_members SET created_at = '".$user["created_at"]."' WHERE user_id = ".$user["id"]);
								}

							}

						}

					}


					// $sql = "SELECT * FROM ".SITE_DB.".user_item_subscriptions";
					// if($query->sql($sql)) {
					// 	$subscriptions = $query->results();
					//
					//
					// 	// TODO: start think specific
					// 	$opening_timestamp = mktime(0, 0, 0, 9, 24, 2016);
					// 	// TODO: end think specific
					//
					//
					// 	foreach($subscriptions as $subscription) {
					//
					// 		// TODO: start think specific
					// 		// update subscription timestamps before opening
					// 		$timestamp = strtotime($subscription["created_at"]);
					// 		if($timestamp < $opening_timestamp) {
					//
					// 			// update subscription creation date
					// 			$subscription["created_at"] = date("Y-m-d H:i:s", $opening_timestamp);
					// 			$query->sql("UPDATE ".SITE_DB.".user_item_subscriptions SET created_at = '".$subscription["created_at"]."' WHERE id = ".$subscription["id"]);
					// 		}
					// 		// TODO: end think specific
					//
					//
					//
					// 		// add order
					// 		$_POST["user_id"] = $subscription["user_id"];
					// 		$_POST["order_comment"] = "System upgade";
					// 		$order = $SC->addOrder(array("addOrder"));
					// 		unset($_POST);
					//
					//
					// 		// add item to order
					// 		$_POST["item_id"] = $subscription["item_id"];
					// 		$_POST["quantity"] = 1;
					// 		$SC->addToOrder(array("addToOrder", $order["id"]));
					// 		unset($_POST);
					//
					// 		$item = $IC->getItem(array("id" => $subscription["item_id"], "extend" => array("subscription_method" => true)));
					//
					// 		// update subscription timestamps
					// 		$sql = "UPDATE ".SITE_DB.".user_item_subscriptions SET renewed_at = NULL";
					// 		$expires_at = $UC->calculateSubscriptionExpiry($item["subscription_method"]["duration"], $subscription["created_at"]);
					// 		if($expires_at) {
					// 			$sql .= ", expires_at = '$expires_at'";
					// 		}
					// 		else {
					// 			$sql .= ", expires_at = NULL";
					// 		}
					// 		$sql .= " WHERE id = ".$subscription["id"];
					//
					// 		$query->sql($sql);
					//
					// 	}
					//
					// }

				}
				// table doesn't exist
				else {

					$this->process($this->createTableIfMissing(SITE_DB.".user_item_subscriptions"), true);

				}

			}

			if(defined("SITE_MEMBERS") && SITE_MEMBERS) {

				// MEMBERS
				$this->process($this->createTableIfMissing(SITE_DB.".user_members"), true);

			}




			// USERS

			$this->process($this->dropConstraints(SITE_DB.".users", "language"), true);
			$this->process($this->dropKeys(SITE_DB.".users", "language"), true);

			$this->process($this->modifyColumn(SITE_DB.".users", "language", "varchar(2) DEFAULT NULL"), true);
			$query->sql("UPDATE ".SITE_DB.".users SET language = NULL WHERE language = ''");

			$this->process($this->addColumn(SITE_DB.".users", "last_login_at", "timestamp NULL DEFAULT NULL", "modified_at"), true);

			$this->process($this->addKey(SITE_DB.".users", "language"), true);
			$this->process($this->addConstraint(SITE_DB.".users.language", UT_LANGUAGES.".id", "ON UPDATE CASCADE"), true);


			if(defined("SITE_SIGNUP") && SITE_SIGNUP) {

				// USER NEWSLETTER SUBSCRIPTIONS

				$this->process($this->createTableIfMissing(SITE_DB.".user_newsletters"), true);

				// Get all existing newsletters from original newsletter table
				$query->sql("SELECT * FROM ".SITE_DB.".user_newsletters GROUP BY newsletter");
				$all_newsletters = $query->results();
				// does newsletter result contain old newsletter column (otherwise is has already been updated)
				if($all_newsletters && isset($all_newsletters[0]["newsletter"])) {

					// Create newsletters in new system table
					foreach($all_newsletters as $newsletter) {
						$this->process($this->checkDefaultValues(UT_NEWSLETTERS, "'DEFAULT','".$newsletter["newsletter"]."', DEFAULT", "name = '".$newsletter["newsletter"]."'"), true);
					}

					// get all subscribers
					$query->sql("SELECT * FROM ".SITE_DB.".user_newsletters");
					$newsletter_subscribers = $query->results();
				 
					// get all the newsletters from new system table
					$query->sql("SELECT * FROM ".UT_NEWSLETTERS);
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

					$this->process($this->addKey(SITE_DB.".user_newsletters", "newsletter_id"), true);
					$this->process($this->addConstraint(SITE_DB.".user_newsletters.newsletter_id", UT_NEWSLETTERS.".id", "ON UPDATE CASCADE"), true);

				}

			}


			// TODO: set filemode and file permissions as well (just to be sure) 


			// Upgrade complete
			print '<li class="done">UPGRADE COMPLETE</li>';

		}
		catch(Exception $exception) {}

	}



	// Check Database structure for v0_8 requirements
	function replaceEmails() {

		$query = new Query();

		try {

			// change emails for all users (during test)
			if($query->sql("SELECT * FROM ".SITE_DB.".user_usernames WHERE type='email'")) {
				$usernames = $query->results();
				foreach($usernames as $username) {
//					if($username["type"] == "email" && !preg_match("/@think\.dk/", $username["username"])) {
						if($query->sql("UPDATE ".SITE_DB.".user_usernames SET username = '".ADMIN_EMAIL."' WHERE id = ".$username["id"])) {
							$this->process(array("success" => true, "message" => "Replaced ". $username["username"] . " with " . ADMIN_EMAIL), true);
						}
						else {
							$this->process(array("success" => false), true);
						}
//					}
				}
			}


			// Upgrade complete
			print '<li class="done">REPLACMENT COMPLETE</li>';

		}
		catch(Exception $exception) {}

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
					include_once("classes/system/video.class.php");
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

			$table_info = array("columns" => array(), "primary_key" => false, "unique_keys" => false, "keys" => array(), "constraints" => array());

			if($create_syntax["Create Table"]) {
				$table_details = explode("\n", $create_syntax["Create Table"]);

				foreach($table_details as $detail) {
					$detail = preg_replace("/,$/", "", trim($detail));

					// COLUMN
					if(preg_match("/^`/", $detail)) {
//						print "column:" . $detail."\n";
						
						preg_match("/^`(.+)` (.+)/", $detail, $column);
						if(count($column) == 3) {
							$table_info["columns"][$column[1]] = $column[2];
						}

					}

					// PRIMARY KEY
					else if(preg_match("/^PRIMARY KEY/", $detail)) {
//						print "pkey:" . $detail."\n";

						preg_match("/`(.+)`/", $detail, $pkey);
						if(count($pkey) == 2) {
							$table_info["primary_key"] = $pkey[1];
						}

					}

					// UNIQUE KEY
					else if(preg_match("/^UNIQUE KEY/", $detail)) {
//						print "ukey:" . $detail."\n";

						preg_match("/`(.+)` \(`(.+)`\)/", $detail, $ukey);
						if(count($ukey) == 3) {
							$table_info["unique_keys"][$ukey[2]][] = $ukey[1];
						}

					}

					// KEY
					else if(preg_match("/^KEY/", $detail)) {
//						print "key:" . $detail."\n";
						
						preg_match("/`(.+)` \(`(.+)`\)/", $detail, $key);
						if(count($key) == 3) {
							$table_info["keys"][$key[2]][] = $key[1];
						}

					}

					// CONSTRAINT
					else if(preg_match("/^CONSTRAINT/", $detail)) {
//						print "constraint:" . $detail."\n";

						preg_match("/`(.+)` FOREIGN KEY \(`(.+)`\) REFERENCES `(.+)` \(`(.+)`\)/", $detail, $constraint);
						if(count($constraint) == 5) {
							$table_info["constraints"][$constraint[2]][$constraint[3].".".$constraint[4]] = $constraint[1];
						}

						
					}
				}

				return $table_info;
			}


		}

		return false;


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



	// Add column to table
	function addColumn($db_table, $name, $declaration, $after = false) {

		$query = new Query();
		list($db, $table) = explode(".", $db_table);
		$message = '';
		$message .= "ADD COLUMN $name $declaration TO $table" . ($after ? " AFTER $after" : "");

		$table_info = $this->tableInfo("$db.$table");
		
		// TABLE INFO AVAILABLE
		if($table_info) {

			// Column does not exist
			if(!isset($table_info["columns"]) || !isset($table_info["columns"][$name])) {

				$sql = "ALTER TABLE $db_table ADD $name $declaration" . ($after ? " AFTER $after" : "");
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

				// Is after defined
				if($after) {
					if(array_search($name, $keys) === array_search($after, $keys)+1) {
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

	// modify column declaration and position
	function modifyColumn($db_table, $name, $declaration, $after = false) {

		$query = new Query();
		list($db, $table) = explode(".", $db_table);
		$message = '';
		$message .= "MODIFY COLUMN $name $declaration IN $table" . ($after ? " AFTER $after" : "");

		// DOES COLUMN EXIST IN TABLE
		if($query->sql("SELECT DISTINCT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE column_name = '".$name."' AND TABLE_NAME = '".$table."' AND TABLE_SCHEMA = '".$db."'")) {

			// Modify column
			if($query->sql("ALTER TABLE $db_table MODIFY $name $declaration" . ($after ? " AFTER $after" : ""))) {
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
	function addConstraint($db_table_column, $ref_db_table_column, $constraint) {

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

				$sql = "ALTER TABLE $db.$table ADD CONSTRAINT FOREIGN KEY (`$column`) REFERENCES $ref_db.$ref_table(`$ref_column`) $constraint";
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
						foreach($table_info["constraints"][$column] as $constraint) {
							$total_constraints++;

							$sql = "ALTER TABLE $db_table DROP FOREIGN KEY $constraint";
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

					foreach($table_info["constraints"] as $column_constraints) {
						foreach($column_constraints as $constraint) {
							$total_constraints++;

							$sql = "ALTER TABLE $db_table DROP FOREIGN KEY $constraint";
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
	function addKey($db_table, $column) {

		$query = new Query();
		list($db, $table) = explode(".", $db_table);
		$message = '';
		$message .= "ADD $column KEY TO $table";

		$table_info = $this->tableInfo($db_table);
		
		// TABLE INFO AVAILABLE
		if($table_info) {
			// Key does not exist
			if(!isset($table_info["keys"]) || !isset($table_info["keys"][$column])) {
				$sql = "ALTER TABLE $db_table ADD KEY (`$column`)";
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
	function addUniqueKey($db_table, $column) {
		$query = new Query();
		list($db, $table) = explode(".", $db_table);
		$message = '';
		$message .= "ADD UNIQUE $column KEY TO $table";

		$table_info = $this->tableInfo($db_table);
		
		// TABLE INFO AVAILABLE
		if($table_info) {
			// Key does not exist
			if(!isset($table_info["unique_keys"]) || !isset($table_info["unique_keys"][$column])) {
				$sql = "ALTER TABLE $db_table ADD UNIQUE (`$column`)";
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
						foreach($table_info["unique_keys"][$column] as $key) {
							$total_keys++;

							$sql = "ALTER TABLE $db_table DROP INDEX $key";
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

					foreach($table_info["unique_keys"] as $column_keys) {
						foreach($column_keys as $key) {
							$total_keys++;

							$sql = "ALTER TABLE $db_table DROP INDEX $key";
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
	function checkDefaultValues($db_table, $values, $accept_row) {

		$query = new Query();
		list($db, $table) = explode(".", $db_table);

		$message = '';
		$message .= "CHECK DEFAULT VALUE OF $table ($accept_row)";

		$sql = "SELECT * FROM $db_table WHERE $accept_row";
		if(!$query->sql($sql)) {

			$sql = "INSERT INTO $db_table values($values)";
//			print $sql;
			if($query->sql($sql)) {
				$message .= ": VALUES ADDED";
				$success = true;
			}
			else {
				$message .= ": VALUES COULD NOT BE ADDED";
				$success = false;
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