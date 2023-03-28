<?php
/**
* @package janitor.itemtypes
* This file contains itemtype functionality
*/

class TypePost extends Itemtype {


	public $db;


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
			"type" => "datetime",
			"label" => "Publishing time (yyyy-mm-dd hh:mm)",
			"hint_message" => "Date of the post publication (yyyy-mm-dd hh:mm). Leave empty for current time.", 
			"error_message" => "Date of the post publication must be a valid date (yyyy-mm-dd hh:mm). Leave empty for current time.", 
		));

		// Name
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Name",
			"required" => true,
			"searchable" => true,
			"hint_message" => "Name your post", 
			"error_message" => "Name must be filled out."
		));

		// Class
		$this->addToModel("classname", array(
			"type" => "string",
			"label" => "CSS Class",
			"hint_message" => "CSS class for custom styling. If you don't know what this is, just leave it empty."
		));

		// description
		$this->addToModel("description", array(
			"type" => "text",
			"label" => "Short SEO description",
			"max" => 155,
			"hint_message" => "Write a short description of the post for SEO and listings.",
			"error_message" => "Your post needs a description – max 155 characters."
		));

		// HTML
		$this->addToModel("html", array(
			"type" => "html",
			"label" => "Full post",
			"searchable" => true,
			"allowed_tags" => "p,h2,h3,h4,ul,ol,download,jpg,png,code,vimeo,youtube", //,mp4",
			"hint_message" => "Write your the post",
			"error_message" => "No words? How weird."
		));

		// Mediae
		$this->addToModel("mediae", array(
			"type" => "files",
			"label" => "Add media here",
			"max" => 20,
			"allowed_formats" => "png,jpg,mp4",
			"hint_message" => "Add images or videos here. Use png, jpg or mp4.",
			"error_message" => "Media does not fit requirements."
		));

	}

}

?>