<?php
/**
* @package janitor.itemtypes
* This file contains generalized itemtype functionality
*/

class ItemtypeCore extends Model {

	function __construct($itemtype) {

		$this->status_states = array(
			0 => "disabled",
			1 => "enabled"
		);

		// itemtype is passed through extending constructs 
		// to ensure restrictions on itemtype data manipulation
		$this->itemtype = $itemtype;

		parent::__construct();
	}



	/**
	* Chacnge status of Item
	* TODO: Implement data validation before allowing enabling 
	*/
	# /janitor/[admin/]#itemtype#/status/#item_id#/#new_status#
	function status($action) {

		if(count($action) == 3) {

//			$itemtype = $action[0];

			$item_id = $action[1];
			$status = $action[2];

			$query = new Query();


			// delete item + itemtype + files
			if($query->sql("SELECT id FROM ".UT_ITEMS." WHERE id = $item_id AND itemtype = '$this->itemtype'")) {
				$query->sql("UPDATE ".UT_ITEMS." SET status = $status WHERE id = $item_id");

				message()->addMessage("Item ".$this->status_states[$status]);
				return true;
			}
		}

		message()->addMessage("Item could not be ".$this->status_states[$status], array("type" => "error"));
		return false;

	}

	/**
	* Chacnge status of Item
	* TODO: Implement data validation before allowing enabling 
	*/
	# /§controller#/#itemtype#/owner/#item_id#/#new_owner#
	function owner($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 2) {

//			$itemtype = $action[0];

			$item_id = $action[1];
			$new_owner = $this->getProperty("item_ownership", "value");

			$query = new Query();


			// delete item + itemtype + files
			if($query->sql("SELECT id FROM ".UT_ITEMS." WHERE id = $item_id AND itemtype = '$this->itemtype'")) {
				$sql = "UPDATE ".UT_ITEMS." SET user_id = $new_owner WHERE id = $item_id";
				$query->sql($sql);

				message()->addMessage("Item owner updated");
				return true;
			}
		}

		message()->addMessage("Item ownership could not be changed", array("type" => "error"));
		return false;

	}

	/**
	* Delete item function
	*/
	# /janitor/[admin/]#itemtype#/delete/#item_id#
	function delete($action) {
		global $page;

		if(count($action) == 2) {

			$item_id = $action[1];

			$query = new Query();
			$fs = new FileSystem();

			// delete item + itemtype + files
			$sql = "SELECT id FROM ".UT_ITEMS." WHERE id = $item_id AND itemtype = '$this->itemtype'";
			// debug([$sql]);
			if($query->sql($sql)) {

				// EXPERIMENTAL: include pre/post functions to all itemtype.core functions to make extendability better
				$pre_delete_state = true;

				// itemtype pre delete handler?
				if(method_exists($this, "deleting")) {
					$pre_delete_state = $this->deleting($item_id);
				}

				// pre delete state allows full delete
				if($pre_delete_state) {

					$sql = "DELETE FROM ".UT_ITEMS." WHERE id = $item_id";
					// debug([$sql]);
					if($query->sql($sql)) {
						
						$fs->removeDirRecursively(PUBLIC_FILE_PATH."/$item_id");
						$fs->removeDirRecursively(PRIVATE_FILE_PATH."/$item_id");

						message()->addMessage("Item deleted");


						// itemtype post delete handler?
						if(method_exists($this, "deleted")) {
							$this->deleted($item_id);
						}

						// add log
						$page->addLog("ItemType->delete ($item_id)");

						return true;
					}
				}
			}
		}

		message()->addMessage("Item could not be deleted", array("type" => "error"));
		return false;
	}



	/**
	* update/create sindex value for item (for search optimized URLs)
	*
	* @param string $item_id Item id
	* @param string $sindex
	* @return String final/valid sindex
	*/
	function sindex($sindex, $item_id, $excluded = []) {

		$query = new Query();

		// superNormalize $sindex suggetion
		$sindex = superNormalize(substr($sindex, 0, 60));
		// print "try this:" . $sindex."<br>\n";

		// check for existence
		// update if sindex does not exist for other item already
		$sql = "SELECT sindex FROM ".UT_ITEMS." WHERE sindex = '$sindex' AND id != $item_id";
		// debug([$sql]);

		// If "clean" sindex is available, then always prioritize that
		// (It always makes sense to use the cleanest possible sindex, as that is what you would typically hope for)
		if(array_search($sindex, $excluded) === false && !$query->sql($sql)) {

			$sql = "UPDATE ".UT_ITEMS." SET sindex = '$sindex' WHERE id = $item_id";
			// debug([$sql]);

			$query->sql($sql);

			return $sindex;
		}
		// Find the best sindex option
		else {

			// Is current sindex already a numeric increment of sindex, then don't change it
			// (It is confusing if sindex' change for no obvious reason)
			$sql = "SELECT sindex FROM ".UT_ITEMS." WHERE id = $item_id";
			if($query->sql($sql)) {

				$current_sindex = $query->result(0, "sindex");

				// Does sindex exist and is it in fact a numeric increment
				if($current_sindex && strpos($current_sindex, $sindex) !== false && is_numeric(str_replace($sindex."-", "", $current_sindex))) {

					// Nothing to be done
					return $sindex;
				}

			}




			// find best match incremental sindex
			// lower value is considered better


			// Add this sindex to excluded options (to prevent endless loops)
			array_push($excluded, $sindex);
			// print "We have tried these already:<br>\n";
			// print_r($excluded);

			// clean sindex in case it's coming from iteration (removing incremental value)
			$sindex = preg_replace("/-([\d]+)$/", "", $sindex);

			// find all existing incremental versions of this sindex
			$sql = "SELECT id, sindex FROM ".UT_ITEMS." WHERE sindex REGEXP '^".$sindex."[-]?[0-9]+$' ORDER BY LENGTH(sindex) DESC, sindex DESC";
			// debug([$sql]);

			$query->sql($sql);
			$existing_sindexes = $query->results();


			// set base values
			$next_i = 1;
			$last_i = 1;

			// are there more increments of this sindex
			if($existing_sindexes) {

//				print_r($existing_sindexes);

				// find last incremental value
				preg_match("/-([\d]+)$/", $existing_sindexes[0]["sindex"], $matches);
				if($matches && is_numeric($matches[1])) {
					$last_i = $matches[1];
				}
				// print "last_i: $last_i". "<br>\n";


				// amount of increments and last value matches or too many numbers in order

				// it doesn't mean that the order is perfect (just that it probably is)
				// let's just go for last+1 - if we haven't tried that already (it's the fastest option)
				if(count($existing_sindexes) >= $last_i && array_search($sindex."-".($last_i+1), $excluded) === false) {
					$next_i = $last_i+1;
					// print "try last number: $next_i";
				}
				// some numbers seems to be missing in order
				else {

					// reverse sindexes to loop through from lowest to highest
					$existing_sindexes = array_reverse($existing_sindexes);
//					print_r($existing_sindexes);

					foreach($existing_sindexes as $existing_sindex) {

						// get incremental value
						preg_match("/-([\d]+)$/", $existing_sindex["sindex"], $matches);
//						print $matches[1]. " = ".$next_i."<br>\n";

						// did we find matching incremental value
						if($matches && is_numeric($matches[1])) {

							// incremental value and position matches
							if($matches[1] == $next_i) {

								$next_i++;

							}
							// last incremental was used again, skip
							else if($matches[1] == $next_i-1) {

								// allow that - do not attempt to fix double entries here

							}
							// found a number which doesn't seem to be used
							else if($matches[1] > $next_i && array_search($sindex."-".$next_i, $excluded) === false) {

//								print "found"."<br>\n";
								break;

							}

						}

					}
//					print "looped:" . $next_i."<br>\n";

				}

			}

			$sindex = $sindex . "-" . $next_i;
			// print $sindex . "<br>\n";

			// recurse until valid sindex has been generated
			$sindex = $this->sindex($sindex, $item_id, $excluded);
		}

		return $sindex;
	}




	// DATA HANDLING



	// SAVE

	# /janitor/[admin/]#itemtype#/save
	# /#controller#/save
	function save($action) {
		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate (only name required on save)
		if($this->validateList(array("name"))) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistence($this->db);

			// create root item
			$item_id = $this->saveItem();
			if($item_id) {

				// get entities for current value
				$entities = $this->getModel();
				$names = array();
				$values = array();

				foreach($entities as $name => $entity) {
					if($entity["value"] !== false && !preg_match("/^(files|tags)$/", $entity["type"]) && !preg_match("/^(published_at|status|htmleditor_file)$/", $name)) {

						// consider reimplementing files in basic save
						// it's a bigger question
						// - are files, tags, prices, comments and ratings all external features or integrated parts of an Item

						$names[] = $name;

						// if value is posted
						if($entity["value"]) {
							$values[] = $name."='".$entity["value"]."'";
						}
						// no value is posted – reset to default value
						else {
							$values[] = $name."=DEFAULT";
						}

					}
				}

				if($values) {

					$sql = "INSERT INTO ".$this->db." SET id = DEFAULT,item_id = $item_id," . implode(",", $values);
//					print $sql;

					if($query->sql($sql)) {

						// item cannot be enabled without datacheck
						// use internal check to ensure datacheck
						$this->status(array("status", $item_id, getPost("status")));

						// look for local sindex method 
						// implement sindexBase function in your itemtype class to use special sindexes
						if(method_exists($this, "sindexBase")) {
							$sindex = $this->sindexBase($item_id);
						}
						else {
							$sindex = $this->getProperty("name", "value");
						}
						// create sindex
						$this->sindex($sindex, $item_id);


						message()->addMessage("Item saved");
						// return current item
						$IC = new Items();


						// itemtype post save handler?
						// TODO: Consider if failed postSave should have consequences
						if(method_exists($this, "saved")) {
							$this->saved($item_id);
						}

						// add log
						$page->addLog("ItemType->save ($item_id)");

						// return selected data array
						return $IC->getItem(array("id" => $item_id, "extend" => array("all" => true)));

					}
				}
			}
		}

		// save failed, remove any stored data
		if(isset($item_id)) {
			$query->sql("DELETE FROM ".UT_ITEMS." WHERE id = $item_id");
		}
		message()->addMessage("Item could not be saved", array("type" => "error"));

		return false;
	}

	// checks posted values and saves item if all informations is available
	function saveItem() {

		$query = new Query();

		// standard Item values
		// - published at (if posted it must be valid)
		if($this->validateList(array("published_at"))) {
			$published_at = toTimestamp($this->getProperty("published_at", "value"));
			$published_at = $published_at ? "'".$published_at."'" : "CURRENT_TIMESTAMP";
		}
		else {
//			print "published_At failed:".$this->getProperty("published_at", "value") ."<br>\n";
			return false;
		}

		// - user_id
		// still supporting non user systems
		$user_id = stringOr(session()->value("user_id"), "DEFAULT");

		// create item
		$sql = "INSERT INTO ".UT_ITEMS." VALUES(DEFAULT, DEFAULT, 0, '$this->itemtype', ".$user_id.", CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ".$published_at.")";
//		print $sql."<br>\n";

		// save root item
		$query->sql($sql);
		$item_id = $query->lastInsertId();

		if($item_id) {

			// return new item
			return $item_id;
		}
		return false;
	}




	// UPDATE



	/**
	* Update item type
	*/
	# /janitor/[admin/]#itemtype#/update/#item_id#
	// TODO: implement itemtype checks
	function update($action) {
		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 2) {
			$item_id = $action[1];

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistence($this->db);


			// get entities for current value
			$entities = $this->getModel();
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false && !preg_match("/^(files|tags)$/", $entity["type"]) && !preg_match("/^(published_at|status|user_id|htmleditor_file)$/", $name)) {

					// debug(["value", $entity["value"]]);
					// consider reimplementing files in basic save
					// it's a bigger question
					// - are files, tags, prices, comments and ratings all external features or integrated parts of an Item

					$names[] = $name;
					// if value is posted 
					if($entity["value"]) {
						$values[] = $name."='".$entity["value"]."'";
					}
					// no value is posted – reset to default value
					else {
						$values[] = $name."=DEFAULT";
					}
				}
			}

			if($this->validateList($names, $item_id) && $this->updateItem($item_id)) {

				// add existing data to version-control-table
				$query->versionControl($item_id, $values);

				$sql = "UPDATE ".$this->db." SET ".implode(",", $values)." WHERE item_id = ".$item_id;
				// debug([$sql]);
				if($query->sql($sql)) {


					$IC = new Items();
					$item = $IC->getItem(array("id" => $item_id, "extend" => array("all" => true)));

					// look for local sindex method 
					// implement sindexBase function in your itemtype class to use special sindexes
					if(method_exists($this, "sindexBase")) {
						$sindex = $this->sindexBase($item_id);
					}
					else {
						$sindex = $item["name"];
					}
					// create sindex
					$item["sindex"] = $this->sindex($sindex, $item_id);

					message()->addMessage("Item updated");

					// itemtype post update handler?
					// TODO: Consider if failed postSave should have consequences
					// TODO: risky - can cause endless loop - if postUpdate, makes update, makes update, makes update
					if(method_exists($this, "updated")) {
						$this->updated($item_id);
					}

					// add log
					$page->addLog("ItemType->update ($item_id)");
					
					return $item;
				}
			}

		}

		message()->addMessage("Item could not be updated", array("type" => "error"));
		return false;
	}

	// update root item
	function updateItem($item_id) {
//		print "update item<br>";

		$query = new Query();

		// is published_at valid?
		if($this->validateList(array("published_at"))) {
			$sql = "UPDATE ".UT_ITEMS." SET published_at='".toTimestamp($this->getProperty("published_at", "value"))."' WHERE id = $item_id";
//			print $sql;
			$query->sql($sql);
		}
		else {
			return false;
		}

		// updating user id?
		$user_id = $this->getProperty("user_id", "value");
		if($user_id && $this->validateList(array("user_id"))) {
			$sql = "UPDATE ".UT_ITEMS." SET user_id=$user_id WHERE id = $item_id";
			$query->sql($sql);
		}

//		$published_at = $this->validateList(array("published_at")) ? $this->getProperty("published_at", "value") : "CURRENT_TIMESTAMP"; 
//		$published_at = getPost("published_at") ? toTimestamp(getPost("published_at")) : false;

		// create item
		$sql = "UPDATE ".UT_ITEMS." SET modified_at=CURRENT_TIMESTAMP WHERE id = $item_id";
//			print $sql;
		$query->sql($sql);

		return true;
	}


	// Duplicate item
	# /#controller#/duplicate/#item_id#
	function duplicate($action) {

		$IC = new Items();


		if(count($action) == 2) {
			$item_id = $action[1];

			$item = $IC->getItem(array("id" => $item_id, "extend" => array("tags" => true, "mediae" => true, "prices" => true, "subscription_method" => true)));

			if($item) {
				$query = new Query();
				$fs = new FileSystem();

				unset($_POST);
				// Compile new POST array for item
				foreach($item as $property => $value) {
					if(is_string($value) && !preg_match("/^(id|status|sindex|itemtype|user_id|item_id|published_at|created_at|modified_at|tags|mediae)$/", $property)) {

						// Add value to POST array
						$_POST[$property] = $value;
					}
				}
				$_POST["name"] = $_POST["name"]." (cloned)";


				// create root item
				$cloned_item = $this->save(["save"]);
				unset($_POST);

				// Did we succeed in creating duplicate item
				if($cloned_item) {

					$new_item_id = $cloned_item["id"];

					// add tags
					if($item["tags"]) {
						foreach($item["tags"] as $tag) {

							unset($_POST);
							$_POST["tags"] = $tag["id"];
							$this->addTag(array("addTags", $new_item_id));
							unset($_POST);

						}
					}


					// Add prices
					if($item["prices"]) {

						foreach($item["prices"] as $price) {

							// Get full price data set
							$sql = "SELECT * FROM ".UT_ITEMS_PRICES." WHERE id = ".$price["id"];
							if($query->sql($sql)) {
								$complete_price = $query->result(0);

								// Create insert statement
								$sql = "INSERT INTO ".UT_ITEMS_PRICES." SET ";
								$sql .= "item_id='".$new_item_id."',";
			
								$sql .= "price='".$complete_price["price"]."',";
								$sql .= "currency='".$complete_price["currency"]."',";
								$sql .= "type='".$complete_price["type"]."',";
								$sql .= "vatrate_id='".$complete_price["vatrate_id"]."'";

								if($complete_price["type"] === "bulk") {
									$sql .= ",quantity='".$complete_price["quantity"]."'";
								}

								// Insert price
								$query->sql($sql);

							}

						}

					}


					// Add subscription method
					if($item["subscription_method"]) {

						unset($_POST);
						$_POST["item_subscription_method"] = $item["subscription_method"]["subscription_method_id"];
						$this->updateSubscriptionMethod(array("updateSubscriptionMethod", $new_item_id));
						unset($_POST);

					}


					// Copy/add "static" mediae
					if($item["mediae"]) {
						
						foreach($item["mediae"] as $media) {

							// Create insert statement
							$sql = "INSERT INTO ".UT_ITEMS_MEDIAE." SET ";
							$sql .= "item_id='".$new_item_id."',";
							
							$sql .= "name='".$media["name"]."',";
							$sql .= "format='".$media["format"]."',";
							$sql .= "variant='".$media["variant"]."',";
							$sql .= "width='".$media["width"]."',";
							$sql .= "height='".$media["height"]."',";
							$sql .= "filesize='".$media["filesize"]."',";
							$sql .= "position=".$media["position"];

							// Insert media
							if($query->sql($sql)) {

								// Copy media
								$fs->copy(
									PRIVATE_FILE_PATH."/".$media["item_id"].($media["variant"] ? "/".$media["variant"] : "")."/".$media["format"], 
									PRIVATE_FILE_PATH."/".$new_item_id.($media["variant"] ? "/".$media["variant"] : "")."/".$media["format"]
								);

							}

						}

					}

					// Copy/add "dynamic" mediae
					// We need model to find HTML input types
					$model = $IC->typeObject($item["itemtype"]);

					// Prepare POST array for updating HTML
					unset($_POST);


					// Look for media/files in HTML fields
					// – HTML must be updated and content must be copied to new item
					foreach($item as $property => $value) {
						if(is_string($value) && !preg_match("/^(id|status|sindex|itemtype|user_id|item_id|published_at|created_at|modified_at|tags|mediae)$/", $property)) {

							// Type is HTML
							if($model->getProperty($property, "type") === "html") {

								// Look for media div's
								preg_match_all("/\<div class\=\"(media|file) item_id\:[\d]+ variant\:HTML-[A-Za-z0-9]+ name/", $value, $mediae_matches);
								if($mediae_matches) {

									// Loop over media div's
									foreach($mediae_matches[0] as $media_match) {

										// debug($media_match);

										preg_match("/(file|media) item_id\:([\d]+) variant\:(HTML-[A-Za-z0-9]+)/", $media_match, $media_details);
										if($media_details) {
											// Get item_id and variant for each embedded media
											list(,$type, $old_item_id, $old_variant) = $media_details;


											// Get full media data set
											$sql = "SELECT * FROM ".UT_ITEMS_MEDIAE." WHERE item_id = $old_item_id AND variant = '$old_variant'";
											if($query->sql($sql)) {
												$media = $query->result(0);


												$new_variant = "HTML-".randomKey(8);


												// Create insert statement
												$sql = "INSERT INTO ".UT_ITEMS_MEDIAE." SET ";
												$sql .= "item_id='".$new_item_id."',";
							
												$sql .= "name='".$media["name"]."',";
												$sql .= "format='".$media["format"]."',";
												$sql .= "variant='".$new_variant."',";
												$sql .= "width='".$media["width"]."',";
												$sql .= "height='".$media["height"]."',";
												$sql .= "filesize='".$media["filesize"]."',";
												$sql .= "position=".$media["position"];

												// debug($sql);

												// Insert media
												if($query->sql($sql)) {

													// Update HTML block
													// Div properties
													$value = str_replace("item_id:$old_item_id variant:$old_variant", "item_id:$new_item_id variant:$new_variant", $value);
													// a-href link
													$value = str_replace("/$old_item_id/$old_variant/", "/$new_item_id/$new_variant/", $value);

													// Add to POST array
													$_POST[$property] = $value;

													// Copy private media
													$fs->copy(
														PRIVATE_FILE_PATH."/".$old_item_id."/".$old_variant, 
														PRIVATE_FILE_PATH."/".$new_item_id."/".$new_variant
													);

													// Extra action for files
													if($type === "file") {

														// Copy public files (because public zip/pdf files are not yet auto re-generated)
														$fs->copy(
															PUBLIC_FILE_PATH."/".$old_item_id."/".$old_variant, 
															PUBLIC_FILE_PATH."/".$new_item_id."/".$new_variant
														);

													}

												}

											}

										}

									}

								}

							}

						}

					}

					// Do we have update HTML values
					if(isset($_POST)) {
						// Update item
						$this->update(["update", $cloned_item["id"]]);
					}
					unset($_POST);


					message()->addMessage("Item duplicated");

					// get and return new device (id will be used to redirect to new item page)
					$item = $IC->getItem(array("id" => $cloned_item["id"]));
					return $item;

				}

			}

		}

		message()->addMessage("Item could not be duplicated", ["type" => "error"]);
		return false;
	}


	// Update item order
	// /janitor/[admin/]#itemtype#/updateOrder (order comma-separated in POST)
	// TODO: implement itemtype checks
	function updateOrder($action) {

		$order_list = getPost("order");
		if(count($action) == 1 && $order_list) {

			$query = new Query();
			$order = explode(",", $order_list);

			for($i = 0; $i < count($order); $i++) {
				$item_id = $order[$i];
				$sql = "UPDATE ".$this->db." SET position = ".($i+1)." WHERE item_id = ".$item_id;
				$query->sql($sql);
			}

			message()->addMessage("Order updated");
			return true;
		}

		message()->addMessage("Order could not be updated - please refresh your browser", array("type" => "error"));
		return false;

	}




	// TAGS



	// addTag and deleteTag differ slightly in implementations
	// addTag must receive tag as post to avoid complications with slashes in tag value

	// /janitor/[admin/]#itemtype#/addTag/#item_id#
	// TODO: implement itemtype checks
	// tag is sent in $_POST
 	function addTag($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 2 && $this->validateList(array("tags"))) {


			$query = new Query();
			$item_id = $action[1];

			$tag = $this->getProperty("tags", "value");

			// full tag?
			if(preg_match("/([a-zA-Z0-9_]+):([^\b]+)/", $tag, $matches)) {
				$context = $matches[1];
				$value = $matches[2];

//				print "SELECT id FROM ".UT_TAG." WHERE context = '$context' AND value = '$value'<br>";
				if($query->sql("SELECT id FROM ".UT_TAG." WHERE context = '$context' AND value = '$value'")) {
					$tag_id = $query->result(0, "id");
				}
	//			print "INSERT INTO ".UT_TAG." VALUES(DEFAULT, '$context', '$value', DEFAULT)<br>";
				else if($query->sql("INSERT INTO ".UT_TAG." VALUES(DEFAULT, '$context', '$value', DEFAULT)")) {
					$tag_id = $query->lastInsertId();
				}

			}
			// tag id?
			else if(is_numeric($tag)) {
				// is it a valid tag_id
				if($query->sql("SELECT * FROM ".UT_TAG." WHERE id = $tag")) {
					$tag_id = $tag;
					$context = $query->result(0, "context");
					$value = $query->result(0, "value");
					
				}
			}

			if(isset($tag_id)) {

				if($query->sql("SELECT id FROM ".UT_TAGGINGS." WHERE item_id = $item_id AND tag_id = $tag_id")) {
					message()->addMessage("Tag already exists for this item", array("type" => "error"));
					return false;
				}
				else if($query->sql("INSERT INTO ".UT_TAGGINGS." VALUES(DEFAULT, $item_id, $tag_id)")) {
					message()->addMessage("Tag added");
					return array("item_id" => $item_id, "tag_id" => $tag_id, "context" => $context, "value" => $value);
				}

			}
		}

		message()->addMessage("Tag could not be added", array("type" => "error"));
		return false;
	}


	// delete tag 
	// tag can be complete context:value or tag_id (number)
	// /janitor/[admin/]#itemtype#/deleteTag/#item_id#/#tag_id|tag#
	// TODO: implement itemtype checks
 	function deleteTag($action) {

		if(count($action) == 3) {

			$query = new Query();
			$item_id = $action[1];
			$tag = $action[2];

			// is tag matching context:value
			if(preg_match("/([a-zA-Z0-9_]+):([^\b]+)/", $tag, $matches)) {
				$context = $matches[1];
				$value = $matches[2];

				if($query->sql("SELECT id FROM ".UT_TAG." WHERE context = '$context' AND value = '$value')")) {
					$tag_id = $query->result(0, "id");
				}
			}
			// is tag really tag_id
			else if(is_numeric($tag)) {
				// is it a valid tag_id
				if($query->sql("SELECT id FROM ".UT_TAG." WHERE id = $tag")) {
					$tag_id = $tag;
				}
			}

			if(isset($tag_id)) {
				if($query->sql("DELETE FROM ".UT_TAGGINGS." WHERE item_id = $item_id AND tag_id = $tag_id")) {
					message()->addMessage("Tag deleted");
					return true;
				}

			}
		}

		message()->addMessage("Tag could not be deleted", array("type" => "error"));
		return false;
	}




	// MEDIA 



	// custom function to add media
	// /janitor/[admin/]#itemtype#/addMedia/#item_id#
	// TODO: implement itemtype checks
	function addMedia($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();


		if(count($action) == 2) {

			$query = new Query();
			$item_id = $action[1];

			$query->checkDbExistence(UT_ITEMS_MEDIAE);

			if($this->validateList(array("mediae"), $item_id)) {
				$uploads = $this->upload($item_id, array("input_name" => "mediae", "auto_add_variant" => true));
				if($uploads) {

					$return_values = array();

					foreach($uploads as $upload) {
						$query->sql("INSERT INTO ".UT_ITEMS_MEDIAE." VALUES(DEFAULT, $item_id, '".$upload["name"]."', '".$upload["format"]."', '".$upload["variant"]."', '".$upload["width"]."', '".$upload["height"]."', '".$upload["filesize"]."', 0)");

						$return_values[] = array(
							"item_id" => $item_id, 
							"media_id" => $query->lastInsertId(), 
							"name" => $upload["name"], 
							"variant" => $upload["variant"], 
							"format" => $upload["format"], 
							"width" => $upload["width"], 
							"height" => $upload["height"],
							"filesize" => $upload["filesize"]
						);
					}

					return $return_values;
				}
			}
		}

		return false;
	}


	// custom function to add single media
	// /janitor/[admin/]#itemtype#/addSingleMedia/#item_id#/#variant#
	// TODO: implement itemtype checks
	function addSingleMedia($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();


		if(count($action) == 3) {
			$query = new Query();
			$IC = new Items();
			$item_id = $action[1];
			$variant = $action[2];

			$query->checkDbExistence(UT_ITEMS_MEDIAE);

			// Image main_media
			if($this->validateList(array($variant), $item_id)) {
				$uploads = $this->upload($item_id, array("input_name" => $variant, "variant" => $variant));
				if($uploads) {

					$name = $uploads[0]["name"];
					$variant = $uploads[0]["variant"];
					$format = $uploads[0]["format"];
					$width = isset($uploads[0]["width"]) ? $uploads[0]["width"] : 0;
					$height = isset($uploads[0]["height"]) ? $uploads[0]["height"] : 0;
					$filesize = $uploads[0]["filesize"];

					$query->sql("DELETE FROM ".UT_ITEMS_MEDIAE." WHERE item_id = $item_id AND variant = '".$variant."'");
					$query->sql("INSERT INTO ".UT_ITEMS_MEDIAE." VALUES(DEFAULT, $item_id, '".$name."', '".$format."', '".$variant."', '".$width."', '".$height."', '".$filesize."', 0)");

					return array(
						"item_id" => $item_id, 
						"media_id" => $query->lastInsertId(), 
						"name" => $name,
						"variant" => $variant, 
						"format" => $format, 
						"width" => $width,
						"height" => $height,
						"filesize" => $filesize
					);
				}
			}
		}

		return false;
	}


	// delete image - 3 parameters exactly
	// /janitor/[admin/]#itemtype#/deleteImage/#item_id#/#variant#
	// TODO: implement itemtype checks
	function deleteMedia($action) {

		if(count($action) == 3) {

			$item_id = $action[1];
			$variant = $action[2];

			$query = new Query();
			$fs = new FileSystem();

			$sql = "DELETE FROM ".UT_ITEMS_MEDIAE." WHERE item_id = ".$item_id." AND variant = '".$variant."'";
//			print $sql."<br>\n";
			if($query->sql($sql)) {

				$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id."/".$variant);
				$fs->removeDirRecursively(PRIVATE_FILE_PATH."/".$item_id."/".$variant);

				message()->addMessage("Media deleted in model");
				return true;
			}
		}

		message()->addMessage("Media could not be deleted", array("type" => "error"));
		return false;
	}



	// Update media name
	// /janitor/post/updateMediaName
	// TODO: implement itemtype checks
	// /janitor/[admin/]#itemtype#/updateMediaName/#item_id#/#variant#
	function updateMediaName($action) {

		if(count($action) == 3) {

			$item_id = $action[1];
			$variant = $action[2];

			$query = new Query();
			$name = getPost("name");

			$sql = "UPDATE ".UT_ITEMS_MEDIAE." SET name = '$name' WHERE item_id = ".$item_id." AND variant = '".$variant."'";
			if($query->sql($sql)) {
				message()->addMessage("Media name updated");
				return true;
			}
		}

		message()->addMessage("Media name could not be updated - please refresh your browser", array("type" => "error"));
		return false;
	}


	// update media order
	// TODO: implement itemtype checks
	// /janitor/[admin/]#itemtype#/updateMediaOrder (comma-separated order in POST)
	function updateMediaOrder($action) {

		$order_list = getPost("order");
		if(count($action) == 2 && $order_list) {

			$item_id = $action[1];

			$query = new Query();
			$order = explode(",", $order_list);

			for($i = 0; $i < count($order); $i++) {
				$media_id = $order[$i];
				$sql = "UPDATE ".UT_ITEMS_MEDIAE." SET position = ".($i+1)." WHERE id = $media_id AND item_id = $item_id";
				$query->sql($sql);
			}

			message()->addMessage("Media order updated");
			return true;
		}

		message()->addMessage("Media order could not be updated - refresh your browser", array("type" => "error"));
		return false;

	}


	// upload to item_id/variant
	// checks content of $_FILES, looks for uploaded file where type matches $type and uploads
	// supports video, audio, image, pdf
	function upload($item_id, $_options) {

		$fs = new FileSystem();


		$_input_name = "files";               // input name to check for files (default is files)

		$_variant = false;                    // variantname to save files under
		$proportion = false;                  // specific proportion for images and videos
		$width = false;                       // specific file width for images and videos
		$height = false;                      // specific file height for images and videos

		$min_height = false;                  // specific file min-height for images and videos
		$max_height = false;                  // specific file max-height for images and videos
		$min_width = false;                   // specific file min-width for images and videos
		$max_width = false;                   // specific file max-width for images and videos

		$min_bitrate = false;                 // specific file max-height for images and videos

		$formats = false;                     // jpg,png,git,mov,mp4,pdf,etc

		$auto_add_variant = false;            // automatically add variant-key for each file


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "input_name"          : $_input_name          = $_value; break;

					case "variant"             : $_variant             = $_value; break;
					case "proportion"          : $proportion           = $_value; break;
					case "width"               : $width                = $_value; break;
					case "height"              : $height               = $_value; break;
					case "min_width"           : $min_width            = $_value; break;
					case "min_height"          : $min_height           = $_value; break;
					case "max_width"           : $max_width            = $_value; break;
					case "max_height"          : $max_height           = $_value; break;

					case "formats"             : $formats              = $_value; break;

					case "auto_add_variant"    : $auto_add_variant     = $_value; break;
				}
			}
		}

		$uploads = array();

		// print "files:<br>";
		// print_r($_FILES);
		// print "post:<br>";
		// print_r($_POST);


		if(isset($_FILES[$_input_name])) {
//			print "input_name:" . $_input_name;
//			print_r($_FILES[$_input_name]);

			foreach($_FILES[$_input_name]["name"] as $index => $value) {
				if(!$_FILES[$_input_name]["error"][$index] && file_exists($_FILES[$_input_name]["tmp_name"][$index])) {

					$upload = array();
					$upload["name"] = $value;

					$extension = false;
					$temp_file = $_FILES[$_input_name]["tmp_name"][$index];
					$temp_type = $_FILES[$_input_name]["type"][$index];
					$temp_extension = mimetypeToExtension($temp_type);

					$upload["filesize"] = filesize($temp_file);
					$upload["format"] = $temp_extension;
					$upload["type"] = $temp_type;


					// File type check is deprecated and moved to model validation
					// Consider doing check still - but in different way
					// specify considerations and validation flow
					
					// print "#".$filegroup.", ".$temp_type.", match:".preg_match("/".$filegroup."/", $temp_type)."#";
					// if(preg_match("/".$filegroup."/", $temp_type)) {

					// is uploaded format acceptable?
					if(($upload["format"] && (preg_match("/".$upload["format"]."/", $formats)) || !$formats)) {

						// define variant value
						if($auto_add_variant) {
							$upload["variant"] = randomKey(8);
							$variant = "/".$upload["variant"];
						}
						else if($_variant) {
							$upload["variant"] = $_variant;
							$variant = "/".$upload["variant"];
						}
						else {
							$variant = "";
							$upload["variant"] = $_variant;
						}

//						print_r($upload);


//						print "correct group:" . $filegroup . ", " . $temp_type . ", " . $variant;

						// video upload
						if(preg_match("/video/", $temp_type)) {

							include_once("classes/helpers/video.class.php");
							$Video = new Video();

							// check if we can get relevant info about movie
							$info = $Video->info($temp_file);
							if($info) {

								// TODO: add bitrate detection
								// TODO: add duration detection
								$upload["width"] = $info["width"];
								$upload["height"] = $info["height"];

								if(
									(!$proportion || round($proportion, 2) == round($upload["width"] / $upload["height"], 2)) &&
									(!$width || $width == $upload["width"]) &&
									(!$height || $height == $upload["height"]) &&
									(!$min_width || $min_width <= $upload["width"]) &&
									(!$min_height || $min_height <= $upload["height"]) &&
									(!$max_width || $max_width >= $upload["width"]) &&
									(!$max_height || $max_height >= $upload["height"])
								) {

									$output_file = PRIVATE_FILE_PATH."/".$item_id.$variant."/".$upload["format"];

	//								print $output_file . "<br>";
									$fs->removeDirRecursively(dirname($output_file));
									$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id.$variant);
									$fs->makeDirRecursively(dirname($output_file));

									copy($temp_file, $output_file);
									$upload["file"] = $output_file;
									$uploads[] = $upload;
									unlink($temp_file);

									message()->addMessage("Video uploaded (".$upload["name"].")");
								}
							}

						}
						// audio upload
						else if(preg_match("/audio/", $temp_type)) {

							include_once("classes/helpers/audio.class.php");
							$Audio = new Audio();

							// check if we can get relevant info about audio file
 							$info = $Audio->info($temp_file);
 							if($info) {

								// TODO: add bitrate detection
								// TODO: add duration detection

 								$output_file = PRIVATE_FILE_PATH."/".$item_id.$variant."/mp3";
// 
// 								print $output_file . "<br>";
 								$fs->removeDirRecursively(dirname($output_file));
 								$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id.$variant);
 								$fs->makeDirRecursively(dirname($output_file));

								copy($temp_file, $output_file);
								$upload["file"] = $output_file;
								$uploads[] = $upload;
								unlink($temp_file);

								message()->addMessage("Audio uploaded (".$upload["name"].")");
							}

						}
						// image upload
						else if(preg_match("/image/", $temp_type)) {

							$image = new Imagick($temp_file);

							// check if we can get relevant info about image
							$info = $image->getImageFormat();
							if($info) {

								$upload["width"] = $image->getImageWidth();
								$upload["height"] = $image->getImageHeight();

								if(
									(!$proportion || round($proportion, 2) == round($upload["width"] / $upload["height"], 2)) &&
									(!$width || $width == $upload["width"]) &&
									(!$height || $height == $upload["height"]) &&
									(!$min_width || $min_width <= $upload["width"]) &&
									(!$min_height || $min_height <= $upload["height"]) &&
									(!$max_width || $max_width >= $upload["width"]) &&
									(!$max_height || $max_height >= $upload["height"])
								) {

									$output_file = PRIVATE_FILE_PATH."/".$item_id.$variant."/".$upload["format"];

//									print $output_file . "<br>";
									$fs->removeDirRecursively(dirname($output_file));
									$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id.$variant);
									$fs->makeDirRecursively(dirname($output_file));

									copy($temp_file, $output_file);
									$upload["file"] = $output_file;
									$uploads[] = $upload;
									unlink($temp_file);

									message()->addMessage("Image uploaded (".$upload["name"].")");
								}
							}
						}
						// pdf upload
						else if(preg_match("/pdf/", $temp_type)) {

							$output_file = PRIVATE_FILE_PATH."/".$item_id.$variant."/".$upload["format"];

							$fs->removeDirRecursively(dirname($output_file));
							$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id.$variant);
							$fs->makeDirRecursively(dirname($output_file));

							copy($temp_file, $output_file);
							$upload["file"] = $output_file;
							$uploads[] = $upload;
							unlink($temp_file);

							message()->addMessage("PDF uploaded (".$upload["name"].")");

						}

					}
					// else {
					// 	message()->addMessage("Format is not supported (".$upload["name"].")", array("type" => "error"));
					// }

				}
				// // file error
				// else {
				// 	message()->addMessage("Unknown file problem", array("type" => "error"));
				// }
			}

		}

		return $uploads;
	}




	// HTML EDITOR



	// custom function to add html media
	// TODO: implement itemtype checks
	// /janitor/[admin/]#itemtype#/addHTMLMedia/#item_id#
	function addHTMLMedia($action) {

		if(count($action) == 2) {
			$query = new Query();
			$IC = new Items();
			$item_id = $action[1];

			$query->checkDbExistence(UT_ITEMS_MEDIAE);

//			$variant = stringOr(getPost("variant"), "HTML-".randomKey(8));


			// Image single_media
			$uploads = $this->upload($item_id, array("input_name" => "htmleditor_media", "variant" => "HTML-".randomKey(8)));
			if($uploads) {
				$query->sql("DELETE FROM ".UT_ITEMS_MEDIAE." WHERE item_id = $item_id AND variant = '".$uploads[0]["variant"]."'");
				$query->sql("INSERT INTO ".UT_ITEMS_MEDIAE." VALUES(DEFAULT, $item_id, '".$uploads[0]["name"]."', '".$uploads[0]["format"]."', '".$uploads[0]["variant"]."', '".$uploads[0]["width"]."', '".$uploads[0]["height"]."', '".$uploads[0]["filesize"]."', 0)");

				return array(
					"item_id" => $item_id, 
					"media_id" => $query->lastInsertId(), 
					"name" => $uploads[0]["name"], 
					"variant" => $uploads[0]["variant"], 
					"format" => $uploads[0]["format"], 
					"width" => $uploads[0]["width"],
					"height" => $uploads[0]["height"],
					"filesize" => $uploads[0]["filesize"]
				);
			}
		}

		return false;
	}

	// delete media from HTML editor - 3 parameters exactly
	// TODO: implement itemtype checks
	// /janitor/[admin/]#itemtype#/deleteHTMLMedia/#item_id#/#variant#
	function deleteHTMLMedia($action) {

		if(count($action) == 3) {

			$query = new Query();
			$fs = new FileSystem();
			$item_id = $action[1];
			$variant = $action[2];


			$sql = "DELETE FROM ".UT_ITEMS_MEDIAE." WHERE item_id = ".$item_id." AND variant = '".$variant."'";
			if($query->sql($sql)) {
				$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id."/".$variant);
				$fs->removeDirRecursively(PRIVATE_FILE_PATH."/".$item_id."/".$variant);

				message()->addMessage("Media deleted");
				return true;
			}
		}

		message()->addMessage("Media could not be deleted", array("type" => "error"));
		return false;
	}



	// custom function to add html file
	// TODO: implement itemtype checks
	// /janitor/[admin/]#itemtype#/addHTMLFile/#item_id#
	function addHTMLFile($action) {

		if(count($action) == 2) {
			$query = new Query();
			$IC = new Items();
			$item_id = $action[1];

			$query->checkDbExistence(UT_ITEMS_MEDIAE);

//			$variant = stringOr(getPost("variant"), "HTML-".randomKey(8));


			// Image single_media
			$uploads = $this->uploadHTMLFile($item_id, array("input_name" => "htmleditor_file"));
			if($uploads) {
				$query->sql("DELETE FROM ".UT_ITEMS_MEDIAE." WHERE item_id = $item_id AND variant = '".$uploads[0]["variant"]."'");
				$query->sql("INSERT INTO ".UT_ITEMS_MEDIAE." VALUES(DEFAULT, $item_id, '".$uploads[0]["name"]."', '".$uploads[0]["format"]."', '".$uploads[0]["variant"]."', '0', '0', '".$uploads[0]["filesize"]."', 0)");

				return array(
					"item_id" => $item_id, 
					"media_id" => $query->lastInsertId(), 
					"name" => $uploads[0]["name"], 
					"variant" => $uploads[0]["variant"], 
					"format" => $uploads[0]["format"], 
					"filesize" => $uploads[0]["filesize"]
				);
			}
		}

		return false;
	}

	// delete file from HTML editor - 3 parameters exactly
	// TODO: implement itemtype checks
	// /janitor/[admin/]#itemtype#/deleteHTMLFile/#item_id#/#variant#
	function deleteHTMLFile($action) {

		if(count($action) == 3) {

			$query = new Query();
			$fs = new FileSystem();
			$item_id = $action[1];
			$variant = $action[2];


			$sql = "DELETE FROM ".UT_ITEMS_MEDIAE." WHERE item_id = ".$item_id." AND variant = '".$variant."'";
			if($query->sql($sql)) {
				$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id."/".$variant);
				$fs->removeDirRecursively(PRIVATE_FILE_PATH."/".$item_id."/".$variant);

				message()->addMessage("File deleted");
				return true;
			}
		}

		message()->addMessage("File could not be deleted", array("type" => "error"));
		return false;
	}


	// Upload handler for HTML editor
	// Downloadable file - Uploads PDF and ZIP directly, ZIPs everything else
	function uploadHTMLFile($item_id, $_options) {

		$fs = new FileSystem();


		$_input_name = "files";               // input name to check for files (default is files)

		$_variant = false;                    // variantname to save files under
		$formats = false;                     // jpg,png,git,mov,mp4,pdf,etc
		$auto_add_variant = true;            // automatically add variant-key for each file


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "input_name"          : $_input_name          = $_value; break;

					case "variant"             : $_variant             = $_value; break;
					case "formats"             : $formats              = $_value; break;

					case "auto_add_variant"    : $auto_add_variant     = $_value; break;
				}
			}
		}

		$uploads = array();

//		print "files:<br>";
//		print_r($_FILES);
		// print "post:<br>";
		// print_r($_POST);


		if(isset($_FILES[$_input_name])) {
			// print "input_name:" . $_input_name;
			// print_r($_FILES[$_input_name]);

			if(!$_FILES[$_input_name]["error"] && file_exists($_FILES[$_input_name]["tmp_name"])) {

				$upload = array();
				$upload["name"] = $_FILES[$_input_name]["name"];

				$extension = false;
				$temp_file = $_FILES[$_input_name]["tmp_name"];
				$temp_type = $_FILES[$_input_name]["type"];
//				$temp_extension = mimetypeToExtension($temp_type);
				$upload["type"] = $temp_type;

				// define variant value
				if($auto_add_variant) {
					$upload["variant"] = "HTML-".randomKey(8);
					$variant = "/".$upload["variant"];
				}
				else if($_variant) {
					$upload["variant"] = $_variant;
					$variant = "/".$upload["variant"];
				}
				else {
					$variant = "";
					$upload["variant"] = $_variant;
				}

				// pdf upload
				if(preg_match("/pdf/", $temp_type)) {

					$upload["format"] = "pdf";

					$output_file = PRIVATE_FILE_PATH."/".$item_id.$variant."/".$upload["name"];
					$public_file = PUBLIC_FILE_PATH."/".$item_id.$variant."/".superNormalize(substr(preg_replace("/\.[a-zA-Z1-9]{3,4}$/", "", $upload["name"]), 0, 40)).".pdf";
					

					$fs->removeDirRecursively(dirname($output_file));
					$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id.$variant);

					$fs->makeDirRecursively(dirname($output_file));
					$fs->makeDirRecursively(PUBLIC_FILE_PATH."/".$item_id.$variant);

					copy($temp_file, $output_file);
					copy($temp_file, $public_file);

					$upload["name"] = basename($public_file);
					$upload["filesize"] = filesize($public_file);
					$upload["file"] = $output_file;
					$uploads[] = $upload;

					unlink($temp_file);
//						unlink($output_file);

					message()->addMessage("PDF uploaded (".$upload["name"].")");

				}
				// zip upload
				else if(preg_match("/zip/", $temp_type)) {

					$upload["format"] = "zip";

					$output_file = PRIVATE_FILE_PATH."/".$item_id.$variant."/".$upload["name"];
					$public_file = PUBLIC_FILE_PATH."/".$item_id.$variant."/".superNormalize(substr(preg_replace("/\.[a-zA-Z1-9]{3,4}$/", "", $upload["name"]), 0, 40)).".zip";
					

					$fs->removeDirRecursively(dirname($output_file));
					$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id.$variant);

					$fs->makeDirRecursively(dirname($output_file));
					$fs->makeDirRecursively(PUBLIC_FILE_PATH."/".$item_id.$variant);

					copy($temp_file, $output_file);
					copy($temp_file, $public_file);

					$upload["name"] = basename($public_file);
					$upload["filesize"] = filesize($public_file);
					$upload["file"] = $output_file;
					$uploads[] = $upload;

					unlink($temp_file);
//						unlink($output_file);

					message()->addMessage("ZIP uploaded (".$upload["name"].")");

				}
				// not PDF or ZIP, zip it for download
				else {

					$upload["format"] = "zip";

					$output_file = PRIVATE_FILE_PATH."/".$item_id.$variant."/".$upload["name"] ;
					$zip_name = superNormalize(substr(preg_replace("/\.[a-zA-Z1-9]{3,4}$/", "", $upload["name"]), 0, 40));
					$public_file = PUBLIC_FILE_PATH."/".$item_id.$variant."/".$zip_name.".zip";

					// Replace uploaded file with zipped result to avoid having different file formats for PUBLIC and PRIVATE folders
					$private_file_zipped = PRIVATE_FILE_PATH."/".$item_id.$variant."/zip";

					$fs->removeDirRecursively(dirname($output_file));
					$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id.$variant);

					$fs->makeDirRecursively(dirname($output_file));
					$fs->makeDirRecursively(PUBLIC_FILE_PATH."/".$item_id.$variant);

					
					copy($temp_file, $output_file);

//					print "create new zip:" . $zip_file . "<br>";

					$zip = new ZipArchive();
					$zip->open($public_file, ZipArchive::CREATE);
					$zip->addFile($output_file, basename($output_file));
					$zip->close();


					// Replace private file with zipped version
					copy($public_file, $private_file_zipped);
					unlink($output_file);


					$upload["filesize"] = filesize($public_file);
					$upload["file"] = $public_file;
					$upload["name"] = basename($public_file);
					$uploads[] = $upload;

					unlink($temp_file);
//						unlink($output_file);

					message()->addMessage("File uploaded (".$upload["name"].")");

				}

			}
			// file group error
			else {
				message()->addMessage("File problem (".$upload["name"].")");
			}

		}

		return $uploads;
	}





	// COMMENTS

	// add comment to item
	// comment is sent in $_POST
	// /janitor/[admin/]#itemtype#/addComment/#item_id#
	function addComment($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();


		if(count($action) == 2) {

			$query = new Query();
			$item_id = $action[1];

			if($this->validateList(array("item_comment"), $item_id)) {

				$user_id = session()->value("user_id");
				$comment = $this->getProperty("item_comment", "value");

				if($query->sql("INSERT INTO ".UT_ITEMS_COMMENTS." VALUES(DEFAULT, $item_id, $user_id, '$comment', DEFAULT)")) {
					message()->addMessage("Comment added");

					$comment_id = $query->lastInsertId();
					$IC = new Items();
					$new_comment = $IC->getComments(array("comment_id" => $comment_id));
					$new_comment["created_at"] = date("Y-m-d, H:i", strtotime($new_comment["created_at"]));
					return $new_comment;
				}


			}

		}

		message()->addMessage("Comment could not be added", array("type" => "error"));
		return false;
	}

	// update comment
	// /janitor/[admin/]#itemtype#/updateComment/#item_id#/#comment_id#
	function updateComment($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();


		if(count($action) == 3) {

			$query = new Query();
			$item_id = $action[1];
			$comment_id = $action[2];

			if($this->validateList(array("item_comment"), $item_id)) {

				$comment = $this->getProperty("item_comment", "value");

				if($query->sql("UPDATE ".UT_ITEMS_COMMENTS." SET comment = '$comment' WHERE id = $comment_id AND item_id = $item_id")) {
					message()->addMessage("Comment updated");
					return true;
				}


			}

		}

		message()->addMessage("Comment could not be updated", array("type" => "error"));
		return false;
	}

	// delete comment
	// /janitor/[admin/]#itemtype#/deleteComment/#item_id#/#comment_id#
	// TODO: implement itemtype checks
 	function deleteComment($action) {

		if(count($action) == 3) {

			$query = new Query();
			$item_id = $action[1];
			$comment_id = $action[2];

			if($query->sql("DELETE FROM ".UT_ITEMS_COMMENTS." WHERE item_id = $item_id AND id = $comment_id")) {

				message()->addMessage("Comment deleted");
				return true;
			}
		}

		message()->addMessage("Comment could not be deleted", array("type" => "error"));
		return false;
	}



	// RATINGS

	// add rating to item
	// rating is sent in $_POST
	// /janitor/[admin/]#itemtype#/addRating/#item_id#
	function addRating($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 2) {

			$query = new Query();
			$item_id = $action[1];

			if($this->validateList(array("item_rating"), $item_id)) {

				$user_id = session()->value("user_id");
				$rating = $this->getProperty("item_rating", "value");

				$sql = "INSERT INTO ".UT_ITEMS_RATINGS." VALUES(DEFAULT, $item_id, $user_id, '$rating', DEFAULT)";
				// debug($sql);
				if($query->sql($sql)) {
					message()->addMessage("Rating added");

					$rating_id = $query->lastInsertId();
					$IC = new Items();
					$new_rating = $IC->getRatings(array("rating_id" => $rating_id));
					$new_rating["created_at"] = date("Y-m-d, H:i", strtotime($new_rating["created_at"]));
					return $new_rating;
				}


			}

		}

		message()->addMessage("Rating could not be added", array("type" => "error"));
		return false;
	}

	// update rating
	// /janitor/[admin/]#itemtype#/updateRating/#item_id#/#rating_id#
	function updateRating($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();


		if(count($action) == 3) {

			$query = new Query();
			$item_id = $action[1];
			$rating_id = $action[2];

			if($this->validateList(array("item_rating"), $item_id)) {

				$rating = $this->getProperty("item_rating", "value");

				if($query->sql("UPDATE ".UT_ITEMS_RATINGS." SET rating = '$rating' WHERE id = $rating_id AND item_id = $item_id")) {
					message()->addMessage("Rating updated");
					return true;
				}


			}

		}

		message()->addMessage("Rating could not be updated", array("type" => "error"));
		return false;
	}

	// delete rating
	// /janitor/[admin/]#itemtype#/deleteRating/#item_id#/#rating_id#
	// TODO: implement itemtype checks
 	function deleteRating($action) {

		if(count($action) == 3) {

			$query = new Query();
			$item_id = $action[1];
			$rating_id = $action[2];

			if($query->sql("DELETE FROM ".UT_ITEMS_RATINGS." WHERE item_id = $item_id AND id = $rating_id")) {

				message()->addMessage("Rating deleted");
				return true;
			}
		}

		message()->addMessage("Rating could not be deleted", array("type" => "error"));
		return false;
	}





	// PRICES

	// add price to item
	// Price info sent in $_POST
	// /janitor/[admin/]#itemtype#/addPrice/#item_id#
 	function addPrice($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();


		if(count($action) == 2) {

			$query = new Query();
			$item_id = $action[1];

			if($this->validateList(array("item_price", "item_price_currency", "item_price_vatrate", "item_price_type", "item_price_quantity"), $item_id)) {

				$price = $this->getProperty("item_price", "value");
				$currency = $this->getProperty("item_price_currency", "value");
				$vatrate = $this->getProperty("item_price_vatrate", "value");
				$type = $this->getProperty("item_price_type", "value");
				if($type == "bulk") {
					$quantity = $this->getProperty("item_price_quantity", "value");
					// check quantity value for bulk price
					if(!is_numeric($quantity) || intval($quantity) != floatval($quantity) || intval($quantity) <= 1) {
						message()->addMessage("Invalid quantity for bulk price", array("type" => "error"));
						return false;
					}

					// bulk items price can only exist once for specific quantity
					$sql = "SELECT id FROM ".UT_ITEMS_PRICES." WHERE item_id = $item_id AND currency = '$currency' AND type = '$type' AND quantity = $quantity";
					// debug($sql);

					if($query->sql($sql)) {
						message()->addMessage("Item already has bulk price for this type, currency and quantity", array("type" => "error"));
						return false;
					}

				}
				else {
					// default and offer price can only exist once for an item
					$sql = "SELECT id FROM ".UT_ITEMS_PRICES." WHERE item_id = $item_id AND currency = '$currency' AND type = '$type'";
					// debug($sql);

					if($query->sql($sql)) {
						message()->addMessage("Item already has price for this type and currency", array("type" => "error"));
						return false;
					}

					$quantity = "DEFAULT";
				}

				// replace , with . to make valid number
				$price = preg_replace("/,/", ".", $price);

				$sql = "INSERT INTO ".UT_ITEMS_PRICES." VALUES(DEFAULT, $item_id, '$price', '$currency', $vatrate, '$type', $quantity)";
				// debug($sql);

				if($query->sql($sql)) {
					message()->addMessage("Price added");

					$price_id = $query->lastInsertId();
					$IC = new Items();
					$new_price = $IC->getPrices(array("price_id" => $price_id));
					// add default formatted price to return result for ease of use
					$new_price["formatted_price"] = formatPrice($new_price, array("vat" => true));
					return $new_price;
				}

			}

		}

		message()->addMessage("Price could not be added", array("type" => "error"));
		return false;

	}


	// delete price
	// /janitor/[admin/]#itemtype#/deletePrice/#item_id#/#price_id#
	// TODO: implement itemtype checks
 	function deletePrice($action) {

		if(count($action) == 3) {

			$query = new Query();
			$item_id = $action[1];
			$price_id = $action[2];

			$sql = "DELETE FROM ".UT_ITEMS_PRICES." WHERE item_id = $item_id AND id = $price_id";
			if($query->sql($sql)) {
				message()->addMessage("Price deleted");
				return true;
			}

		}

		message()->addMessage("Price could not be deleted", array("type" => "error"));
		return false;
	}



	// update subscription method
	// /janitor/[admin/]#itemtype#/updateSubscriptionMethod/#item_id#
	// subscription method is sent in $_POST
	// TODO: implement itemtype checks
	// TODO: also update all existing subscriptions of selected item (if method changes, expriry date changes)
 	function updateSubscriptionMethod($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();


		if(count($action) == 2) {

			$query = new Query();
			$item_id = $action[1];

			if($this->validateList(array("item_subscription_method"), $item_id)) {

				$subscription_method = $this->getProperty("item_subscription_method", "value");

				// insert or update
				if($subscription_method) {

					$sql = "SELECT id FROM ".UT_ITEMS_SUBSCRIPTION_METHOD." WHERE item_id = $item_id";
//					print $sql;
					if($query->sql($sql)) {
				
						if($query->sql("UPDATE ".UT_ITEMS_SUBSCRIPTION_METHOD." SET subscription_method_id = '$subscription_method' WHERE item_id = $item_id")) {
							message()->addMessage("Subscription method updated");

							$IC = new Items();
							$subscription_method = $IC->getSubscriptionMethod(array("item_id" => $item_id));
							return $subscription_method;
						}
					
					}
					else {

						$sql = "INSERT INTO ".UT_ITEMS_SUBSCRIPTION_METHOD." VALUES(DEFAULT, $item_id, $subscription_method)";
//						print $sql;

						if($query->sql($sql)) {
							message()->addMessage("Subscription method added");

							$IC = new Items();
							$subscription_method = $IC->getSubscriptionMethod(array("item_id" => $item_id));
							return $subscription_method;
						}
					
					
					}

				}
				// subscription_method is empty - delete
				else {

					$sql = "DELETE FROM ".UT_ITEMS_SUBSCRIPTION_METHOD." WHERE item_id = $item_id";
					if($query->sql($sql)) {
						message()->addMessage("Subscription method deleted");
						return true;
					}

				}
			}
		}

		message()->addMessage("Subscription method could not be changed", array("type" => "error"));
		return false;

	}


}
