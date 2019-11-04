<?php
/**
* @package janitor.items
* This file contains item type functionality
*/

class TypePerson extends Itemtype {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		parent::__construct(get_class());


		// itemtype database
		$this->db = SITE_DB.".item_person";

		// Name
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Name",
			"required" => true,
			"hint_message" => "Person name", 
			"error_message" => "Person needs a name."
		));

		// Description
		$this->addToModel("description", array(
			"type" => "text",
			"label" => "Short description",
			"hint_message" => "Write a short description of the Person.",
			"error_message" => "A short description without any words? How weird."
		));

		// HTML
		$this->addToModel("html", array(
			"type" => "html",
			"label" => "Full description",
			"allowed_tags" => "p,h3",
			"hint_message" => "Write a full description of the Person.",
			"error_message" => "A full description without any words? How weird."
		));

		// Job Title
		$this->addToModel("job_title", array(
			"type" => "string",
			"label" => "Job Title",
			"hint_message" => "Job Title", 
			"error_message" => "Job Title is invalid."
		));
		// Email
		$this->addToModel("email", array(
			"type" => "email",
			"label" => "Email",
			"hint_message" => "Email of Person.", 
			"error_message" => "Email is invalid."
		));
		// Phonenumber
		$this->addToModel("tel", array(
			"type" => "tel",
			"label" => "Phone",
			"hint_message" => "Phonenumber of Person.", 
			"error_message" => "Phonenumber is invalid."
		));
		// Single media
		$this->addToModel("single_media", array(
			"type" => "files",
			"label" => "Drag Image here",
			"allowed_sizes" => "500x500",
			"allowed_formats" => "png,jpg,mp4",
			"max" => 1,
			"hint_message" => "Add single image by dragging it here. PNG, JPG, MP4 allowed in 500x500.",
			"error_message" => "Image does not fit requirements."
		));

	}

}

?>