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
	* Change status of Item
	* TODO: Implement data validation before allowing enabling 
	*/
	# /janitor/[admin/]#itemtype#/status/#item_id#/#new_status#
	function status($action) {
		global $page;

		if(count($action) == 3) {

//			$itemtype = $action[0];

			$item_id = $action[1];
			$status = $action[2];
			
			$query = new Query();
			$IC = new Items();

			$model = $IC->typeObject($this->itemtype);
			$item = $IC->getItem(array("id" => $item_id, "extend" => array("all" => true)));
			
			// delete item + itemtype + files
			if(isset($this->status_states[$status]) && $query->sql("SELECT id FROM ".UT_ITEMS." WHERE id = $item_id AND itemtype = '$this->itemtype'")) {
			
				// add callback to 'enabling', if available
				if($status === "1" && method_exists($model, "enabling")) {
					
					$pre_enable_state = true;
					$pre_enable_state = $model->enabling($item);
					if($pre_enable_state === false) {
						
						return false;
					}
				}


				$query->sql("UPDATE ".UT_ITEMS." SET status = $status WHERE id = $item_id");
				message()->addMessage("Item ".$this->status_states[$status]);
				
				// add callback to 'enabled' and/or 'disabled', if available
				if($status === "1" && method_exists($model, "enabled")) {
					$model->enabled($item);
				}
				elseif($status === "0" && method_exists($model, "disabled")) {
					$model->disabled($item);
				}
				
				return true;
			}
		}

		
		message()->addMessage("Item status could not be changed", array("type" => "error"));
		return false;

	}

	/**
	* Change owner of Item
	*/
	# /§controller#/#itemtype#/owner/#item_id#
	function owner($action) {
		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 2) {

//			$itemtype = $action[0];

			$item_id = $action[1];
			$new_owner = $this->getProperty("item_ownership", "value");

			$query = new Query();


			// Update item owner
			if(
				$query->sql("SELECT id FROM ".UT_ITEMS." WHERE id = $item_id AND itemtype = '$this->itemtype'")
					&&
				$query->sql("SELECT id FROM ".SITE_DB.".users WHERE id = $new_owner")
				
			) {

				$sql = "UPDATE ".UT_ITEMS." SET user_id = $new_owner WHERE id = $item_id";
				$query->sql($sql);

				message()->addMessage("Item owner updated");


				// add log
				$page->addLog("ItemType->owner ($item_id, $new_owner)");

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
			// debug(["save name", $_POST]);

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

				$files_inputs = array();

				foreach($entities as $name => $entity) {
					if($entity["value"] !== false && !preg_match("/^(published_at|status|htmleditor_file|htmleditor_media)$/", $name)) {

						if(!preg_match("/^(files|tags)$/", $entity["type"])) {

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
						// Store files input names for processing after main data update
						else if(preg_match("/^files$/", $entity["type"])) {

							array_push($files_inputs, $name);
						}

					}

				}

				if($values) {

					$sql = "INSERT INTO ".$this->db." SET id = DEFAULT,item_id = $item_id," . implode(",", $values);
					// debug([$sql]);

					if($query->sql($sql)) {

						// item cannot be enabled without datacheck
						// use internal check to ensure datacheck
						$this->status(array("status", $item_id, getPost("status")));


						// implementing files, tags, comments etc in basic save?
						// it's a bigger question
						// - are files, tags, prices, comments and ratings all external features or integrated parts of an Item
						// - They are integrated parts but only files can added on creation

						// Only files can be included in a save

						// Saving files
						if($files_inputs) {
							foreach($files_inputs as $files_input) {
								$files_max_count = $this->getProperty($files_input, "max");

								if(!$files_max_count || $files_max_count === 1) {

									$this->addSingleMedia(["addSingleMedia", $item_id, $files_input]);
								}
								else {

									$this->addMedia(["addMedia", $item_id, $files_input]);
								}
							}
						}


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


						// itemtype post save handler?
						// TODO: Consider if failed postSave should have consequences
						if(method_exists($this, "saved")) {
							$this->saved($item_id);
						}


						// Add message
						message()->addMessage("Item saved");

						// add log
						$page->addLog("ItemType->save ($item_id)");

						// return selected data array
						$IC = new Items();
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
		if($query->sql($sql)) {

			// return new item
			return $query->lastInsertId();

		}
		return false;
	}




	// UPDATE


	/**
	* Update item type
	*/
	# /#controller#/update/#item_id#
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

			$files_inputs = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false && !preg_match("/^(published_at|status|user_id|htmleditor_file|htmleditor_media)$/", $name)) {

					if(!preg_match("/^(files|tags)$/", $entity["type"])) {

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
					// Store files input names for processing after main data update
					else if(preg_match("/^files$/", $entity["type"])) {
						array_push($files_inputs, $name);
					}

				}

			}

			if($this->validateList($names, $item_id) && $this->updateItem($item_id)) {


				// add existing data to version-control-table
				$query->versionControl($item_id, $values);


				// Update values
				$sql = "UPDATE ".$this->db." SET ".implode(",", $values)." WHERE item_id = ".$item_id;
				// debug([$sql]);
				if($query->sql($sql)) {


					// implementing files, tags, comments etc in basic save?
					// it's a bigger question
					// - are files, tags, prices, comments and ratings all external features or integrated parts of an Item
					// - They are integrated parts but only files can added on creation

					// Only files can be included in a save

					// Saving files
					if($files_inputs) {

						foreach($files_inputs as $files_input) {
							$files_max_count = $this->getProperty($files_input, "max");

							if(!$files_max_count || $files_max_count === 1) {

								$this->addSingleMedia(["addSingleMedia", $item_id, $files_input]);
							}
							else {

								$this->addMedia(["addMedia", $item_id, $files_input]);
							}
						}
					}


					$sindex = false;
					// look for local sindex method 
					// implement sindexBase function in your itemtype class to use special sindexes
					if(method_exists($this, "sindexBase")) {
						$sindex = $this->sindexBase($item_id);
					}
					// Use name as default
					else if(array_search("name", $names) !== false) {
						$sindex = $entities["name"]["value"];
					}

					// create new sindex
					if($sindex) {
						$this->sindex($sindex, $item_id);
					}


					// itemtype post update handler?
					// TODO: Consider if failed postSave should have consequences
					// TODO: risky - can cause endless loop - if postUpdate, makes update, makes update, makes update
					if(method_exists($this, "updated")) {
						$this->updated($item_id);
					}


					// Add message
					message()->addMessage("Item updated");

					// add log
					$page->addLog("ItemType->update ($item_id)");

					$IC = new Items();
					return $IC->getItem(array("id" => $item_id, "extend" => array("all" => true)));

				}
			}

		}

		message()->addMessage("Item could not be updated", array("type" => "error"));
		return false;
	}

	// update root item
	function updateItem($item_id) {
		// debug(["update item"]);

		$query = new Query();

		// is published_at valid?
		if($this->validateList(array("published_at"))) {
			$sql = "UPDATE ".UT_ITEMS." SET published_at='".toTimestamp($this->getProperty("published_at", "value"))."' WHERE id = $item_id";
			// debug([$sql]);
			$query->sql($sql);
		}
		else {
			return false;
		}


		// // updating user id?
		// $user_id = $this->getProperty("user_id", "value");
		// if($user_id && $this->validateList(array("user_id"))) {
		// 	$sql = "UPDATE ".UT_ITEMS." SET user_id=$user_id WHERE id = $item_id";
		// 	$query->sql($sql);
		// }

		// Update modified_at
		$sql = "UPDATE ".UT_ITEMS." SET modified_at=CURRENT_TIMESTAMP WHERE id = $item_id";
		$query->sql($sql);

		return true;
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




	// DUPLICATE


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
								preg_match_all("/\<div class\=\"(media|file) item_id\:[\d]+ variant\:HTMLEDITOR-[A-Za-z0-9\-_]+ name/", $value, $mediae_matches);
								if($mediae_matches) {

									// Loop over media div's
									foreach($mediae_matches[0] as $media_match) {

										// debug($media_match);

										preg_match("/(file|media) item_id\:([\d]+) variant\:(HTMLEDITOR-[A-Za-z0-9\-_]+)/", $media_match, $media_details);
										if($media_details) {
											// Get item_id and variant for each embedded media
											list(,$type, $old_item_id, $old_variant) = $media_details;


											// Get full media data set
											$sql = "SELECT * FROM ".UT_ITEMS_MEDIAE." WHERE item_id = $old_item_id AND variant = '$old_variant'";
											if($query->sql($sql)) {
												$media = $query->result(0);


												$new_variant = "HTMLEDITOR-".$property."-".randomKey(8);


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


					message()->resetMessages();
					message()->addMessage("Item duplicated");

					// get and return new device (id will be used to redirect to new item page)
					$item = $IC->getItem(array("id" => $cloned_item["id"]));
					return $item;

				}

			}

		}
		
		message()->resetMessages();
		message()->addMessage("Item could not be duplicated", ["type" => "error"]);
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
	// /#controller#/addMedia/#item_id#/#variant#
	function addMedia($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();


		if(count($action) == 3) {

			$query = new Query();
			$item_id = $action[1];
			$variant = $action[2];

			$query->checkDbExistence(UT_ITEMS_MEDIAE);


			// Attempt to upload (upload will validate input)
			$uploads = $this->upload($item_id, [
				"input_name" => $variant, 
				"auto_add_variant" => true
			]);

			// Successful upload
			if($uploads) {

				$return_values = array();

				foreach($uploads as $upload) {

					$name = $upload["name"];
					$variant = $upload["variant"];
					$format = $upload["format"];
					$width = $upload["width"];
					$height = $upload["height"];
					$filesize = $upload["filesize"];

					// Add new assets
					// $query->sql("INSERT INTO ".UT_ITEMS_MEDIAE." VALUES(DEFAULT, $item_id, '".$upload["name"]."', '".$upload["format"]."', '".$upload["variant"]."', '".$upload["width"]."', '".$upload["height"]."', '".$upload["filesize"]."', 0)");
					$query->sql("INSERT INTO ".UT_ITEMS_MEDIAE." VALUES(DEFAULT, $item_id, '$name', '$format', '$variant', ".($width ? "'$width'" : "DEFAULT").", ".($height ? "'$height'" : "DEFAULT").", '$filesize', 0)");

					// return upload data in standard mediae array
					$return_values[$upload["variant"]] = array(
						"id" => $query->lastInsertId(), 
						"item_id" => $item_id, 
						"name" => $upload["name"], 
						"variant" => $upload["variant"], 
						"format" => $upload["format"], 
						"width" => $upload["width"], 
						"height" => $upload["height"],
						"filesize" => $upload["filesize"]
					);

				}

				// return upload data
				return ["mediae" => $return_values];
			}

		}

		return false;
	}


	// custom function to add single media
	// /#controller#/addSingleMedia/#item_id#/#variant#
	function addSingleMedia($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();


		if(count($action) == 3) {
			$query = new Query();
			$IC = new Items();
			$item_id = $action[1];
			$variant = $action[2];

			$query->checkDbExistence(UT_ITEMS_MEDIAE);

			// Attempt to upload (upload will validate input)
			$uploads = $this->upload($item_id, [
				"input_name" => $variant
			]);

			// Successful upload
			if($uploads) {

				$name = $uploads[0]["name"];
				$variant = $uploads[0]["variant"];
				$format = $uploads[0]["format"];
				$width = $uploads[0]["width"];
				$height = $uploads[0]["height"];
				$filesize = $uploads[0]["filesize"];

				// Replace assets
				$query->sql("DELETE FROM ".UT_ITEMS_MEDIAE." WHERE item_id = $item_id AND variant = '$variant'");
				$query->sql("INSERT INTO ".UT_ITEMS_MEDIAE." VALUES(DEFAULT, $item_id, '$name', '$format', '$variant', ".($width ? "'$width'" : "DEFAULT").", ".($height ? "'$height'" : "DEFAULT").", '$filesize', 0)");

				// return upload data in standard mediae array
				return ["mediae" => [$variant => [
					"id" => $query->lastInsertId(), 
					"item_id" => $item_id, 
					"name" => $name,
					"variant" => $variant, 
					"format" => $format, 
					"width" => $width,
					"height" => $height,
					"filesize" => $filesize
				]]];

			}

		}

		return false;
	}


	// delete image - 3 parameters exactly
	// /janitor/[admin/]#itemtype#/deleteImage/#item_id#/#variant#
	// TODO: implement itemtype checks
	// DEBATE: PROS/CONS of itemtype checks
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

				message()->addMessage("Media deleted");
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
	function upload($item_id, $_options = false) {

		// Get posted values to make them available for models
		$this->getPostedEntities();


		// Default values
		$input_name = "mediae";               // input name to check for files (default is mediae)
		$variant = false;                     // variantname to save files under

		$auto_add_variant = false;            // automatically add variant-key for each file


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "input_name"          : $input_name          = $_value; break;
					case "variant"             : $variant             = $_value; break;

					case "auto_add_variant"    : $auto_add_variant    = $_value; break;
				}
			}
		}


		// create return array
		$uploads = array();


		// Validate input
		if($this->validateList([$input_name], $item_id)) {
			// debug(["validated", $_FILES[$input_name]]);

			$fs = new FileSystem();

			// Get upload information
			$identified_uploads = $this->identifyUploads($input_name);


			foreach($identified_uploads as $upload) {

				// define variant value
				if($auto_add_variant) {
					$variant = $input_name . "-" . randomKey(8);
					$upload["variant"] = $variant;
				}
				else if($variant) {
					$upload["variant"] = $variant;
				}
				// default variant to input_name if variant was not passed
				else {
					$variant = $input_name;
					$upload["variant"] = $variant;
				}


				// if format was identified and type indicates special support
				if($upload["format"] && preg_match("/video\/|audio\/|image\/|\/pdf|\/zip/", $upload["type"])) {

					$output_file = PRIVATE_FILE_PATH."/".$item_id."/".$variant."/".$upload["format"];

					$fs->removeDirRecursively(PRIVATE_FILE_PATH."/".$item_id."/".$variant);
					$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id."/".$variant);

					$fs->makeDirRecursively(PRIVATE_FILE_PATH."/".$item_id."/".$variant);
					copy($upload["file"], $output_file);
					unlink($upload["file"]);

					// Add to return array
					$uploads[] = $upload;

					message()->addMessage(strtoupper($upload["format"]) . " uploaded (".$upload["name"].")");

				}

				// Unknown filetype – zip it
				else {

					$output_file = PRIVATE_FILE_PATH."/".$item_id."/".$variant."/zip";

					$fs->removeDirRecursively(PRIVATE_FILE_PATH."/".$item_id."/".$variant);
					$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id."/".$variant);

					$fs->makeDirRecursively(PRIVATE_FILE_PATH."/".$item_id."/".$variant);

					$zip = new ZipArchive();
					$zip->open($output_file, ZipArchive::CREATE);
					$zip->addFile($upload["file"], $upload["name"]);
					$zip->close();

					unlink($upload["file"]);

					// Update properties, which might have changed
					$upload["format"] = "zip";
					$upload["filesize"] = filesize($output_file);
					$upload["name"] = $upload["name"].".zip";

					// Add to return array
					$uploads[] = $upload;

					message()->addMessage("File uploaded and zipped (".$upload["name"].")");

				}

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


			// Get name of related HTML input
			$input_name = getPost("input-name");


			// Upload media
			$uploads = $this->upload($item_id, [
				"input_name" => "htmleditor_media", 
				"variant" => "HTMLEDITOR-".$input_name."-".randomKey(8)
			]);

			// Successful upload
			if($uploads) {
				$sql = "DELETE FROM ".UT_ITEMS_MEDIAE." WHERE item_id = $item_id AND variant = '".$uploads[0]["variant"]."'";
				$query->sql($sql);

				$sql = "INSERT INTO ".UT_ITEMS_MEDIAE." VALUES(DEFAULT, $item_id, '".$uploads[0]["name"]."', '".$uploads[0]["format"]."', '".$uploads[0]["variant"]."', '".$uploads[0]["width"]."', '".$uploads[0]["height"]."', '".$uploads[0]["filesize"]."', 0)";
				$query->sql($sql);

				return array(
					"id" => $query->lastInsertId(), 
					"item_id" => $item_id, 
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


			// Get name of related HTML input
			$input_name = getPost("input-name");


			// Upload media
			$uploads = $this->upload($item_id, [
				"input_name" => "htmleditor_file", 
				"variant" => "HTMLEDITOR-".$input_name."-".randomKey(8)
			]);

			// Successful upload
			if($uploads) {

				$sql = "DELETE FROM ".UT_ITEMS_MEDIAE." WHERE item_id = $item_id AND variant = '".$uploads[0]["variant"]."'";
				$query->sql("DELETE FROM ".UT_ITEMS_MEDIAE." WHERE item_id = $item_id AND variant = '".$uploads[0]["variant"]."'");

				$sql = "INSERT INTO ".UT_ITEMS_MEDIAE." VALUES(DEFAULT, $item_id, '".$uploads[0]["name"]."', '".$uploads[0]["format"]."', '".$uploads[0]["variant"]."', ".($uploads[0]["width"] ? "'".$uploads[0]["width"]."'" : "NULL").", ".($uploads[0]["height"] ? "'".$uploads[0]["height"]."'" : "NULL") . ", '".$uploads[0]["filesize"]."', 0)";
				$query->sql($sql);

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

		global $page;
		
		// Get posted values to make them available for models
		$this->getPostedEntities();


		if(count($action) == 2) {

			$query = new Query();
			$item_id = $action[1];

			if($this->validateList(array("item_price", "item_price_currency", "item_price_vatrate", "item_price_type", "item_price_quantity"), $item_id)) {

				$price = $this->getProperty("item_price", "value");
				$currency = $this->getProperty("item_price_currency", "value");
				$vatrate = $this->getProperty("item_price_vatrate", "value");
				$type_id = $this->getProperty("item_price_type", "value");
				$type_name = $page->price_types(["id" => $type_id])["name"];
				if($type_name == "bulk") {
					$quantity = $this->getProperty("item_price_quantity", "value");
					// check quantity value for bulk price
					if(!is_numeric($quantity) || intval($quantity) != floatval($quantity) || intval($quantity) <= 1) {
						message()->addMessage("Invalid quantity for bulk price", array("type" => "error"));
						return false;
					}

					// bulk items price can only exist once for specific quantity
					$sql = "SELECT id FROM ".UT_ITEMS_PRICES." WHERE item_id = $item_id AND currency = '$currency' AND type_id = '$type_id' AND quantity = $quantity";
					// debug($sql);

					if($query->sql($sql)) {
						message()->addMessage("Item already has bulk price for this type, currency and quantity", array("type" => "error"));
						return false;
					}

				}
				else {
					// default and offer price can only exist once for an item
					$sql = "SELECT id FROM ".UT_ITEMS_PRICES." WHERE item_id = $item_id AND currency = '$currency' AND type_id = '$type_id'";
					// debug($sql);

					if($query->sql($sql)) {
						message()->addMessage("Item already has price for this type and currency", array("type" => "error"));
						return false;
					}

					$quantity = "DEFAULT";
				}

				// replace , with . to make valid number
				$price = preg_replace("/,/", ".", $price);

				$sql = "INSERT INTO ".UT_ITEMS_PRICES." VALUES(DEFAULT, $item_id, '$price', '$currency', $vatrate, '$type_id', $quantity)";
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
	// TODO: also update all existing subscriptions of selected item (if method changes, expiry date changes)
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
						// print $sql;
						
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

	function addedToCart($added_item, $cart) {
		
		$added_item_id = $added_item["id"];
		// print "\n<br>###$added_item_id### added to cart (generic item)\n<br>";
	}

	function ordered($order_item, $order){

		include_once("classes/shop/supersubscription.class.php");
		$SuperSubscriptionClass = new SuperSubscription();
		$IC = new Items();

		$item = $IC->getItem(["id" => $order_item["item_id"], "extend" => ["subscription_method" => true]]);
		$item_id = $order_item["item_id"];

		$custom_price = isset($order_item["custom_price"]) ? $order_item["unit_price"] : false;

		// order item can be subscribed to
		if(SITE_SUBSCRIPTIONS && isset($item["subscription_method"]) && $item["subscription_method"]) {
			
			$order_id = $order["id"];
			$user_id = $order["user_id"];
			
			$subscription = $SuperSubscriptionClass->getSubscriptions(array("user_id" => $user_id, "item_id" => $item_id));

			// user already subscribes to item
			if($subscription) {

				// update existing subscription
				// makes callback to 'subscribed' if item_id changes
				$_POST["order_id"] = $order["id"];
				$_POST["item_id"] = $item_id;
				$_POST["custom_price"] = $custom_price;
				$subscription = $SuperSubscriptionClass->updateSubscription(["updateSubscription", $subscription["id"]]);
				unset($_POST);

			}
			
			else {
				// add new subscription
				// makes callback to 'subscribed'
				$_POST["item_id"] = $item_id;
				$_POST["user_id"] = $user_id;
				$_POST["order_id"] = $order_id;
				$_POST["custom_price"] = $custom_price;
				$subscription = $SuperSubscriptionClass->addSubscription(["addSubscription"]);
				unset($_POST);

			}
		}
	}

	function subscribed($subscription) {
		
		// print "\n<br>###$subscription["item_id"]### subscribed\n<br>";

	}


}
