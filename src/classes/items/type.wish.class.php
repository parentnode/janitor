<?php
/**
* @package janitor.items
* This file contains item type functionality
*/

class TypeWish extends Itemtype {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		parent::__construct(get_class());


		// itemtype database
		$this->db = SITE_DB.".item_wish";

//		$this->wish_reserved = array(0 => "Available", 1 => "Reserved");


		// Name
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Name",
			"required" => true,
			"unique" => $this->db,
			"hint_message" => "Name your wish", 
			"error_message" => "Name must be unique."
		));

		// Price
		$this->addToModel("price", array(
			"type" => "integer",
			"label" => "Price starting at",
			"required" => true,
			"hint_message" => "State the lowest price observed", 
			"error_message" => "Price must be indicated"
		));

		// Reserved
		$this->addToModel("reserved", array(
			"type" => "string",
			"label" => "Reserved by",
			"hint_message" => "Is this item reserved by someone. Write their name here."
		));

		// Description
		$this->addToModel("description", array(
			"type" => "text",
			"label" => "Short SEO description",
			"max" => 155,
			"hint_message" => "Write a short description of the wish for SEO and listings.",
			"error_message" => "Your wish needs a description – max 155 characters."
		));

		// Link
		$this->addToModel("link", array(
			"type" => "string",
			"label" => "Link",
			"hint_message" => "Link to product"
		));

		// Mediae
		$this->addToModel("mediae", array(
			"type" => "files",
			"label" => "Add media here",
			"max" => 20,
			"allowed_formats" => "png,jpg",
			"hint_message" => "Add images or videos here. Use png or jpg.",
			"error_message" => "Media does not fit requirements."
		));

	}

	// internal helper functions

	// add wishlist tag after save (if wish was created from wishlist)
	function saved($item_id) {

		$return_to_wishlist = session()->value("return_to_wishlist");
		if($return_to_wishlist) {

			$IC = new Items();
			$wishlist_tag = $IC->getTags(array("item_id" => $return_to_wishlist, "context" => "wishlist"));
			if($wishlist_tag) {
				$_POST["tags"] = "wishlist:".$wishlist_tag[0]["value"];
				$this->addTag(array("addTag", $item_id));
			}
		}

		// enable item
		$this->status(array("status", $item_id, 1));
	}



	// used for frontend communication
	// reserve wish
	// /wishlist/reserve/#item_id#
	function reserve($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 2) {

			$reserved = $this->getProperty("reserved", "value");

			// "reserved by" was left blank
			$reserved = $reserved ? $reserved : 1;
			$query = new Query();
			$sql = "UPDATE ".$this->db." SET reserved = '$reserved' WHERE item_id = ".$action[1];
			if($query->sql($sql)) {
				return true;
			}

		}
		return false;
	}

	// un-reserve wish
	// /wishlist/unreserve/#item_id#
	function unreserve($action) {

		if(count($action) == 2) {

			$query = new Query();
			$sql = "UPDATE ".$this->db." SET reserved = '' WHERE item_id = ".$action[1];
			if($query->sql($sql)) {
				return true;
			}

		}
		return false;
	}

}

?>