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
	* Delete item function
	*/
	# /janitor/[admin/]#itemtype#/delete/#item_id#
	function delete($action) {

		if(count($action) == 2) {
//			$itemtype = $action[0];
			$item_id = $action[1];

			$query = new Query();
			$fs = new FileSystem();

			// delete item + itemtype + files
			if($query->sql("SELECT id FROM ".UT_ITEMS." WHERE id = $item_id AND itemtype = '$this->itemtype'")) {


				// EXPERIMENTAL: include pre/post functions to all itemtype.core functions to make extendability better
				$pre_delete_state = true;

				// itemtype pre delete handler?
				if(method_exists($this, "preDelete")) {
					$pre_delete_state = $this->preDelete($item_id);
				}

				// pre delete state allows full delete
				if($pre_delete_state) {
					$query->sql("DELETE FROM ".UT_ITEMS." WHERE id = $item_id");
					$fs->removeDirRecursively(PUBLIC_FILE_PATH."/$item_id");
					$fs->removeDirRecursively(PRIVATE_FILE_PATH."/$item_id");

					message()->addMessage("Item deleted");


					// itemtype post delete handler?
					if(method_exists($this, "postDelete")) {
						$this->postDelete($item_id);
					}


					return true;
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
	function sindex($sindex, $item_id) {

		$query = new Query();

		// superNormalize $sindex suggetion
		$sindex = superNormalize(substr($sindex, 0, 40));

		// check for existance
		// update if sindex does not exist already
		if(!$query->sql("SELECT sindex FROM ".UT_ITEMS." WHERE sindex = '$sindex' AND id != $item_id")) {
			$query->sql("UPDATE ".UT_ITEMS." SET sindex = '$sindex' WHERE id = $item_id");
		}

		// add counter to the end of sindex
		else {
			// does sindex have counter, then increase it
			preg_match("/-([\d]+)$/", $sindex, $matches);
			if($matches && is_numeric($matches[1])) {
				$counter = ($matches[1] + 1);
				$sindex = preg_replace("/-([\d]+)$/", "", $sindex);
			}
			// start with 1
			else {
				$counter = 1;
			}
			$sindex = $sindex."-".$counter;

			// recurse until valid sindex has been generated
			$sindex = $this->sindex($sindex, $item_id);
		}

		return $sindex;
	}




	// DATA HANDLING



	// SAVE

	# /janitor/[admin/]#itemtype#/save
	function save($action) {
		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate (only name required on save)
		if($this->validateList(array("name"))) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db);

			// create root item
			$item_id = $this->saveItem();
			if($item_id) {

				// get entities for current value
				$entities = $this->getModel();
				$names = array();
				$values = array();

				foreach($entities as $name => $entity) {
					if($entity["value"] !== false && !preg_match("/^(files|tags|prices)$/", $entity["type"]) && !preg_match("/^(published_at|status|htmleditor_file)$/", $name)) {

						// consider reimplementing files in basic save
						// it's a bigger question
						// - are files, tags, prices, comments and ratings all external features or integrated parts of an Item

						$names[] = $name;
						$values[] = $name."='".$entity["value"]."'";
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
						if(method_exists($this, "postSave")) {
							$this->postSave($item_id);
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
			$query->checkDbExistance($this->db);


			// get entities for current value
			$entities = $this->getModel();
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false && !preg_match("/^(files|tags|prices)$/", $entity["type"]) && !preg_match("/^(published_at|status|user_id|htmleditor_file)$/", $name)) {

					// consider reimplementing files in basic save
					// it's a bigger question
					// - are files, tags, prices, comments and ratings all external features or integrated parts of an Item

					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($this->validateList($names, $item_id) && $this->updateItem($item_id)) {

				// add existing data to version-control-table
				$query->versionControl($item_id, $values);

				$sql = "UPDATE ".$this->db." SET ".implode(",", $values)." WHERE item_id = ".$item_id;
//				print $sql;
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
					$this->sindex($sindex, $item_id);

					message()->addMessage("Item updated");

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

	//			print "SELECT id FROM ".UT_TAG." WHERE context = '$context' AND value = '$value'<br>";
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

			$query->checkDbExistance(UT_ITEMS_MEDIAE);

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

			$query->checkDbExistance(UT_ITEMS_MEDIAE);

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

							include_once("classes/system/video.class.php");
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

							include_once("classes/system/audio.class.php");
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

			$query->checkDbExistance(UT_ITEMS_MEDIAE);

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

			$query->checkDbExistance(UT_ITEMS_MEDIAE);

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
						$public_file = PUBLIC_FILE_PATH."/".$item_id.$variant."/".superNormalize(substr(preg_replace("/\.[a-zA-Z1-9]{3,4}$/", "", $upload["name"]), 0, 30)).".pdf";
						

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
						$public_file = PUBLIC_FILE_PATH."/".$item_id.$variant."/".superNormalize(substr(preg_replace("/\.[a-zA-Z1-9]{3,4}$/", "", $upload["name"]), 0, 30)).".zip";
						

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
						$zip_name = superNormalize(substr(preg_replace("/\.[a-zA-Z1-9]{3,4}$/", "", $upload["name"]), 0, 30));
						$public_file = PUBLIC_FILE_PATH."/".$item_id.$variant."/".$zip_name.".zip";

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

			if($this->validateList(array("comment"), $item_id)) {

				$user_id = session()->value("user_id");
				$comment = $this->getProperty("comment", "value");

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

			if($this->validateList(array("comment"), $item_id)) {

				$comment = $this->getProperty("comment", "value");

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

			if($this->validateList(array("price", "currency", "vatrate_id"), $item_id)) {

				$price = $this->getProperty("price", "value");
				$currency = $this->getProperty("currency", "value");
				$vatrate_id = $this->getProperty("vatrate_id", "value");

				$price = preg_replace("/,/", ".", $price);

				if($query->sql("INSERT INTO ".UT_ITEMS_PRICES." VALUES(DEFAULT, $item_id, '$price', '$currency', '$vatrate_id')")) {
					message()->addMessage("Price added");

					$price_id = $query->lastInsertId();
					$IC = new Items();
					$new_price = $IC->getPrices(array("price_id" => $price_id));
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



	// delete price
	// /janitor/[admin/]#itemtype#/updateSubscriptionMethod/#item_id#
	// subscription method is sent in $_POST
	
	// TODO: implement itemtype checks
 	function updateSubscriptionMethod($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();


		if(count($action) == 2) {

			$query = new Query();
			$item_id = $action[1];
			$subscription_method = getPost("subscription_method");

			// insert or update
			if($subscription_method) {

				$sql = "SELECT id FROM ".UT_ITEMS_SUBSCRIPTION_METHOD." WHERE item_id = $item_id";
				if($query->sql($sql)) {
				
					if($query->sql("UPDATE ".UT_ITEMS_SUBSCRIPTION_METHOD." SET subscription_method_id = '$subscription_method' WHERE id = $comment_id AND item_id = $item_id")) {
						message()->addMessage("Subscription method updated");

						$IC = new Items();
						$subscription_method = $IC->getSubscriptionMethod(array("item_id" => $item_id));
						return $subscription_method;
					}
					
				}
				else {

					if($query->sql("INSERT INTO ".UT_ITEMS_SUBSCRIPTION_METHOD." VALUES(DEFAULT, $item_id, $subscription_method)")) {
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

		message()->addMessage("Subscription method could not be changed", array("type" => "error"));
		return false;

	}



	// READ STATES - INDICATES THAT THE USER HAS READ THE ITEM

	// update readstate for user+item
	// enables adding a button for the user to indicate wheter an item has been read
	// disabled for user_id = 1 (guest)

	// /janitor/[admin/]#itemtype#/updateReadstate/#item_id#
	function updateReadstate($action) {

		if(count($action) == 2) {

			$query = new Query();
			$item_id = $action[1];
			$user_id = session()->value("user_id");

			if($user_id > 1) {

				$query->checkDbExistance(UT_ITEMS_READSTATE);

				if($query->sql("SELECT ".UT_ITEMS_READSTATE." WHERE user_id = $user_id AND item_id = $item_id")) {
					$sql = "UPDATE ".UT_ITEMS_READSTATE." SET read_at = CURRENT_TIMESTAMP WHERE user_id = $user_id AND item_id = $item_id";
				}
				else {
					$sql = "INSERT INTO ".UT_ITEMS_READSTATE." VALUES(DEFAULT, $item_id, $user_id, DEFAULT)";
				}

				if($query->sql($sql)) {
					message()->addMessage("Read state updated");
					return true;
				}
			}
		}

		message()->addMessage("Read state could not be updated", array("type" => "error"));
		return false;
	}


	// delete Read state
	// /janitor/[admin/]#itemtype#/deleteReadstate/#item_id#
	// disabled for user_id = 1 (guest)
	// TODO: implement itemtype checks
 	function deleteReadstate($action) {

		if(count($action) == 2) {

			$query = new Query();
			$item_id = $action[1];
			$user_id = session()->value("user_id");

			if($user_id > 1) {

				if($query->sql("DELETE FROM ".UT_ITEMS_READSTATE." WHERE item_id = $item_id AND user_id = $user_id")) {

					message()->addMessage("Read state deleted");
					return true;
				}
			}
		}

		message()->addMessage("Read state could not be deleted", array("type" => "error"));
		return false;
	}




	// HERE OR THERE, I DON'T KNOW WHERE
	// MAYBE IN IC - BUT NEED EASY WAY TO OVERRIDE IN TYPE


	// SHOULD REPLACE Items::getSimpleType?
	// Custom get item with media
	// function get($item_id) {
	// 	$query = new Query();
	//
	// 	if($query->sql("SELECT * FROM ".$this->db." WHERE item_id = $item_id")) {
	// 		$item = $query->result(0);
	// 		unset($item["id"]);
	//
	// 		$item["mediae"] = false;
	//
	// 		// get media
	// 		if($query->sql("SELECT * FROM ".UT_ITEMS_MEDIAE." WHERE item_id = $item_id AND variant NOT LIKE 'HTML-%' ORDER BY position ASC, id DESC")) {
	//
	// 			$mediae = $query->results();
	// 			foreach($mediae as $i => $media) {
	// 				$variant = $media["variant"];
	// 				$item["mediae"][$variant]["variant"] = $variant;
	//
	// 				$item["mediae"][$variant]["id"] = $media["id"];
	// 				$item["mediae"][$variant]["name"] = $media["name"];
	// 				$item["mediae"][$variant]["format"] = $media["format"];
	// 				$item["mediae"][$variant]["width"] = $media["width"];
	// 				$item["mediae"][$variant]["height"] = $media["height"];
	// 				$item["mediae"][$variant]["filesize"] = $media["filesize"];
	// 				$item["mediae"][$variant]["position"] = $media["position"];
	// 			}
	// 		}
	//
	// 		return $item;
	// 	}
	// 	else {
	// 		return false;
	// 	}
	// }



}