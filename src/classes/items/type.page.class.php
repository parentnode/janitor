<?php
/**
* @package janitor.itemtypes
* This file contains itemtype functionality
*/

class TypePage extends Itemtype {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		// construct ItemType before adding to model
		parent::__construct(get_class());


		// itemtype database
		$this->db = SITE_DB.".item_page";


		// Published
		$this->addToModel("published_at", array(
			"type" => "datetime",
			"label" => "Publish date (yyyy-mm-dd hh:mm)",
			"hint_message" => "Publication date and time of page. This will be shown on website. Leave empty for current time",
			"error_message" => "Datetime must be of format (yyyy-mm-dd hh:mm)"
		));

		// Name
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Title",
			"searchable" => true,
			"required" => true,
			"hint_message" => "Title of your page", 
			"error_message" => "Title must be filled out."
		));

		// Secondary headline
		$this->addToModel("subheader", array(
			"type" => "string",
			"label" => "Secondary headline",
			"searchable" => true,
			"hint_message" => "Secondary headline of your page", 
			"error_message" => "Secondary headline contains illegal characters."
		));

		// Description
		$this->addToModel("description", array(
			"type" => "text",
			"label" => "Short description",
			"hint_message" => "Write a short description of the page. It is used for page listings and SEO.",
			"error_message" => "Your page needs a description"
		));

		// HTML
		$this->addToModel("html", array(
			"type" => "html",
			"label" => "Full page text",
			"searchable" => true,
			"allowed_tags" => "p,h2,h3,h4,ul,ol,code,download,jpg,png",
			"hint_message" => "Write!",
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