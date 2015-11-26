<?php
/**
* @package janitor.items
* This file contains item type functionality
*/

class TypeWishlist extends Itemtype {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		parent::__construct(get_class());


		// itemtype database
		$this->db = SITE_DB.".item_wishlist";

		// Name
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Name",
			"required" => true,
			"unique" => $this->db,
			"hint_message" => "Name of the wishlist", 
			"error_message" => "Wishlist name must be unique"
		));

		// Class
		$this->addToModel("classname", array(
			"type" => "string",
			"label" => "CSS Class for wishlist",
			"hint_message" => "If you don't know what this is, just leave it empty"
		));

		// // Tags
		// $this->addToModel("tags", array(
		// 	"type" => "tags",
		// 	"label" => "Tag",
		// 	"hint_message" => "Start typing to filter available tags. A correct tag has this format: context:value.",
		// 	"error_message" => "Tag must conform to tag format: context:value."
		// ));
	}


	// CMS SECTION
	// custom loopback function


	// // Update item order
	// // /janitor/wishlist/updateOrder (order comma-separated in POST)
	// function updateOrder($action) {
	//
	// 	$order_list = getPost("order");
	// 	if(count($action) == 1 && $order_list) {
	//
	// 		$query = new Query();
	// 		$order = explode(",", $order_list);
	//
	// 		for($i = 0; $i < count($order); $i++) {
	// 			$item_id = $order[$i];
	// 			$sql = "UPDATE ".$this->db." SET position = ".($i+1)." WHERE item_id = ".$item_id;
	// 			$query->sql($sql);
	// 		}
	//
	// 		message()->addMessage("Wishlist order updated");
	// 		return true;
	// 	}
	//
	// 	message()->addMessage("Wishlist order could not be updated - please refresh your browser", array("type" => "error"));
	// 	return false;
	//
	// }

}

?>