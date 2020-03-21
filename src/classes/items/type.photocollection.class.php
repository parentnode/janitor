<?php
/**
* @package janitor.itemtypes
* This file contains itemtype functionality
*/

class TypePhotocollection extends Itemtype {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		// construct ItemType before adding to model
		parent::__construct(get_class());


		// itemtype database
		$this->db = SITE_DB.".item_photocollection";


		// Name
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Title",
			"required" => true,
			"hint_message" => "Title of your page", 
			"error_message" => "Title must be filled out."
		));

		// description
		$this->addToModel("description", array(
			"type" => "text",
			"label" => "Short SEO description",
			"max" => 155,
			"hint_message" => "Write a short description of the photo collection for SEO and listings.",
			"error_message" => "Your photo collection needs a description – max 155 characters."
		));

		// Classname
		$this->addToModel("classname", array(
			"type" => "string",
			"label" => "CSS classname",
			"hint_message" => "Optional CSS classname - leave blank if you don't know what this is.", 
			"error_message" => ""
		));

		// Mediae
		$this->addToModel("mediae", array(
			"type" => "files",
			"label" => "Add media here",
			"max" => 40,
			"allowed_formats" => "png,jpg",
			"hint_message" => "Add images here. Use png or jpg.",
			"error_message" => "Media does not fit requirements."
		));

	}

	//
	// // Custom get item with media
	// function get($item_id) {
	// 	$query = new Query();
	// 	$query_media = new Query();
	//
	// 	if($query->sql("SELECT * FROM ".$this->db." WHERE item_id = $item_id")) {
	// 		$item = $query->result(0);
	// 		unset($item["id"]);
	//
	// 		$item["mediae"] = false;
	//
	// 		// get mediae
	// 		if($query_media->sql("SELECT * FROM ".$this->db_mediae." WHERE item_id = $item_id ORDER BY position ASC, id DESC")) {
	//
	// 			$mediae = $query_media->results();
	// 			foreach($mediae as $i => $media) {
	// 				$variant = $media["variant"];
	// 				$item["mediae"][$variant]["id"] = $media["id"];
	// 				$item["mediae"][$variant]["name"] = $media["name"];
	// 				$item["mediae"][$variant]["variant"] = $variant;
	// 				$item["mediae"][$variant]["format"] = $media["format"];
	// 				$item["mediae"][$variant]["width"] = $media["width"];
	// 				$item["mediae"][$variant]["height"] = $media["height"];
	// 				$item["mediae"][$variant]["filesize"] = $media["filesize"];
	// 			}
	// 		}
	//
	// 		return $item;
	// 	}
	// 	else {
	// 		return false;
	// 	}
	// }
	//
	//
	// // CMS SECTION
	// // custom loopback functions
	//
	//
	// // custom function to add media
	// // /janitor/photocollection/addMedia/#item_id# (post image)
	// function addMedia($action) {
	//
	// 	if(count($action) == 2) {
	// 		$query = new Query();
	// 		$IC = new Items();
	// 		$item_id = $action[1];
	//
	// 		$query->checkDbExistence($this->db_mediae);
	//
	// 		if($this->validateList(array("mediae"), $item_id)) {
	// 			$uploads = $IC->upload($item_id, array("input_name" => "mediae", "auto_add_variant" => true));
	// 			if($uploads) {
	//
	// 				$return_values = array();
	//
	// 				foreach($uploads as $upload) {
	// 					$query->sql("INSERT INTO ".$this->db_mediae." VALUES(DEFAULT, $item_id, '".$upload["name"]."', '".$upload["format"]."', '".$upload["variant"]."', '".$upload["width"]."', '".$upload["height"]."', '".$upload["filesize"]."', 0)");
	//
	// 					$return_values[] = array(
	// 						"item_id" => $item_id,
	// 						"name" => $upload["name"],
	// 						"media_id" => $query->lastInsertId(),
	// 						"variant" => $upload["variant"],
	// 						"format" => $upload["format"],
	// 						"width" => $upload["width"],
	// 						"height" => $upload["height"],
	// 						"filesize" => $upload["filesize"]
	// 					);
	// 				}
	//
	// 				return $return_values;
	// 			}
	// 		}
	// 	}
	//
	// 	return false;
	// }
	//
	//
	// // delete image - 3 parameters exactly
	// // /janitor/photocollection/deleteImage/#item_id#/#variant#
	// function deleteMedia($action) {
	//
	// 	if(count($action) == 3) {
	//
	// 		$query = new Query();
	// 		$fs = new FileSystem();
	//
	// 		$sql = "DELETE FROM ".$this->db_mediae." WHERE item_id = ".$action[1]." AND variant = '".$action[2]."'";
	// 		if($query->sql($sql)) {
	// 			$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$action[1]."/".$action[2]);
	// 			$fs->removeDirRecursively(PRIVATE_FILE_PATH."/".$action[1]."/".$action[2]);
	//
	// 			message()->addMessage("Media deleted");
	// 			return true;
	// 		}
	// 	}
	//
	// 	message()->addMessage("Media could not be deleted", array("type" => "error"));
	// 	return false;
	// }
	//
	//
	// // update media order
	// // /janitor/photocollection/updateMediaOrder (comma-separated order in POST)
	// function updateMediaOrder($action) {
	//
	// 	$order_list = getPost("order");
	// 	if(count($action) == 1 && $order_list) {
	//
	// 		$query = new Query();
	// 		$order = explode(",", $order_list);
	//
	// 		for($i = 0; $i < count($order); $i++) {
	// 			$media_id = $order[$i];
	// 			$sql = "UPDATE ".$this->db_mediae." SET position = ".($i+1)." WHERE id = ".$media_id;
	// 			$query->sql($sql);
	// 		}
	//
	// 		message()->addMessage("Media order updated");
	// 		return true;
	// 	}
	//
	// 	message()->addMessage("Media order could not be updated - refresh your browser", array("type" => "error"));
	// 	return false;
	//
	// }
	//
	// // Update media name
	// // /janitor/photocollection/updateMediaName
	// function updateMediaName($action) {
	//
	// 	if(count($action) == 3) {
	//
	// 		$query = new Query();
	// 		$name = getPost("name");
	//
	// 		$sql = "UPDATE ".$this->db_mediae." SET name = '$name' WHERE item_id = ".$action[1]." AND variant = '".$action[2]."'";
	// 		if($query->sql($sql)) {
	// 			message()->addMessage("Media name updated");
	// 			return true;
	// 		}
	// 	}
	//
	// 	message()->addMessage("Media name could not be updated - please refresh your browser", array("type" => "error"));
	// 	return false;
	// }
	//
	//
	// // Update item order
	// // /janitor/photocollection/updateOrder (order comma-separated in POST)
	// function updateOrder($action) {
	//
	// 	$order_list = getPost("order");
	// 	if(count($action) == 1 && $order_list) {
	//
	// 		$query = new Query();
	// 		$order = explode(",", $order_list);
	//
	// 		for($i = 0; $i < count($order); $i++) {
	// 			$item_id = $order[$i];
	// 			$sql = "UPDATE ".$this->db." SET position = ".($i+1)." WHERE item_id = ".$item_id;
	// 			$query->sql($sql);
	// 		}
	//
	// 		message()->addMessage("Collection order updated");
	// 		return true;
	// 	}
	//
	// 	message()->addMessage("Collection order could not be updated - please refresh your browser", array("type" => "error"));
	// 	return false;
	//
	// }
}

?>