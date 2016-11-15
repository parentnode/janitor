<?php
/**
* @package janitor.itemtypes
* This file contains itemtype functionality
*/

class TypeQna extends Itemtype {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		// construct ItemType before adding to model
		parent::__construct(get_class());


		// itemtype database
		$this->db = SITE_DB.".item_qna";

		// Name
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Question snippet",
			"required" => true,
			"hint_message" => "Question snippet (less then 100 chars)",
			"error_message" => "Question snippet must be filled out."
		));

		// related to item
		$this->addToModel("about_item_id", array(
			"type" => "item_id",
			"label" => "Select item to ask question about",
			"hint_message" => "Please select an item",
			"error_message" => "Please select an item"
		));

		// Question
		$this->addToModel("question", array(
			"type" => "text",
			"label" => "Question",
			"required" => true,
			"hint_message" => "Be precise, brief and make it easy to understand.",
			"error_message" => "Question must be filled out."
		));

		// Answer
		$this->addToModel("answer", array(
			"type" => "text",
			"label" => "Answer",
			"hint_message" => "Be precise and make it easy to understand.",
			"error_message" => "Answer must be filled out."
		));

	}


	// CMS SECTION
	// custom loopback function

	// update name based on question
	function postSave($item_id) {

		$IC = new Items();
		$item = $IC->getItem(["id" => $item_id, "extend" => true]);

		$_POST["name"] = cutString($item["question"], 45);
		$this->update(["update", $item_id]);

	}

}

?>