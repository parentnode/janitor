<?php
/**
* @package janitor.itemtypes
* This file contains itemtype functionality
*/

class TypeArticle extends Itemtype {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		// construct ItemType before adding to model
		parent::__construct(get_class());


		// itemtype database
		$this->db = SITE_DB.".item_article";


		// Published
		$this->addToModel("published_at", array(
			"hint_message" => "Publishing date of the article. Leave empty for current time",
		));

		// Name
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Headline",
			"required" => true,
			"hint_message" => "Headline of your article", 
			"error_message" => "Headline must be filled out."
		));

		// Secondary headline
		$this->addToModel("subheader", array(
			"type" => "string",
			"label" => "Secondary headline",
			"hint_message" => "Secondary headline of your article", 
			"error_message" => "Secondary headline contains illegal characters."
		));

		// description
		$this->addToModel("description", array(
			"type" => "text",
			"label" => "Short description",
			"hint_message" => "Write a short description of the article",
			"error_message" => "A short description without any words? How weird."
		));

		// HTML
		$this->addToModel("html", array(
			"hint_message" => "Write your article",
			"allowed_tags" => "p,h2,h3,h4,ul,ol,download,jpg,png,code", //,mp4,vimeo,youtube,code",
		));

		// Single media
		$this->addToModel("single_media", array(
			"allowed_sizes" => "960x540",
			"allowed_formats" => "png,jpg",
			"hint_message" => "Add single image/video by dragging it here. MP4, PNG or JPG allowed in 960x540"
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