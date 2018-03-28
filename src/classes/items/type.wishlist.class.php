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
		$this->db_wishes_order = SITE_DB.".item_wishlist_wishes_order";

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
			"hint_message" => "CSS class for custom styling. If you don't know what this is, just leave it empty"
		));

	}


	// CMS SECTION
	// custom loopback function


	// Update item order
	// /janitor/admin/wishlist/updateOrder (order comma-separated in POST)
	function updateWishOrder($action) {

		$order_list = getPost("order");
		if(count($action) == 2 && $order_list) {

			$wishlist_id = $action[1];

			$query = new Query();
			// make sure type tables exist
			$query->checkDbExistence($this->db_wishes_order);

			$order = explode(",", $order_list);
			$sql = "DELETE FROM ".$this->db_wishes_order." WHERE item_id = ".$wishlist_id;
			$query->sql($sql);


			for($i = 0; $i < count($order); $i++) {
				$wish_id = $order[$i];
				$sql = "INSERT INTO ".$this->db_wishes_order." SET position = ".($i+1).", item_id = ".$wishlist_id.", wish_id = ".$wish_id;
				$query->sql($sql);
			}

			message()->addMessage("Wish order updated");
			return true;
		}

		message()->addMessage("Wish order could not be updated - please refresh your browser", array("type" => "error"));
		return false;

	}

	// internal helper functions

	// delete wishlist tag, when wishlist is deleted
	function preDelete($item_id) {

		$IC = new Items();

		$item = $IC->getItem(array("id" => $item_id, "extend" => array("tags" => true)));
		$tag_index = arrayKeyValue($item["tags"], "context", "wishlist");
		if($tag_index !== false) {
			// delete wishlist tag, when wishlist is deleted
			$TC = new Tag();
			$TC->deleteTag(array("deleteTag", $item["tags"][$tag_index]["id"]));
		}

		return true;
	}

	// get wishlist tag
	function getWishlistTag($item_id) {

		// TODO: maybe better to use getTags here?
		$IC = new Items();
		$item = $IC->getItem(array("id" => $item_id, "extend" => array("tags" => true)));

		$tag_index = arrayKeyValue($item["tags"], "context", "wishlist");
		if($tag_index !== false) {
			$tag = "wishlist:".addslashes($item["tags"][$tag_index]["value"]);
		}
		// create tag if wishlist doesnt have tag already
		else {
			$tag = "wishlist:".$item["name"];
			$_POST["tags"] = $tag;
			$this->addTag(array("addTag", $item["id"]));
		}
		return $tag;
	}

	// get correctly ordered wishes for this wishlist
	function getOrderedWishes($item_id, $_options = false) {

		$status = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "status"           : $status            = $_value; break;
				}
			}
		}

		// get wishlist tag
		$tag = $this->getWishlistTag($item_id);

		$query = new Query();
		$IC = new Items();

		// get all wishes (new elements could be added without being ordered yet)
		$query_options = array("itemtype" => "wish", "tags" => $tag, "extend" => array("tags" => true, "mediae" => true));
		if($status !== false) {
			$query_options["status"] = $status;
		}
		$wishlist_wishes = $IC->getItems($query_options);

//		print_r($wishlist_wishes);

		// get wish order
		$sql = "SELECT * FROM ".$this->db_wishes_order." WHERE item_id = ".$item_id." ORDER BY position";
		$query->sql($sql);
		$ordered_wishes = $query->results();

//		print_r($ordered_wishes);

		foreach($ordered_wishes as $wish_index => $ordered_wish) {
			// ordered wish position in all wishes?
			$position = arrayKeyValue($wishlist_wishes, "id", $ordered_wish["wish_id"]);
			// it is there, so we remove it from the all (and unordered stack)
			if($position !== false) {
				// copy full item to order stack in correct position
				// only allow enabled items

				if($status !== false) {
					// status matches
					if($wishlist_wishes[$position]["status"] == $status) {
						$ordered_wishes[$wish_index] = $wishlist_wishes[$position];
					}
					// remove from ordered stack
					else {
						unset($ordered_wishes[$wish_index]);
					}
				}
				// no status specified
				else {
					$ordered_wishes[$wish_index] = $wishlist_wishes[$position];
				}

				// remove it from full stack
				unset($wishlist_wishes[$position]);
			}
			// it is not there, so it must have been removed
			else {
				unset($ordered_wishes[$wish_index]);
				// remove from ordered list now
				$sql = "DELETE FROM ".$this->db_wishes_order." WHERE item_id = ".$item_id." AND wish_id = ".$ordered_wish["wish_id"];
				$query->sql($sql);
			}
		}

		// add unordered wishes to ordered list
		foreach($wishlist_wishes as $wishlist_wish) {
			$ordered_wishes[] = $wishlist_wish;
		}

		// return all wishes, with the ordered once first
		return $ordered_wishes;

	}
}

?>