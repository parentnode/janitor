<?php
/**
* @package janitor.items
*/

/**
* This class holds Item functionallity.
*
*/

// define default database name constants
// base DB tables
define("UT_ITEMS",              SITE_DB.".items");                             // Items

define("UT_TAG",                SITE_DB.".tags");                              // Item tags
define("UT_TAGGINGS",           SITE_DB.".taggings");                          // Item tags relations

// SHOP EXTENSIONS
define("UT_PRICES",             SITE_DB.".prices");                            // Item prices

define("UT_LANGUAGES",          SITE_DB.".languages");                         // Languages
define("UT_COUNTRIES",          SITE_DB.".countries");                         // Countries

define("UT_CURRENCIES",         SITE_DB.".currencies");                        // Currencies
define("UT_VATRATES",           SITE_DB.".vatrates");                          // Vatrates



class ItemCore {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {


	}

	/**
	* Get matching type object instance
	*
	* @return return instance of type object
	*/
	function TypeObject($itemtype) {

		// TODO: is mixed needed anymore?
		// include generic type (for mixed itemtypes)
		if($itemtype == "mixed" || !$itemtype) {
			$itemtype = "mixed";
			$class = "TypeMixed";
		}
		else {
			$class = "Type".ucfirst($itemtype);
		}

		if(!isset($this->itemtypes["class"][$itemtype])) {
			include_once("class/items/type.$itemtype.class.php");
			$this->itemtypes["class"][$itemtype] = new $class();

		}
		return $this->itemtypes["class"][$itemtype];
	}


	/**
	* Global getItem
	*
	* @param $id Item id or sindex to get
	*/
	function getItem($id) {
//		print "get item:" . "SELECT * FROM ".UT_ITEMS." WHERE sindex = '$id' OR id = '$id'";

		$item = array();

		$query = new Query();
		if($query->sql("SELECT * FROM ".UT_ITEMS." WHERE sindex = '$id' OR id = '$id'")) {

			$item["id"] = $query->result(0, "id");

			// TODO: create sindex value if it doesn't exist (backwards compatibility)
			$item["itemtype"] = $query->result(0, "itemtype");
			$item["sindex"] = $query->result(0, "sindex");
			$item["status"] = $query->result(0, "status");

			$item["user_id"] = $query->result(0, "user_id");

			$item["created_at"] = $query->result(0, "created_at");
			$item["modified_at"] = $query->result(0, "modified_at");
			$item["published_at"] = $query->result(0, "published_at");

			return $item;
		}
		return false;
	}

	/**
	* Global getCompleteItem (both getItem and get on itemtype) + tags + prices
	*
	* @param $id Item id or sindex to get
	*/
	// TODO: add options parameter to skip prices and tags in complete item (no_tags, no_prices)
	function getCompleteItem($item_id) {
		$item = $this->getItem($item_id);
		if($item) {


			// get the specific type data
			$typeObject = $this->TypeObject($item["itemtype"]);
			if(method_exists($typeObject, "get")) {
				$item = array_merge($item, $typeObject->get($item["id"]));
			}
			else {
				$item = array_merge($item, $this->getSimpleType($item["id"], $typeObject));
			}

			// add prices and tags
			$item["prices"] = $this->getPrices($item["id"]);
			$item["tags"] = $this->getTags(array("item_id" => $item["id"]));

			return $item;
		}
		return false;
	}


	/**
	* Get simple (flat) item type
	*/
	function getSimpleType($item_id, $typeObject) {
		$query = new Query();
		if($query->sql("SELECT * FROM ".$typeObject->db." WHERE item_id = $item_id LIMIT 1")) {
			$results = $query->results();
			if($results) {
				// remove type id from index
				unset($results[0]["id"]);
				return $results[0];
			}
		}
		return false;
	}

	/**
	* Get all matching items
	*
	* @param String $options
	* $order      String  - 
	* $status     Int 
	* $tags       
	* $sindex
	* $itemtype  
	* $limit
	*
	* @param String $sindex Optional navigation index - s(earch)index
	*
	* @return Array [id][] + [itemtype][]
	*/
	function getItems($_options = false) {

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "itemtype"   : $itemtype   = $_value; break;
					case "status"     : $status     = $_value; break;
					case "tags"       : $tags       = $_value; break;
					case "sindex"     : $sindex     = $_value; break;
					case "order"      : $order      = $_value; break;
					case "limit"      : $limit      = $_value; break;
					
					// TODO: implement date ranges

					// TODO: implement search patterns which can also look in local databases - first experiment made in local device search (type.device.class.php)
				}

			}
		}


		$query = new Query();

		$SELECT = array();
		$FROM = array();
		$LEFTJOIN = array();
		$WHERE = array();
		$GROUP_BY = "";
		$HAVING = "";
		$ORDER = array();


		$SELECT[] = "items.id";
		$SELECT[] = "items.sindex";
		$SELECT[] = "items.status";
		$SELECT[] = "items.itemtype";
		$SELECT[] = "items.user_id";

		$SELECT[] = "items.created_at";
		$SELECT[] = "items.modified_at";
		$SELECT[] = "items.published_at";

	 	$FROM[] = UT_ITEMS." as items";

		if(isset($status)) {
			$WHERE[] = "items.status = $status";
		}

		// TODO: implement dateranges
		// if(isset($published_at)) {
		// 	$WHERE[] = "items.published_at = $published_at";
		// }

		if(isset($itemtype)) {
			$WHERE[] = "items.itemtype = '$itemtype'";

			// add main itemtype table to enable sorting based on local values
			$LEFTJOIN[] = $this->typeObject($itemtype)->db." as ".$itemtype." ON items.id = ".$itemtype.".item_id";
		}

		// tag query

		if(isset($tags) && is_string($tags)) {

			$LEFTJOIN[] = UT_TAGGINGS." as taggings ON taggings.item_id = items.id";
			$LEFTJOIN[] = UT_TAG." as tags ON tags.id = taggings.tag_id";


//			$FROM[] = UT_TAGGINGS . " as item_tags";
//			$FROM[] = UT_TAG . " as tags";
//			$tag_array = explode(",", $tags);
			// UPDATED: changed tags separator to ;
			$tag_array = explode(";", $tags);
			$tag_sql = "";


			foreach($tag_array as $tag) {
//				$exclude = false;
				// tag id
				if($tag) {

					// dechipher tag
					$exclude = false;

					// negative tag, exclude
					if(substr($tag, 0, 1) == "!") {
						$tag = substr($tag, 1);
						$exclude = true;
					}

					// if tag has both context and value
					if(strpos($tag, ":")) {
						list($context, $value) = explode(":", $tag);
					}
					// only context present, value false
					else {
						$context = $tag;
						$value = false;
					}

					if($context || $value) {
						// Negative !tag
						if($exclude) {
//							$WHERE[] = "items.id NOT IN (SELECT item_id FROM ".UT_TAGGINGS." as item_tags, ".UT_TAG." as tags WHERE item_tags.tag_id = tags.id" . ($context ? " AND tags.context = '$context'" : "") . ($value ? " AND tags.value = '$value'" : "") . ")";
//							$WHERE[] = "items.id NOT IN (SELECT item_id FROM ".UT_TAGGINGS." as item_tags, ".UT_TAG." as tags WHERE item_tags.tag_id = tags.id" . ($context ? " AND tags.context = '$context'" : "") . ($value ? " AND tags.value = '$value'" : "") . ")";
						}
						// positive tag
						else {
							if($context && $value) {
								$tag_sql .= ($tag_sql ? " OR " : "") .  "tags.context = '$context' AND tags.value = '$value'";
							}
							else if($context) {
								$tag_sql .= ($tag_sql ? " OR " : "") .  "tags.context = '$context'";
							}
//							$WHERE[] = "items.id IN (SELECT item_id FROM ".UT_TAGGINGS." as item_tags, ".UT_TAG." as tags WHERE item_tags.tag_id = tags.id" . ($context ? " AND tags.context = '$context'" : "") . ($value ? " AND tags.value = '$value'" : "") . ")";
	//						$WHERE[] = "items.id IN (SELECT item_id FROM ".UT_TAGGINGS." as item_tags, ".UT_TAG." as tags WHERE item_tags.tag_id = '$tag' OR (item_tags.tag_id = tags.id AND tags.name = '$tag'))";
						}
					}
				}
			}
			$WHERE[] = "(".$tag_sql.")";
			$HAVING = "count(*) = ".count($tag_array);
		}


		$GROUP_BY = "items.id";


		// add item-order specific SQL
		if(isset($order)) {
			$ORDER[] = $order;
		}

		$ORDER[] = "items.published_at DESC";

		if(isset($limit)) {
			$limit = " LIMIT $limit";
		}
		else {
			$limit = "";
		}

		$items = array();

//		print $query->compileQuery($SELECT, $FROM, array("LEFTJOIN" => $LEFTJOIN, "WHERE" => $WHERE, "HAVING" => $HAVING, "GROUP_BY" => $GROUP_BY, "ORDER" => $ORDER)) . $limit;
//		return array();
		$query->sql($query->compileQuery($SELECT, $FROM, array("LEFTJOIN" => $LEFTJOIN, "WHERE" => $WHERE, "HAVING" => $HAVING, "GROUP_BY" => $GROUP_BY, "ORDER" => $ORDER)) . $limit);
		for($i = 0; $i < $query->count(); $i++){

			$item = array();

			$item["id"] = $query->result($i, "items.id");
			$item["itemtype"] = $query->result($i, "items.itemtype");

//			$item_sindex = $query->result($i, "items.sindex");
			$item["sindex"] = $query->result($i, "items.sindex"); //$item_sindex ? $item_sindex : $this->sindex($item["id"]);

			$item["status"] = $query->result($i, "items.status");

			$item["user_id"] = $query->result($i, "items.user_id");

			$item["created_at"] = $query->result($i, "items.created_at");
			$item["modified_at"] = $query->result($i, "items.modified_at");
			$item["published_at"] = $query->result($i, "items.published_at");

			$items[] = $item;
		}

		return $items;
	}



	/**
	* set sIndex value for item
	*
	* @param string $item_id Item id
	* @param string $sindex
	* @return String final/valid sindex
	*/
	function sindex($item_id, $sindex = false) {
		$query = new Query();

		if($sindex) {
			$sindex = superNormalize(substr($sindex, 0, 40));

			// check for existance
			if(!$query->sql("SELECT sindex FROM ".UT_ITEMS." WHERE sindex = '$sindex' AND id != $item_id")) {
				$query->sql("UPDATE ".UT_ITEMS." SET sindex = '$sindex' WHERE id = $item_id");
			}
			// try with timestamped variation
			else {
				$query->sql("SELECT published_at FROM ".UT_ITEMS." WHERE id = $item_id");
				$sindex = $this->sindex($item_id, $query->result(0, "published_at")."_".$sindex);
			}
		}
		else {
			$query->sql("SELECT itemtype FROM ".UT_ITEMS." WHERE id = $item_id");
			$itemtype = $query->result(0, "itemtype");

			$typeObject = $this->TypeObject($itemtype);

			if(method_exists($typeObject, "sindexBase")) {
				$sindex = $typeObject->sindexBase($item_id);
			}
			else if($query->sql("SELECT name FROM ".$typeObject->db." WHERE item_id = " . $item_id)) {
				$sindex = $query->result(0, "name");
			}
			
			$sindex = $this->sindex($item_id, $sindex);
		}
		return $sindex;
	}


	// checks posted values and saves item if all informations is available
	function saveItem() {

		// TODO: user_id
		// TODO: access validation
		// TODO: format of published_at

		$itemtype = RESTParams(1);
		$typeObject = $this->TypeObject($itemtype);

		if($typeObject) {
			$query = new Query();

			// standard Item values
			// - published at
			// - status
			$published_at = getPost("published_at") ? toTimestamp(getPost("published_at")) : false;
			$status = is_numeric(getPost("status")) ? getPost("status") : 0;

//			print "INSERT INTO ".UT_ITEMS." VALUES(DEFAULT, DEFAULT, $status, '$itemtype', DEFAULT, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ".($published_at ? "'$published_at'" : "CURRENT_TIMESTAMP").")";
			// create item
			$query->sql("INSERT INTO ".UT_ITEMS." VALUES(DEFAULT, DEFAULT, $status, '$itemtype', DEFAULT, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ".($published_at ? "'$published_at'" : "CURRENT_TIMESTAMP").")");
			$new_id = $query->lastInsertId();

			// attempt typeObject save
			if($new_id && 
				(
					(method_exists($typeObject, "save") && $typeObject->save($new_id)) ||
					$this->saveSimpleType($new_id, $typeObject)
				)
			) {

				// add tags
				$tags = getPost("tags");
				if($tags) {
					foreach($tags as $tag) {
						if($tag) {
							$this->addTag($new_id, $tag);
						}
					}
				}

				// create sindex
				$this->sindex($new_id);

				message()->addMessage("Item saved");

				// return new item
				return $this->getCompleteItem($new_id);
			}
			else if($new_id) {
				// save failed, remove item again
				$query->sql("DELETE FROM ".UT_ITEMS." WHERE id = $new_id");
			}

		}
		message()->addMessage("Item could not be saved", array("type" => "error"));
		return false;
	}

	/**
	* Save simple (flat) item type
	*/
	function saveSimpleType($item_id, $typeObject) {

		// does values validate
		if($typeObject->validateAll()) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($typeObject->db);

			$entities = $typeObject->data_entities;
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false && $name != "published_at" && $name != "status" && $name != "tags" && $name != "prices") {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($values) {
				$sql = "INSERT INTO ".$typeObject->db." SET id = DEFAULT,item_id = $item_id," . implode(",", $values);
//				print $sql;

				if($query->sql($sql)) {
					return true;
				}
			}
		}

		return false;
	}



	// update item - does not update tags which is a separate process entirely
	function updateItem($item_id) {
//		print "update item<br>";

		// TODO: user_id
		// TODO: access validation
		// TODO: format of published_at

		$item = $this->getItem($item_id);
		$typeObject = $this->TypeObject($item["itemtype"]);

		if($typeObject) {
			$query = new Query();

			// is published_at posted?
			$published_at = getPost("published_at") ? toTimestamp(getPost("published_at")) : false;

//			print "published_at:" . $published_at ."<br>";

//			print "UPDATE ".UT_ITEMS." SET modified_at=CURRENT_TIMESTAMP ".($published_at ? "published_at=$published_at" : "")." WHERE id = $id<br>";
			// create item
			$query->sql("UPDATE ".UT_ITEMS." SET modified_at=CURRENT_TIMESTAMP ".($published_at ? ",published_at='$published_at'" : "")." WHERE id = $item_id");

			// add tags
			$tags = getPost("tags");
			if($tags) {
				if(is_array($tags)) {
					foreach($tags as $tag) {
						if($tag) {
							$this->addTag($item_id, $tag);
						}
					}
				}
				else {
					$this->addTag($item_id, $tags);
				}
			}

			// add prices
			$prices = getPost("prices");
			$currency = getPost("currency");
			$vatrate = getPost("vatrate");
			if($prices) {
				if(is_array($prices)) {
					foreach($prices as $price) {
						if($price) {
							$this->addPrice($item_id, $price, $currency, $vatrate);
						}
					}
				}
				else {
					$this->addPrice($item_id, $prices, $currency, $vatrate);
				}
			}

			if(
				(method_exists($typeObject, "update") && $typeObject->update($item_id)) ||
				$this->updateSimpleType($item_id, $typeObject)
			) {
				// update sindex
				$this->sindex($item_id);

				message()->addMessage("Item updated");

				return $this->getCompleteItem($item_id);
//				return true;
			}

		}
		message()->addMessage("Item could not be updated", array("type" => "error"));
		return false;
	}

	/**
	* Update simple (flat) item type
	* TODO: extend with one file handling - still missing parts
	*/
	function updateSimpleType($item_id, $typeObject) {
		$query = new Query();

		// make sure type tables exist
		$query->checkDbExistance($typeObject->db);

		$entities = $typeObject->data_entities;
		$names = array();
		$values = array();

		foreach($entities as $name => $entity) {

			// type files (simple type can handle one file)
			// TODO: file input can only be named "files" due to JS and upload names - needs to be more flexible
			if($entity["type"] == "files") {
				$uploads = $this->upload($item_id, $entity);
				if($uploads) {
					$names[] = $name;
					$values[] = $name."='".$uploads[0]["format"]."'";
				}
			}
			else if($entity["value"] && $name != "published_at" && $name != "status" && $name != "tags" && $name != "prices") {

				$names[] = $name;
				$values[] = $name."='".$entity["value"]."'";
			}
		}



		if($typeObject->validateList($names, $item_id)) {
			if($values) {
				$sql = "UPDATE ".$typeObject->db." SET ".implode(",", $values)." WHERE item_id = ".$item_id;
//				print $sql;
			}

			if(!$values || $query->sql($sql)) {
				return true;
			}
		}

		return false;
	}




	// does this still have a purpose
	function identifyUploads() {

		$uploads = array();

		if(isset($_FILES["files"])) {
//			print_r($_FILES["files"]);

			foreach($_FILES["files"]["name"] as $index => $value) {
				if(!$_FILES["files"]["error"][$index]) {

					$temp_file = $_FILES["files"]["tmp_name"][$index];
					$temp_type = $_FILES["files"]["type"][$index];

					$upload = array();

					if(preg_match("/video/", $temp_type)) {

						include_once("class/system/video.class.php");
						$Video = new Video();
						$info = $Video->info($temp_file);
						// check if we can get relevant info about movie
						if($info) {
							// TODO: add format detection to Video Class
							// TODO: add bitrate detection to Video Class

							$upload["type"] = "movie";
							$upload["format"] = "mov";
							$upload["width"] = $info["width"];
							$upload["height"] = $info["height"];
							$uploads[] = $upload;
						}
					}
					// audio upload
					else if(preg_match("/audio/", $temp_type)) {

						include_once("class/system/audio.class.php");
						$Audio = new Audio();
						// check if we can get relevant info about audio
						$info = $Audio->info($temp_file);
						if($info) {
							// TODO: add format detection to Audio Class
							// TODO: add bitrate detection to Audio Class

							$upload["type"] = "audio";
							$upload["format"] = "mp3";
							$uploads[] = $upload;
						}

					}
					// image upload
					else if(preg_match("/image/", $temp_type)) {

						$info = getimagesize($temp_file);
						// is image valid format
						if(isset($info["mime"])) {
							$extension = mimetypeToExtension($info["mime"]);

							$upload["type"] = "image";
							$upload["format"] = $extension;
							$upload["width"] = $info[0];
							$upload["height"] = $info[1];
							$uploads[] = $upload;
						}
					}
				}
			}
		}

		return $uploads;
	}

	// upload to item_id/variant
	// checks content of $_FILES, looks for uploaded file where type matches $type and uploads
	// supports video, audio, image
	function upload($item_id, $_options) {

		$input_name = "files";                // input name to check for files (default is files)

		$variant = false;                     // variantname to save files under
		$proportion = false;                  // specific proportion for images and videos
		$width = false;                       // specific file width for images and videos
		$height = false;                      // specific file height for images and videos

		$min_height = false;                  // specific file min-height for images and videos
		$max_height = false;                  // specific file max-height for images and videos
		$min_width = false;                   // specific file min-width for images and videos
		$max_width = false;                   // specific file max-width for images and videos

		$min_bitrate = false;                 // specific file max-height for images and videos

		$filetypes = false;                   // jpg,png,git,mov,mp4,pdf,etc
		$filegroup = false;                   // image,video

		$auto_add_variant = false;            // automatically add variant-key for each file - true for unlimited images, TODO: or state max number of files


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "input_name"          : $input_name           = $_value; break;

					case "variant"             : $variant              = $_value; break;
					case "proportion"          : $proportion           = $_value; break;
					case "width"               : $width                = $_value; break;
					case "height"              : $height               = $_value; break;
					case "min_width"           : $min_width            = $_value; break;
					case "min_height"          : $min_height           = $_value; break;
					case "max_width"           : $max_width            = $_value; break;
					case "max_height"          : $max_height           = $_value; break;

					case "filetypes"           : $filetypes            = $_value; break;
					case "filegroup"           : $filegroup            = $_value; break;

					case "auto_add_variant"    : $auto_add_variant     = $_value; break;
				}
			}
		}

		$uploads = array();

		// print "files:<br>";
		// print_r($_FILES);
		// print "post:<br>";
		// print_r($_POST);


		if(isset($_FILES[$input_name])) {
//			print_r($_FILES["files"]);

			foreach($_FILES[$input_name]["name"] as $index => $value) {
				if(!$_FILES[$input_name]["error"][$index] && file_exists($_FILES[$input_name]["tmp_name"][$index])) {

					$upload = array();
					$upload["name"] = $value;

					$extension = false;
					$temp_file = $_FILES[$input_name]["tmp_name"][$index];
					$temp_type = $_FILES[$input_name]["type"][$index];

//					print preg_match("/".$filegroup."/", $temp_type)."#";

					if(preg_match("/".$filegroup."/", $temp_type)) {


						if($auto_add_variant) {
							$upload["variant"] = randomKey(8);
							$variant = "/".$upload["variant"];
						}
						else if($variant) {
							$upload["variant"] = $variant;
							$variant = "/".$upload["variant"];
						}
						else {
							$variant = "";
							$upload["variant"] = $variant;
						}

//						print "correct group:" . $filegroup . ", " . $temp_type . ", " . $variant;

						// video upload
						if(preg_match("/video/", $temp_type)) {

							include_once("class/system/video.class.php");
							$Video = new Video();

							$info = $Video->info($temp_file);
							// check if we can get relevant info about movie
							if($info) {
								// TODO: add extension to Video Class
								// TODO: add better bitrate detection to Video Class
//								$extension = $info["extension"];
//								$bitrate = $info["bitrate"];

								$upload["format"] = "mov";
								$upload["width"] = $info["width"];
								$upload["height"] = $info["height"];

								if(
									isset($upload["format"]) &&
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
									FileSystem::removeDirRecursively(dirname($output_file));
									FileSystem::removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id.$variant);
									FileSystem::makeDirRecursively(dirname($output_file));

									copy($temp_file, $output_file);
									$upload["file"] = $output_file;
									$uploads[] = $upload;
									unlink($temp_file);
								}
							}

						}
						// audio upload
						else if(preg_match("/audio/", $temp_type)) {

							include_once("class/system/audio.class.php");
							$Audio = new Audio();

 							$info = $Audio->info($temp_file);
//							print_r($info);
// 							// check if we can get relevant info about movie
 							if($info) {
 								$output_file = PRIVATE_FILE_PATH."/".$item_id.$variant."/mp3";
// 
// 								print $output_file . "<br>";
 								FileSystem::removeDirRecursively(dirname($output_file));
 								FileSystem::removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id.$variant);
 								FileSystem::makeDirRecursively(dirname($output_file));

								copy($temp_file, $output_file);
								$upload["file"] = $output_file;
								$upload["format"] = "mp3";
								$uploads[] = $upload;
								unlink($temp_file);
							}

						}
						// image upload
						else if(preg_match("/image/", $temp_type)) {

//							print "uploaded image<br>";

							$gd = getimagesize($temp_file);
							// is image valid format
							if(isset($gd["mime"])) {

//								print $gd["mime"].", ". mimetypeToExtension($gd["mime"]);
								$upload["format"] = mimetypeToExtension($gd["mime"]);
								$upload["width"] = $gd[0];
								$upload["height"] = $gd[1];

//								print round($proportion, 2) . "==" . round($upload["width"] / $upload["height"], 2);
								if(
									isset($upload["format"]) &&
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
									FileSystem::removeDirRecursively(dirname($output_file));
									FileSystem::removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id.$variant);
									FileSystem::makeDirRecursively(dirname($output_file));

									copy($temp_file, $output_file);
									$upload["file"] = $output_file;
									$uploads[] = $upload;
									unlink($temp_file);
								}
							}
						}
					}

				}
				else {
					// error
				}
			}

		}
		return $uploads;
	}


	function disableItem($item_id) {
		$query = new Query();

		// delete item + itemtype + files
		if($query->sql("SELECT id FROM ".UT_ITEMS." WHERE id = $item_id")) {
			$query->sql("UPDATE ".UT_ITEMS." SET status = 0 WHERE id = $item_id");

			message()->addMessage("Item disabled");
			return true;
		}
		message()->addMessage("Item could not be disabled", array("type" => "error"));
		return false;

	}
	function enableItem($item_id) {
		$query = new Query();

		// delete item + itemtype + files
		if($query->sql("SELECT id FROM ".UT_ITEMS." WHERE id = $item_id")) {
			$query->sql("UPDATE ".UT_ITEMS." SET status = 1 WHERE id = $item_id");

			message()->addMessage("Item enabled");
			return true;
		}
		message()->addMessage("Item could not be enabled", array("type" => "error"));
		return false;
	}

	function deleteItem($item_id) {
		$query = new Query();

		// delete item + itemtype + files
		if($query->sql("SELECT id FROM ".UT_ITEMS." WHERE id = $item_id")) {
			
			$query->sql("DELETE FROM ".UT_ITEMS." WHERE id = $item_id");
			FileSystem::removeDirRecursively(PUBLIC_FILE_PATH."/$item_id");
			FileSystem::removeDirRecursively(PRIVATE_FILE_PATH."/$item_id");

			message()->addMessage("Item deleted");
			return true;
		}

		message()->addMessage("Item could not be deleted", array("type" => "error"));
		return false;
	}



	// get tag, optionally based on item_id, limited to context, or just check if specific tag exists
	function getTags($_options=false) {

		$item_id = false;
		$tag_id = false;
		$tag_context = false;
		$tag_value = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "item_id"    : $item_id        = $_value; break;
					case "tag_id"     : $tag_id         = $_value; break;
					case "context"    : $tag_context    = $_value; break;
					case "value"      : $tag_value      = $_value; break;
				}
			}
		}

		$query = new Query();

		if($item_id) {
			// specific tag exists?
			if($tag_context && $tag_value) {
				return $query->sql("SELECT * FROM ".UT_TAG." as tags, ".UT_TAGGINGS." as taggings WHERE tags.context = '$tag_context' AND tags.value = '$tag_value' AND tags.id = taggings.tag_id AND taggings.item_id = $item_id");
			}
			// get all tags with context
			else if($tag_context) {
				if($query->sql("SELECT tags.id as id, tags.context as context, tags.value as value FROM ".UT_TAG." as tags, ".UT_TAGGINGS." as taggings WHERE tags.context = '$tag_context' AND tags.id = taggings.tag_id AND taggings.item_id = $item_id")) {
					return $query->results();
				}
			}
			// all tags
			else {
				if($query->sql("SELECT tags.id as id, tags.context as context, tags.value as value FROM ".UT_TAG." as tags, ".UT_TAGGINGS." as taggings WHERE tags.id = taggings.tag_id AND taggings.item_id = $item_id")) {
					return $query->results();
				}
			}
		}
		// get tag and items using tag_id
		else if($tag_id) {
			$query->sql("SELECT * FROM ".UT_TAG." as tags WHERE tags.id = '$tag_id'");
			$tag = $query->result(0);
			
			$query->sql("SELECT * FROM ".UT_TAGGINGS." as taggings, ".UT_ITEMS." as items WHERE taggings.tag_id = '$tag_id' AND taggings.item_id = items.id");
			$tag["items"] = $query->results();
//			print "SELECT * FROM ".UT_TAG." as tags, ".UT_TAGGINGS." as taggings WHERE tags.id = '$tag_id' AND tags.id = taggings.tag_id";
			return $tag;
		}
		// get items using tag with context and value
		else if($tag_context && $tag_value) {
			$query->sql("SELECT * FROM ".UT_TAG." as tags, ".UT_TAGGINGS." as taggings WHERE tags.context = '$tag_context' AND tags.value = '$tag_value' AND tags.id = taggings.tag_id");
			return $query->results();
		}
		// get all tags
		else {
			// get all tags with context
			if($tag_context) {
				if($query->sql("SELECT tags.id as id, tags.context as context, tags.value as value FROM ".UT_TAG." as tags WHERE tags.context = '$tag_context'")) {
					return $query->results();
				}
			}
			// all tags
			else {
				if($query->sql("SELECT tags.id as id, tags.context as context, tags.value as value FROM ".UT_TAG." ORDER BY tags.context, tags.value")) {
					return $query->results();
				}
			}
			
		}
		return false;
	}

	// add tag to item, create tag if it does not exist
	// tag can be tag-string or tag_id
 	function addTag($item_id, $tag) {

		$query = new Query();

		if(preg_match("/([a-zA-Z0-9_]+):([^\b]+)/", $tag, $matches)) {
			$context = $matches[1];
			$value = $matches[2];

//			print "SELECT id FROM ".UT_TAG." WHERE context = '$context' AND value = '$value'<br>";
			if($query->sql("SELECT id FROM ".UT_TAG." WHERE context = '$context' AND value = '$value'")) {
				$tag_id = $query->result(0, "id");
			}
//			print "INSERT INTO ".UT_TAG." VALUES(DEFAULT, '$context', '$value', DEFAULT)<br>";
			else if($query->sql("INSERT INTO ".UT_TAG." VALUES(DEFAULT, '$context', '$value', DEFAULT)")) {
				$tag_id = $query->lastInsertId();
			}

		}
		else if(is_numeric($tag)) {
			// is it a valid tag_id
			if($query->sql("SELECT id FROM ".UT_TAG." WHERE id = $tag")) {
				$tag_id = $tag;
			}
		}

		if(isset($tag_id)) {
			$query->sql("INSERT INTO ".UT_TAGGINGS." VALUES(DEFAULT, $item_id, $tag_id)");


			// TODO: update device modified timestamp


			message()->addMessage("Tag added");
			return true;
		}


		message()->addMessage("Tag could not be added", array("type" => "error"));
		return false;
	}

	// delete tag - tag can be complete context:value or tag_id (number)
	// TODO: or just context to delete all context tags for item
 	function deleteTag($item_id, $tag) {
//		print "Delete tag:" . $item_id . ":" . $tag . ":" . is_numeric($tag) . "<br>";

		$query = new Query();

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
			$query->sql("DELETE FROM ".UT_TAGGINGS." WHERE item_id = $item_id AND tag_id = $tag_id");


			// TODO: update device modified timestamp


			message()->addMessage("Tag deleted");
			return true;
		}

		message()->addMessage("Tag could not be deleted", array("type" => "error"));
		return false;
	}


	// delete tag globally 
 	function globalDeleteTag($tag_id) {
		$query = new Query();

		if($query->sql("DELETE FROM ".UT_TAG." WHERE id = $tag_id")) {
			message()->addMessage("Tag deleted");
			return true;
		}

		message()->addMessage("Tag could not be deleted", array("type" => "error"));
		return false;
 	}

	// update tag globally
 	function globalUpdateTag($tag_id) {
		$query = new Query();
		
		$context = getPost("context");
		$value = getPost("value");
		$description = getPost("description");

		if($query->sql("SELECT id FROM ".UT_TAG." WHERE id = $tag_id")) {
			$query->sql("UPDATE ".UT_TAG." SET context = '$context', value = '$value', description = '$description' WHERE id = $tag_id");

			message()->addMessage("Tag updated");
			return true;
		}
		message()->addMessage("Tag could not be updated", array("type" => "error"));
		return false;
 	}


	// get prices, 
	// TODO: add correct comma/point formatting
	// TODO: update to getTags format as part of price function fix
	function getPrices($item_id, $options = false) {

		$prices = false;

		$currency = false;
		$country = false;

		if($options !== false) {
			foreach($options as $option => $value) {
				switch($option) {
					case "currency" : $currency = $value; break;
					case "country"  : $country  = $value; break;
				}
			}
		}

		$query = new Query();

		if($country && !$currency) {
			if($query->sql("SELECT currency FROM ".UT_COUNTRIES." WHERE id = '$country' LIMIT 1")) {
				$currency = $query->result(0, "currency");
			}
		}

		if($currency) {
			if($query->sql("SELECT * FROM ".UT_PRICES.", ".UT_CURRENCIES.", ".UT_VATRATES." WHERE vatrate_id = ".UT_VATRATES.".id AND currency = '$currency' AND item_id = $item_id")) {
				$prices = $query->results();
			}
		}
		else {
			if($query->sql("SELECT * FROM ".UT_PRICES.", ".UT_CURRENCIES.", ".UT_VATRATES." WHERE vatrate_id = ".UT_VATRATES.".id AND item_id = $item_id")) {
				$prices = $query->results();
			}
		}

		if($prices) {
			foreach($prices as $index => $price) {
				$prices[$index]["price_with_vat"] = $price["price"]* (1 + ($price["vatrate"]/100));
				// $prices[$index]["formatted"] = formatPrice($price["price"], $price["currency"]);
				// $prices[$index]["formatted_with_vat"] = formatPrice($prices[$index]["price_with_vat"], $price["currency"]); 
			}
		}

		return $prices;
	}

	// add price to item
 	function addPrice($item_id, $price, $currency, $vatrate) {

		$query = new Query();
//		print "INSERT INTO ".UT_PRICES." VALUES(DEFAULT, $item_id, $price, '$currency', '$vatrate')";

		// check if price in currency exists - if it does update price
		if($query->sql("SELECT id FROM ".UT_PRICES." WHERE currency = '$currency' AND item_id = $item_id")) {
			$price_id = $query->result(0, "id");
			if($query->sql("UPDATE ".UT_PRICES." SET price = $price, vatrate_id = $vatrate WHERE id = $price_id")) {
				message()->addMessage("Price updated");
				return true;
			}
		}
		// insert price
		else {
			if($query->sql("INSERT INTO ".UT_PRICES." VALUES(DEFAULT, $item_id, $price, '$currency', '$vatrate')")) {
				message()->addMessage("Price added");
				return true;
			}
		}

		message()->addMessage("Price could not be added", array("type" => "error"));
		return false;
	}

	// delete price
 	function deletePrice($item_id, $price_id) {

		$query = new Query();

		if($query->sql("DELETE FROM ".UT_PRICES." WHERE item_id = $item_id AND id = $price_id")) {
			message()->addMessage("Price deleted");
			return true;
		}

		message()->addMessage("Price could not be deleted", array("type" => "error"));
		return false;
	}

}

?>