<?php
/**
* @package janitor.itemtypes
* This file contains itemtype functionality
*/

class TypePost extends Itemtype {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		// construct ItemType before adding to model
		parent::__construct(get_class());


		// itemtype database
		$this->db = SITE_DB.".item_post";


		// Published
		$this->addToModel("published_at", array(
			"hint_message" => "Publishing date of the post. Leave empty for current time",
		));

		// Name
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Name",
			"required" => true,
			"hint_message" => "Name your post", 
			"error_message" => "Name must be filled out."
		));

		// description
		$this->addToModel("description", array(
			"type" => "text",
			"label" => "Short description",
			"hint_message" => "Write a short description of the post",
			"error_message" => "A short description without any words? How weird."
		));

		// HTML
		$this->addToModel("html", array(
			"hint_message" => "Write your the post",
			"allowed_tags" => "p,h2,h3,h4,ul,ol,download,jpg,png,code", //,mp4,vimeo,youtube,code",
		));

		// Files
		$this->addToModel("mediae", array(
			"label" => "Add media here",
			"allowed_formats" => "png,jpg,mp4",
			"hint_message" => "Add images or videos here. Use png, jpg or mp4.",
		));

	}

}

?>