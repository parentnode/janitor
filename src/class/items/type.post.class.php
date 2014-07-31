<?php
/**
* @package janitor.items
* This file contains Log maintenance functionality
*/


class TypePost extends Model {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		// itemtype database
		$this->db = SITE_DB.".item_post";
		$this->db_mediae = SITE_DB.".item_post_mediae";


		// Published
		$this->addToModel("published_at", array(
			"type" => "datetime",
			"label" => "Publish date (yyyy-mm-dd hh:mm:ss)",
			"pattern" => "^[\d]{4}-[\d]{2}-[\d]{2}[0-9\-\/ \:]*$",
			"hint_message" => "Date of the log entry. Leave empty for current time", 
			"error_message" => "Date must be of format (yyyy-mm-dd hh:mm:ss)"
		));

		// Name
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Name",
			"required" => true,
			"hint_message" => "Name your log entry", 
			"error_message" => "Name must be filled out."
		));

		// description
		$this->addToModel("description", array(
			"type" => "text",
			"label" => "Short description",
			"hint_message" => "Write a short description of the log entry",
			"error_message" => "A short description without any words? How weird."
		));

		// HTML
		$this->addToModel("html", array(
			"type" => "html",
			"label" => "HTML",
			"required" => true,
			"hint_message" => "Write the log entry",
			"error_message" => "A log without any words? How weird."
		));


		// Files
		$this->addToModel("files", array(
			"type" => "files",
			"label" => "Add media here",
			"allowed_formats" => "png,jpg,mp4",
			"hint_message" => "Add images or videos here. Use png, jpg or mp4.",
			"error_message" => "Media does not fit requirements."
		));


		// Tags
		$this->addToModel("tags", array(
			"type" => "tags",
			"label" => "Tag",
			"hint_message" => "Start typing to get suggestions. A correct tag has this format: context:value.",
			"error_message" => "Must be correct Tag format."
		));


		parent::__construct();
	}


	/**
	* Get item
	*/
	function get($item_id) {
		$query = new Query();
		$query_media = new Query();

		if($query->sql("SELECT * FROM ".$this->db." WHERE item_id = $item_id")) {
			$item = $query->result(0);
			unset($item["id"]);

			$item["mediae"] = false;

			// get media
			if($query_media->sql("SELECT * FROM ".$this->db_mediae." WHERE item_id = $item_id ORDER BY position ASC, id DESC")) {

				$mediae = $query_media->results();
				foreach($mediae as $i => $media) {
					$item["mediae"][$i]["id"] = $media["id"];
					$item["mediae"][$i]["variant"] = $media["variant"];
					$item["mediae"][$i]["format"] = $media["format"];
					$item["mediae"][$i]["width"] = $media["width"];
					$item["mediae"][$i]["height"] = $media["height"];
				}
			}

			return $item;
		}
		else {
			return false;
		}
	}

	// CMS SECTION




	// update item type - based on posted values
	function update($item_id) {

		$query = new Query();
		$IC = new Item();

		$query->checkDbExistance($this->db);
		$query->checkDbExistance($this->db_mediae);

		$uploads = $IC->upload($item_id, array("auto_add_variant" => true));
		if($uploads) {
			foreach($uploads as $upload) {
				$query->sql("INSERT INTO ".$this->db_mediae." VALUES(DEFAULT, $item_id, '".$upload["name"]."', '".$upload["format"]."', '".$upload["variant"]."', ".$upload["width"].", ".$upload["height"].", 0)");
			}
		}


		$entities = $this->data_entities;
		$names = array();
		$values = array();

		foreach($entities as $name => $entity) {
			if($entity["value"] != false && $name != "published_at" && $name != "status" && $name != "tags" && $name != "prices") {
				$names[] = $name;
				$values[] = $name."='".$entity["value"]."'";
			}
		}

		if($this->validateList($names, $item_id)) {
			if($values) {
				$sql = "UPDATE ".$this->db." SET ".implode(",", $values)." WHERE item_id = ".$item_id;
//				print $sql;
			}

			if(!$values || $query->sql($sql)) {
				return true;
			}
		}

		return false;
	}


	// custom loopback function

	// delete post image - 4 parameters exactly
	// /post/#item_id#/deleteImage/#image_id#
	function deleteMedia($action) {

		if(count($action) == 4) {

			$query = new Query();
			$fs = new FileSystem();

			$sql = "DELETE FROM ".$this->db_mediae." WHERE item_id = ".$action[1]." AND variant = '".$action[3]."'";
			if($query->sql($sql)) {
				$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$action[1]."/".$action[3]);
				$fs->removeDirRecursively(PRIVATE_FILE_PATH."/".$action[1]."/".$action[3]);

				message()->addMessage("Media deleted");
				return true;
			}
		}

		message()->addMessage("Media could not be deleted", array("type" => "error"));
		return false;
	}


	function updateMediaOrder($action) {

		$order_list = getPost("order");
		if(count($action) == 1 && $order_list) {

			$query = new Query();
			$order = explode(",", $order_list);

			for($i = 0; $i < count($order); $i++) {
				$media_id = $order[$i];
				$sql = "UPDATE ".$this->db_mediae." SET position = ".($i+1)." WHERE id = ".$media_id;
				$query->sql($sql);
			}

			message()->addMessage("Media order updated");
			return true;
		}

		message()->addMessage("Media order could not be updated - refresh your browser", array("type" => "error"));
		return false;

	}
}

?>