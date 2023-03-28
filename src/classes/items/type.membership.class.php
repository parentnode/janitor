<?php
/**
* @package janitor.items
* This file contains item type functionality
*/

class TypeMembership extends Itemtype {


	public $db;


	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		parent::__construct(get_class());


		// itemtype database
		$this->db = SITE_DB.".item_membership";


		// Name
		$this->addToModel("name", [
			"type" => "string",
			"label" => "Name",
			"required" => true,
			"hint_message" => "Membership name", 
			"error_message" => "Membership needs a name."
		]);

		// Class
		$this->addToModel("classname", [
			"type" => "string",
			"label" => "CSS Class",
			"hint_message" => "CSS class for custom styling. If you don't know what this is, just leave it empty"
		]);

		// subscribed_message
		$this->addToModel("subscribed_message_id", [
			"type" => "integer",
			"label" => "Welcome message",
			"required" => true,
			"hint_message" => "Select a message to send to users when they subscribe to this membership"
		]);

		// Description
		$this->addToModel("description", [
			"type" => "text",
			"label" => "Short SEO description",
			"max" => 155,
			"hint_message" => "Write a short description of the membership for SEO.",
			"error_message" => "Your membership needs a description – max 155 characters."
		]);

		// HTML
		$this->addToModel("introduction", [
			"type" => "html",
			"label" => "Introduction for overview",
			"allowed_tags" => "p,h2,h3,h4,ul",
			"hint_message" => "Write a short introduction of the membership.",
			"error_message" => "A short introduction without any words? How weird."
		]);

		// HTML
		$this->addToModel("html", [
			"type" => "html",
			"label" => "Full description",
			"allowed_tags" => "p,h2,h3,h4,ul,ol,code,download,jpg,png", //,mp4,vimeo,youtube",
			"hint_message" => "Write a full description of the membership.",
			"error_message" => "A full description without any words? How weird."
		]);

		// Single media
		$this->addToModel("single_media", array(
			"type" => "files",
			"label" => "Add media here",
			"allowed_sizes" => "960x540",
			"max" => 1,
			"allowed_formats" => "png,jpg",
			"hint_message" => "Add single image by dragging it here. PNG or JPG allowed in 960x540",
			"error_message" => "Media does not fit requirements."
		));

	}

	function saved($item_id) {
		$query = new Query();
		$IC = new Items();

		$item = $IC->getItem(["id" => $item_id, "extend" => true]);
		
		// insert price type for membership
		$item_id = $item["id"];
		$item_name = $item["name"];
		$normalized_item_name = superNormalize(substr($item_name, 0, 60));
		$sql = "INSERT INTO ".UT_PRICE_TYPES." (item_id, name, description) VALUES ($item_id, '$normalized_item_name', 'Price for \\'$item_name\\' members')";
		$query->sql($sql);
	}
	
	function deleting($item_id) {
		$query = new Query();
		$IC = new Items();
		
		$item = $IC->getItem(["id" => $item_id, "extend" => true]);
		$item_id = $item["id"];
		
		$sql = "DELETE FROM ".UT_PRICE_TYPES." WHERE item_id = '$item_id'";
		if($query->sql($sql)) {
			 return true;
		}
		
		message()->addMessage("Can't delete. Could not delete associated price type.", ["type" => "error"]);
		return false;
	}
	
	function enabling($item) {

		if(!$item["subscription_method"]) {

			message()->addMessage("Can't enable. Membership items must have a subscription method.", ["type" => "error"]);
			return false;
		}
	}


	function addedToCart($added_item, $cart) {

		$added_item_id = $added_item["id"];
		// debug(["added to cart (membership)", $added_item_id]);### added to cart (membership)\n<br>";
		$SC = new Shop;
		$IC = new Items;
		$query = new Query;

		// get membership cart_item(s)
		$sql = "SELECT cart_items.* FROM ".SITE_DB.".shop_cart_items AS cart_items JOIN ".SITE_DB.".items AS items ON items.id = cart_items.item_id WHERE items.itemtype = 'membership' AND cart_items.cart_id = ".$cart["id"];

		if($query->sql($sql)) {
			$results = $query->results();

			// only 1 membership cart_item was found in cart
			if(count($results) === 1) {
				
				// membership cart_item has quantity above 1
				if($query->result(0, "quantity") > 1) {

					// set membership cart_item quantity to 1 
					$sql = "UPDATE ".SITE_DB.".shop_cart_items SET quantity = 1 WHERE item_id = ".$added_item["id"]." AND cart_id = ".$cart["id"];
					$query->sql($sql);
	
					message()->addMessage("Can't update quantity. A Membership can only have a quantity of 1.", ["type" => "error"]);
				}
			}
			// several membership cart_items were found in cart
			elseif(count($results) > 1) {

				// find most recently added cart_item (highest cart_item_id)
				$max_cart_item_id = max(array_column($results, "id"));

				// delete all membership cart_items except most recently added
				$sql = "DELETE ".SITE_DB.".shop_cart_items FROM ".SITE_DB.".shop_cart_items INNER JOIN ".SITE_DB.".items i ON i.id = ".SITE_DB.".shop_cart_items.item_id WHERE ".SITE_DB.".shop_cart_items.cart_id = ".$cart["id"]." AND i.itemtype = 'membership' AND ".SITE_DB.".shop_cart_items.id != ".$max_cart_item_id;
				if($query->sql($sql)) {
					logger()->addLog("membership->addedToCart: enforce single membership in cart - keep only cart_item_id:".$max_cart_item_id);
				}

				// set remaining membership cart_item quantity to 1 
				$sql = "UPDATE ".SITE_DB.".shop_cart_items SET quantity = 1 WHERE item_id = ".$added_item["id"]." AND cart_id = ".$cart["id"];
				$query->sql($sql);

			}
		}


		logger()->addLog("membership->addedToCart: added_item:".$added_item_id);

	}


	function shipped($order_item, $order) {

		$item_id = $order_item["item_id"];

		logger()->addLog("membership->shipped: order_id:".$order["id"]);

	}

	// user subscribed to a membership item
	function subscribed($subscription) {

		// check for subscription error
		if($subscription && $subscription["item_id"] && $subscription["user_id"]) {

			include_once("classes/users/supermember.class.php");
			$MC = new SuperMember();

			$item_id = $subscription["item_id"];
			$user_id = $subscription["user_id"];


			$existing_membership = $MC->getMembers(["user_id" => $user_id]);
			if($existing_membership) {
				$MC->updateMembership(["user_id" => $user_id, "subscription_id" => $subscription["id"]]);
			}
			else {
				// add membership
				$MC->addMembership($item_id, $subscription["id"], ["user_id" => $user_id]);
			}


			$IC = new Items();
			$model = $IC->typeObject("message");

			$message_id = $subscription["item"]["subscribed_message_id"];

			$model->sendMessage([
				"item_id" => $message_id, 
				"user_id" => $user_id, 
			]);

			logger()->addLog("membership->subscribed: item_id:$item_id, user_id:$user_id");

		}

	}

	function unsubscribed($subscription) {

		// check for subscription error
		if($subscription) {

			logger()->addLog("membership->unsubscribed: item_id:".$subscription["item_id"].", user_id:".$subscription["user_id"]);

		}

	}

}

?>