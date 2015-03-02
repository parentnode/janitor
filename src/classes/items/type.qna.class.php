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


}

?>