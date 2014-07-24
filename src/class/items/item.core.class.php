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
	* Get item data from items db - does not did any deeper into type object
	*
	* @param $_options Named Array containing id or sindex to get
	*/
	function getItem($_options = false) {

		$id = false;
		$sindex = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "id"        : $id             = $_value; break;
					case "sindex"    : $sindex         = $_value; break;
				}
			}
		}


		$query = new Query();
		$sql = false;
		if($id) {
			$sql = "SELECT * FROM ".UT_ITEMS." WHERE id = '$id'";
		}
		else if($sindex) {
			$sql = "SELECT * FROM ".UT_ITEMS." WHERE sindex = '$sindex'";
		}
//		print $sql."<br>";

		if($sql && $query->sql($sql)) {
			$item = $query->result(0);
			return $item;
		}

		return false;
	}

	/**
	* Get ID of item based on sindex
	*/
	function getIdFromSindex($sindex) {

		$query = new Query();
		$sql = "SELECT id FROM ".UT_ITEMS." WHERE sindex = '$sindex'";
		if($query->sql($sql)) {
			return $query->result(0, "id"); 
		}

		return false;
	}

	/**
	* Global getCompleteItem (both getItem and get on itemtype)
	* + tags
	* + prices
	* + ratings
	* + comments
	*
	* @param $_options Named Array containing id or sindex to get
	*/
	function getCompleteItem($_options = false) {

		$item = $this->getItem($_options);
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
			$item["prices"] = $this->getPrices(array("item_id" => $item["id"]));
			$item["tags"] = $this->getTags(array("item_id" => $item["id"]));

			// TODO: add comments and ratings
			// $item["ratings"] = $this->getRatings(array("item_id" => $item["id"]));
			// $item["comments"] = $this->getComments(array("item_id" => $item["id"]));

			return $item;
		}
		return false;
	}


	/**
	* Extend item (already having base information)
	* Defined to be able to limit queries when getting information
	*
	* Default only gets type data
	*
	* Optional data
	*/
	function extendItem($item, $_options = false) {
		if(isset($item["id"]) && isset($item["itemtype"])) {

			$tags = false;
			$prices = false;
			$ratings = false;
			$comments = false;

			// global setting for getting everything
			$everything = false;

			if($_options !== false) {
				foreach($_options as $_option => $_value) {
					switch($_option) {
						case "tags"         : $tags           = $_value; break;
						case "prices"       : $prices         = $_value; break;
						case "ratings"      : $ratings        = $_value; break;
						case "comments"     : $comments       = $_value; break;

						case "everything"   : $everything     = $_value; break;
					}
				}
			}


			// get the specific type data
			$typeObject = $this->TypeObject($item["itemtype"]);
			if(method_exists($typeObject, "get")) {
				$item = array_merge($item, $typeObject->get($item["id"]));
			}
			else {
				$item = array_merge($item, $this->getSimpleType($item["id"], $typeObject));
			}

			// add prices
			if($everything || $prices) {
				$item["prices"] = $this->getPrices(array("item_id" => $item["id"]));
			}

			// add tags
			if($everything || $tags) {
				$item["tags"] = $this->getTags(array("item_id" => $item["id"]));
			}

			// TODO: Implement ratings and comments
			// NOT IMPLEMENTED YET
			// if($everything || $ratings) {
			//	$item["ratings"] = $this->getRatings(array("item_id" => $item["id"]));
			// }
			// if($everything || $comments) {
			//	$item["comments"] = $this->getComments(array("item_id" => $item["id"]));
			// }

			return $item;
		}
		return false;
	}


	/**
	* Get simple (flat) item type
	* Defined to handle basic type data - a replacement for having a Get in all type objects
	*
	* To overwrite this, add a get function to your type object
	*/
	function getSimpleType($item_id, $typeObject) {
		$query = new Query();
		$sql = "SELECT * FROM ".$typeObject->db." WHERE item_id = $item_id LIMIT 1";
//		print $sql."<br>";
		if($query->sql($sql)) {
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
	* $user_id
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

					case "user_id"    : $user_id    = $_value; break;
					
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

		if(isset($user_id)) {
			$WHERE[] = "items.user_id = $user_id";
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

		$sql = $query->compileQuery($SELECT, $FROM, array("LEFTJOIN" => $LEFTJOIN, "WHERE" => $WHERE, "HAVING" => $HAVING, "GROUP_BY" => $GROUP_BY, "ORDER" => $ORDER)) . $limit;
//		print $sql;

		$query->sql($sql);
		$items = $query->results();
// 		for($i = 0; $i < $query->count(); $i++){
//
// 			$item = array();
//
// 			$item["id"] = $query->result($i, "items.id");
// 			$item["itemtype"] = $query->result($i, "items.itemtype");
//
// //			$item_sindex = $query->result($i, "items.sindex");
// 			$item["sindex"] = $query->result($i, "items.sindex"); //$item_sindex ? $item_sindex : $this->sindex($item["id"]);
//
// 			$item["status"] = $query->result($i, "items.status");
//
// 			$item["user_id"] = $query->result($i, "items.user_id");
//
// 			$item["created_at"] = $query->result($i, "items.created_at");
// 			$item["modified_at"] = $query->result($i, "items.modified_at");
// 			$item["published_at"] = $query->result($i, "items.published_at");
//
// 			$items[] = $item;
// 		}

		return $items;
	}


	/**
	* Get next item(s)
	*
	* Can receive items array to use for finding next item(s) 
	* or receive query syntax to perform getItems request on it own
	* TODO: This implementation is far from performance optimized, but works - consider alternate implementations
	*/
	function getNext($item_id, $_options=false) {

		$items = false;
		$count = 1;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "items"   : $items    = $_value; break;
					case "count"   : $count    = $_value; break;
				}
			}
		}

		if(!$items) {
			$items = $this->getItems($_options);
		}

		$next_items = array();
		$item_found = false;
		$counted = 0;
		for($i = 0; $i < count($items); $i++) {

			if($item_found) {
				$counted++;

				$next_items[] = $items[$i];

				if($counted == $count) {
					break;
				}
			}
			else if($item_id == $items[$i]["id"]) {
				$item_found = true;
			}
		}


		return $next_items;
	}

	/**
	* Get previous item(s)
	*
	* Can receive items array to use for finding previous item(s) 
	* or receive query syntax to perform getItems request on it own
	* TODO: This implementation is far from performance optimized, but works - consider alternate implementations
	*/
	function getPrev($item_id, $_options=false) {

		$items = false;
		$count = 1;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "items"   : $items    = $_value; break;
					case "count"   : $count    = $_value; break;
				}
			}
		}

		if(!$items) {
			$items = $this->getItems($_options);
		}

		$prev_items = array();
		$item_found = false;
		$counted = 0;
		for($i = count($items)-1; $i >= 0; $i--) {

			if($item_found) {
				$counted++;

				array_unshift($prev_items, $items[$i]);

				if($counted == $count) {
					break;
				}
			}
			else if($item_id == $items[$i]["id"]) {
				$item_found = true;
			}
		}


		return $prev_items;
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

				// timestamp already added
				if(strstr($sindex, date("Y-d-m", strToTime($query->result(0, "published_at"))))) {

					// does sindex have counter
					preg_match("/_([\d]+)$/", $sindex, $matches);
					if($matches && is_numeric($matches[1])) {
						$counter = ($matches[1] + 1);
						$sindex = preg_replace("/_([\d]+)$/", "", $sindex);
					}
					else {
						$counter = 1;
						$sindex = preg_replace("/_$/", "", $sindex);
					}
					$sindex = $sindex . "_" . $counter;
				}
				// add timestamp
				else {
					$sindex = $this->sindex($item_id, date("Y-d-m", strToTime($query->result(0, "published_at")))."_".$sindex);
				}

				$sindex = $this->sindex($item_id, $sindex);
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
			// - user_id
			$published_at = getPost("published_at") ? toTimestamp(getPost("published_at")) : false;
			$status = is_numeric(getPost("status")) ? getPost("status") : 0;
			$user_id = stringOr(session()->value("user_id"), "DEFAULT");

			// create item
			$sql = "INSERT INTO ".UT_ITEMS." VALUES(DEFAULT, DEFAULT, $status, '$itemtype', ".$user_id.", CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ".($published_at ? "'$published_at'" : "CURRENT_TIMESTAMP").")";

//			print $sql;

			$query->sql($sql);
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
				return $this->getCompleteItem(array("id" => $new_id));
			}

			// save failed, remove item again
			if($new_id) {
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



	// update item
	function updateItem($item_id) {
//		print "update item<br>";

		// TODO: user_id
		// TODO: access validation
		// TODO: format of published_at

		$item = $this->getItem(array("id" => $item_id));
		$typeObject = $this->TypeObject($item["itemtype"]);

		if($typeObject) {
			$query = new Query();

			// is published_at posted?
			$published_at = getPost("published_at") ? toTimestamp(getPost("published_at")) : false;

			// create item
			$sql = "UPDATE ".UT_ITEMS." SET modified_at=CURRENT_TIMESTAMP ".($published_at ? ",published_at='$published_at'" : "")." WHERE id = $item_id";
//			print $sql;
			$query->sql($sql);

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
			$price = stringOr($prices["price"]);
			$currency = stringOr($prices["currency"]);

//			$vatrate = getPost("vatrate");
			if($price && $currency) {
				// if(is_array($prices)) {
				// 	foreach($prices as $price) {
				// 		if($price) {
				// 			$this->addPrice($item_id, $price, $currency);
				// 		}
				// 	}
				// }
				// else {
				$this->addPrice($item_id, $price, $currency);
//				}
			}

			if(
				(method_exists($typeObject, "update") && $typeObject->update($item_id)) ||
				$this->updateSimpleType($item_id, $typeObject)
			) {
				// update sindex
				$this->sindex($item_id);

				message()->addMessage("Item updated");

				return $this->getCompleteItem(array("id" => $item_id));
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
			else if($entity["value"] !== false && $name != "published_at" && $name != "status" && $name != "tags" && $name != "prices") {

				$names[] = $name;
				$values[] = $name."='".$entity["value"]."'";
			}
		}

//		print_r($values);
//		print_r($names);

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

	// TODO: implement format restriction validation
	function upload($item_id, $_options) {


		$fs = new FileSystem();


// TODO: TEST WITH VARIABLE FILES NAMES

		$_input_name = "files";                // input name to check for files (default is files)

		$_variant = false;                     // variantname to save files under
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
					case "input_name"          : $_input_name           = $_value; break;

					case "variant"             : $_variant              = $_value; break;
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

//		print "files:<br>";
//		print_r($_FILES);
		// print "post:<br>";
		// print_r($_POST);


		if(isset($_FILES[$_input_name])) {
//			print "input_name:" . $_input_name;
//			print_r($_FILES["files"]);

			foreach($_FILES[$_input_name]["name"] as $index => $value) {
				if(!$_FILES[$_input_name]["error"][$index] && file_exists($_FILES[$_input_name]["tmp_name"][$index])) {

					$upload = array();
					$upload["name"] = $value;

					$extension = false;
					$temp_file = $_FILES[$_input_name]["tmp_name"][$index];
					$temp_type = $_FILES[$_input_name]["type"][$index];

//					print preg_match("/".$filegroup."/", $temp_type)."#";

					if(preg_match("/".$filegroup."/", $temp_type)) {


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
									$fs->removeDirRecursively(dirname($output_file));
									$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id.$variant);
									$fs->makeDirRecursively(dirname($output_file));

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
 								$fs->removeDirRecursively(dirname($output_file));
 								$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id.$variant);
 								$fs->makeDirRecursively(dirname($output_file));

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


// TODO: use Imagick to get image size and format

							$image = new Imagick($temp_file);

							// get input file info
							$info = $image->getImageFormat();

							// if($info) {
							// 	$input_width = $image->getImageWidth();
							// 	$input_height = $image->getImageHeight();
							// }
							// 
							// print $info.", (".$input_width."x".$input_height.")";
							// 
							// 
							// 
							// $gd = getimagesize($temp_file);
							// 
							// print_r($gd);

							// is image valid format
//							if(isset($gd["mime"])) {
							if($info) {

//								print $gd["mime"].", ". mimetypeToExtension($gd["mime"]);
								// $upload["format"] = mimetypeToExtension($gd["mime"]);
								// $upload["width"] = $gd[0];
								// $upload["height"] = $gd[1];

								$upload["format"] = preg_replace("/jpeg/", "jpg", strToLower($info));
								$upload["width"] = $image->getImageWidth();
								$upload["height"] = $image->getImageHeight();

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
									$fs->removeDirRecursively(dirname($output_file));
									$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id.$variant);
									$fs->makeDirRecursively(dirname($output_file));

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

	/**
	* Chacnge status of Item
	*/
	function status($item_id, $status) {
		$query = new Query();

		$status_states = array(
			0 => "disabled",
			1 => "enabled"
		);

		// delete item + itemtype + files
		if($query->sql("SELECT id FROM ".UT_ITEMS." WHERE id = $item_id")) {
			$query->sql("UPDATE ".UT_ITEMS." SET status = $status WHERE id = $item_id");

			message()->addMessage("Item ".$status_states[$status]);
			return true;
		}
		message()->addMessage("Item could not be ".$status_states[$status], array("type" => "error"));
		return false;

	}

	/**
	* Deprecated status functions
	*/
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

	/**
	* Delete item function
	*/
	function deleteItem($item_id) {
		$query = new Query();
		$fs = new FileSystem();

		// delete item + itemtype + files
		if($query->sql("SELECT id FROM ".UT_ITEMS." WHERE id = $item_id")) {
			
			$query->sql("DELETE FROM ".UT_ITEMS." WHERE id = $item_id");
			$fs->removeDirRecursively(PUBLIC_FILE_PATH."/$item_id");
			$fs->removeDirRecursively(PRIVATE_FILE_PATH."/$item_id");

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
			
			$sql = "SELECT item_id as id, itemtype, status FROM ".UT_TAGGINGS." as taggings, ".UT_ITEMS." as items WHERE taggings.tag_id = '$tag_id' AND taggings.item_id = items.id";
//			print $sql;
			$query->sql($sql);
			$tag["items"] = $query->results();
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
 	function globalDeleteTag($action) {


		if(count($action) == 2) {

			$tag_id = $action[1];

			$query = new Query();

			if($query->sql("DELETE FROM ".UT_TAG." WHERE id = $tag_id")) {
				message()->addMessage("Tag deleted");
				return true;
			}
		}
		message()->addMessage("Tag could not be deleted", array("type" => "error"));
		return false;
 	}

	// update tag globally
 	function globalUpdateTag($action) {

		if(count($action) == 2) {

			$tag_id = $action[1];

			$query = new Query();
		
			$context = getPost("context");
			$value = getPost("value");
			$description = getPost("description");

			if($query->sql("SELECT id FROM ".UT_TAG." WHERE id = $tag_id")) {
				$query->sql("UPDATE ".UT_TAG." SET context = '$context', value = '$value', description = '$description' WHERE id = $tag_id");

				message()->addMessage("Tag updated");
				return true;
			}
		}

		message()->addMessage("Tag could not be updated", array("type" => "error"));
		return false;
 	}



	// TODO: temporary price handler (should be updated when currencies are finalized)
	// extend price array with calulations
	// if currency is stated, just return one price
	//
	function extendPrices($prices, $_options = false) {

		$currency = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "currency"  : $currency   = $_value; break;
				}
			}
		}

		if($currency) {

			foreach($prices as $index => $price) {
				if($currency == $prices[$index]["currency"]) {
					$prices[$index]["price_with_vat"] = $price["price"] * (1 + ($price["vatrate"]/100));
					$prices[$index]["vat_of_price"] = $price["price"] * ($price["vatrate"]/100);

					return $prices[$index];
				}
			}
		}
		else {

			foreach($prices as $index => $price) {
				if(!$currency || $currency == $prices[$index]["currency"]) {
					$prices[$index]["price_with_vat"] = $price["price"]* (1 + ($price["vatrate"]/100));
					$prices[$index]["vat_of_price"] = $price["price"] * ($price["vatrate"]/100);
				}
			}
		}

		return $prices;

	}

	// get prices, 
	// TODO: extend to be able to get items ordered by price if possible
	// TODO: could/should be merged with extendPrices when currencies are finalized
	function getPrices($_options = false) {

		$item_id = false;

		$currency = false;
		$country = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "item_id"   : $item_id    = $_value; break;

					case "currency"  : $currency   = $_value; break;
					case "country"   : $country    = $_value; break;
				}
			}
		}

		$prices = array();
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

		return $prices;
	}

	// add price to item
 	function addPrice($item_id, $price, $currency) {

		$query = new Query();

		// check if price in currency exists - if it does update price
		$sql = "SELECT id FROM ".UT_PRICES." WHERE currency = '$currency' AND item_id = $item_id";
//		print $sql."<br>";
		if($query->sql($sql)) {

			$price_id = $query->result(0, "id");

			$sql = "UPDATE ".UT_PRICES." SET price = $price WHERE id = $price_id";
//			print $sql."<br>";
			if($query->sql($sql)) {
				message()->addMessage("Price updated");
				return true;
			}
		}
		// insert price
		else {

			$sql = "INSERT INTO ".UT_PRICES." VALUES(DEFAULT, $item_id, $price, '$currency')";
//			print $sql."<br>";
			if($query->sql($sql)) {
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