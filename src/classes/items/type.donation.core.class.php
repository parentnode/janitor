<?php
/**
* @package janitor.itemtypes
* This file contains itemtype functionality
*/

class TypeDonationCore extends Itemtype {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct($itemtype) {

		// construct ItemType before adding to model
		parent::__construct($itemtype);


		// itemtype database
		$this->db = SITE_DB.".item_donation";


		// Name
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Donation name",
			"required" => true,
			"hint_message" => "Name of the donation.", 
			"error_message" => "Name must be filled out."
		));

		// Class
		$this->addToModel("classname", array(
			"type" => "string",
			"label" => "CSS Class for list.",
			"hint_message" => "CSS class for custom styling. If you don't know what this is, just leave it empty."
		));

		// description
		$this->addToModel("description", array(
			"type" => "text",
			"label" => "Short SEO description",
			"max" => 155,
			"hint_message" => "Write a short description of the donation for SEO and listings.",
			"error_message" => "Your donation needs a description – max 155 characters."
		));

		// HTML
		$this->addToModel("html", array(
			"type" => "html",
			"label" => "Full description",
			"required" => true,
			"allowed_tags" => "p,h2,h3,h4,ul,ol,download,jpg,png",
			"hint_message" => "Write the full description of what the donation is for.",
			"error_message" => "A donation description without any words? How weird."
		));

		// Single media
		$this->addToModel("single_media", array(
			"type" => "files",
			"label" => "Add media here",
			"max" => 1,
			"allowed_formats" => "png,jpg",
			"hint_message" => "Add single image by dragging it here. PNG or JPG allowed.",
			"error_message" => "Media does not fit requirements."
		));

		// ordered_message_id
		$this->addToModel("ordered_message_id", [
			"type" => "integer",
			"label" => "Donation message",
			"required" => true,
			"hint_message" => "Select a message to send to users along with donation, when they order this donation.",
			"error_message" => "You must choose a donation email to be sent when donation is ordered."
		]);

	}


	function ordered($order_item, $order) {

		// check for subscription error
		if($order && $order["user_id"] && $order_item && $order_item["item_id"]) {

			$item_id = $order_item["item_id"];
			$user_id = $order["user_id"];



			$IC = new Items();
			$model = $IC->typeObject("message");

			$item = $IC->getItem(["id" => $item_id, "extend" => true]);
			$message_id = $item["ordered_message_id"];


			// Send thank you mail
			$model->sendMessage([
				"item_id" => $message_id, 
				"user_id" => $user_id, 
				"values" => ["DONATION" => formatPrice(["price" => $order_item["total_price"], "currency" => $order["currency"]])]
			]);


			logger()->addLog("donation->ordered: item_id:$item_id, user_id:$user_id, order_id:".$order["id"].", order_item_id:".$order_item["id"]);

		}

	}

}

?>