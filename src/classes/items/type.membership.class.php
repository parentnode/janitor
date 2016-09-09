<?php
/**
* @package janitor.items
* This file contains item type functionality
*/

class TypeMembership extends Itemtype {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		parent::__construct(get_class());


		// itemtype database
		$this->db = SITE_DB.".item_membership";


		// Name
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Name",
			"required" => true,
			"hint_message" => "Membership name", 
			"error_message" => "Membership needs a name."
		));

		// Class
		$this->addToModel("classname", array(
			"type" => "string",
			"label" => "CSS Class and mail template postfix",
			"hint_message" => "If you don't know what this is, just leave it empty"
		));

		// Description
		$this->addToModel("description", array(
			"type" => "text",
			"label" => "Short description",
			"hint_message" => "Write a short description of the membership for SEO.",
			"error_message" => "A short description without any words? How weird."
		));

		// HTML
		$this->addToModel("html", array(
			"type" => "html",
			"label" => "Full description",
			"hint_message" => "Write a full description of the membership.",
			"error_message" => "A full description without any words? How weird."
		));


		// // Interval
		// $this->addToModel("renewal", array(
		// 	"type" => "string",
		// 	"label" => "Renewal",
		// 	"required" => true,
		// 	"hint_message" => "Use Cron syntax for renewal interval description.",
		// 	"error_message" => "Invalid renewal interval."
		// ));


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


	// handle shipment of item
	function shipped($item_id, $order) {

		print $item_id;
		print_r($order);
		// should send welcome email 
		print "oh I'm being shipped.";

	}

}

?>