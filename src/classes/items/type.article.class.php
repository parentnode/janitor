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
			"type" => "datetime",
			"label" => "Publish date (yyyy-mm-dd hh:mm)",
			"hint_message" => "Publishing date of the article. Leave empty for current time",
			"error_message" => "Datetime must be of format (yyyy-mm-dd hh:mm)"
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
			"label" => "Short SEO description",
			"max" => 155,
			"hint_message" => "Write a short description of the article for SEO and listings.",
			"error_message" => "Your article needs a description – max 155 characters."
		));

		// HTML
		$this->addToModel("html", array(
			"type" => "html",
			"label" => "Full article",
			"allowed_tags" => "p,h2,h3,h4,ul,ol,download,jpg,png,code", //,mp4,vimeo,youtube,code",
			"hint_message" => "Write your article",
			"error_message" => "No words? How weird."
		));

		// Single media
		$this->addToModel("single_media", array(
			"type" => "files",
			"label" => "Add media here",
			"allowed_sizes" => "960x540",
			"max" => 1,
			"allowed_formats" => "png,jpg,mp4",
			"hint_message" => "Add single image/video by dragging it here. MP4, PNG or JPG allowed in 960x540",
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