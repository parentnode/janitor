<?php
/**
* @package janitor.items
* This file contains item type functionality
*/

class TypeSubscription extends Itemtype {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		parent::__construct(get_class());


		// itemtype database
		$this->db = SITE_DB.".item_subscription";


		// Name
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Name",
			"required" => true,
			"hint_message" => "Event name", 
			"error_message" => "Event needs a name."
		));

		// Description
		$this->addToModel("description", array(
			"type" => "text",
			"label" => "Short description",
			"hint_message" => "Write a short description of the subscription for SEO.",
			"error_message" => "A short description without any words? How weird."
		));

		// HTML
		$this->addToModel("html", array(
			"type" => "html",
			"label" => "Full description",
			"hint_message" => "Write a full description of the subscription.",
			"error_message" => "A full description without any words? How weird."
		));


		// Interval
		$this->addToModel("interval", array(
			"type" => "string",
			"label" => "Interval",
			"required" => true,
			"hint_message" => "Use Cron syntax for interval description.", 
			"error_message" => "Invalid interval."
		));


		// // Start datetime
		// $this->addToModel("starting_at", array(
		// 	"type" => "datetime",
		// 	"label" => "Starts at",
		// 	"requied" => true,
		// 	"hint_message" => "When does the subscription start.",
		// 	"error_message" => "You need to enter a valid date/time."
		// ));
		// // End datetime
		// $this->addToModel("ending_at", array(
		// 	"type" => "datetime",
		// 	"label" => "Ends at",
		// 	"hint_message" => "When does the subscription end.",
		// 	"error_message" => "You need to enter a valid date/time."
		// ));

	}


}

?>