<?php
/**
* @package janitor.itemtypes
* This file contains itemtype functionality
*/

class TypeBlog extends Itemtype {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		// construct ItemType before adding to model
		parent::__construct(get_class());


		// itemtype database
		$this->db = SITE_DB.".item_blog";

		// Name
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Name of blog",
			"required" => true,
			"hint_message" => "Name of blog", 
			"error_message" => "Name must be filled out."
		));

		// Description
		$this->addToModel("description", array(
			"type" => "text",
			"label" => "Short SEO description",
			"max" => 155,
			"hint_message" => "Write a short description of the blog for SEO and listings.",
			"error_message" => "Your blog needs a description – max 155 characters."
		));

		// Author
		$this->addToModel("author", array(
			"type" => "string",
			"label" => "Author",
			"required" => true,
			"hint_message" => "Author of blog", 
			"error_message" => "Author contains illegal characters."
		));

		// title
		$this->addToModel("title", array(
			"type" => "string",
			"label" => "Title",
			"hint_message" => "Title of author", 
			"error_message" => "Title contains illegal characters."
		));

		// Short biography
		$this->addToModel("bio", array(
			"type" => "text",
			"label" => "Short biography",
			"hint_message" => "Write a short biography of the author.",
			"error_message" => "Your author needs a biography."
		));

		// HTML
		$this->addToModel("html", array(
			"type" => "html",
			"label" => "Full description",
			"required" => true,
			"allowed_tags" => "p,h2,h3,h4,ul,ol,download,jpg,png,code,vimeo,youtube", //,mp4",
			"hint_message" => "Full blog description",
			"error_message" => "No words? How weird."
		));

		// Single media
		$this->addToModel("single_media", array(
			"type" => "files",
			"label" => "Add media here",
			"allowed_sizes" => "960x540",
			"max" => 1,
			"allowed_formats" => "png,jpg",
			"hint_message" => "Add single image by dragging it here. PNG or JPG allowed in 960x540",
			"error_message" => "Media does not fit requirements."
		));

	}

	// Add blog tag on save
	function saved($item_id) {

		$IC = new Items();
		$item = $IC->getItem(["id" => $item_id, "extend" => true]);

		$_POST["tags"] = "blog:".$item["name"];
		$this->addTag(["addTag", $item_id]);

	}

	// Update (or add) blog tag on update
	function updated($item_id) {

		$IC = new Items();
		$item = $IC->getItem(["id" => $item_id, "extend" => true]);

		$tags = $IC->getTags(["item_id" => $item_id, "context" => "blog"]);
		if($tags) {
			$tag_id = $tags[0]["id"];

			$TC = new Tag();
			$_POST["context"] = "blog";
			$_POST["value"] = $item["name"];
			$TC->updateTag(["updateTag", $tag_id]);

		}
		else {

			$_POST["tags"] = "blog:".$item["name"];
			$this->addTag(["addTag", $item_id]);

		}

	}

}

?>