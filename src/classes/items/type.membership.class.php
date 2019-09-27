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
			"label" => "SEO description",
			"hint_message" => "Write a short description of the membership for SEO.",
			"error_message" => "A short description without any words? How weird."
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
			"hint_message" => "Write a full description of the membership.",
			"error_message" => "A full description without any words? How weird."
		]);

	}

	function enabling($item) {

		if(!$item["subscription_method"]) {

			message()->addMessage("Can't enable. Membership items must have a subscription method.", ["type" => "error"]);
			return false;
		}
	}


	function addedToCart($added_item, $cart) {

		$added_item_id = $added_item["id"];
		// print "\n<br>###$added_item_id### added to cart (membership)\n<br>";
		$SC = new Shop;
		$IC = new Items;
		$query = new Query;

		foreach($cart["items"] as $cart_item) {
			
			$existing_item = $IC->getItem(["id" => $cart_item["item_id"]]);

			// another membership type already exists in cart
			if($existing_item["itemtype"] == "membership" && $existing_item["id"] != $added_item["id"]) {

				// keep the newest membership item
				$SC->deleteFromCart(["deleteFromCart", $cart["cart_reference"], $existing_item["id"]]);

			}
		}
		
		// ensure that membership item has quantity of 1
		$sql = "UPDATE ".SITE_DB.".shop_cart_items SET quantity = 1 WHERE id = ".$added_item["id"]." AND cart_id = ".$cart["id"];
		// print $sql;
		$query->sql($sql);



		global $page;
		$page->addLog("membership->addedToCart: added_item:".$added_item_id);

	}

	function ordered($order_item, $order) {

		include_once("classes/shop/supersubscription.class.php");
		include_once("classes/users/supermember.class.php");
		$SuperSubscriptionClass = new SuperSubscription();
		$MC = new SuperMember();
		
		$order_item_item_id = $order_item["item_id"];
		$order_id = $order["id"];
		$user_id = $order["user_id"];
		
		$existing_membership = $MC->getMembers(["user_id" => $user_id]);
		
		// user is already member (active or inactive)
		if($existing_membership) {

			// new membership item has a subscription method
			if(SITE_SUBSCRIPTIONS && $order_item["subscription_method"]) {
				
				// existing membership is active
				if($existing_membership["subscription_id"]) {
					
					// update subscription
					$subscription_id = $existing_membership["subscription_id"];
					$_POST["item_id"] = $order_item_item_id;
					$_POST["user_id"] = $user_id;
					$_POST["order_id"] = $order_id;
					$subscription = $SuperSubscriptionClass->updateSubscription(["updateSubscription", $subscription_id]);
					unset($_POST);
				}
				else {

					// add subscription
					$_POST["item_id"] = $order_item_item_id;
					$_POST["user_id"] = $user_id;
					$_POST["order_id"] = $order_id;
					$subscription = $SuperSubscriptionClass->addSubscription(["addSubscription"]);
					unset($_POST);
				}

				// update membership with subscription_id
				$subscription_id = $subscription["id"];
				$MC->updateMembership(["user_id" => $user_id, "subscription_id" => $subscription_id]);
			}
			
			// new membership item has no subscription method
			else {
				
				return false;
			}
			
		}
		
		// user is not yet a member
		else {

			// new membership has a subscription method
			if(SITE_SUBSCRIPTIONS && $order_item["subscription_method"]) {
				
				// add subscription
				$_POST["item_id"] = $order_item_item_id;
				$_POST["user_id"] = $user_id;
				$_POST["order_id"] = $order_id;
				$subscription = $SuperSubscriptionClass->addSubscription(["addSubscription"]);
				$subscription_id = $subscription["id"];
				unset($_POST);
	
				// add membership
				$MC->addMembership($order_item_item_id, $subscription_id, ["user_id" => $user_id]);
			}
			else {

				return false;
			}
		}
		
		global $page;
		$page->addLog("membership->ordered: order_id:".$order["id"]);
		// print "\n<br>###$order_item_item_id### ordered (membership)\n<br>";
	}

	function shipped($order_item, $order) {

		$order_item_item_id = $order_item["id"];		
		print "\n<br>###$order_item_item_id### shipped (membership)\n<br>";



		global $page;
		$page->addLog("membership->shipped: order_id:".$order["id"]);

	}

	// user subscribed to an item
	function subscribed($subscription) {

		// check for subscription error
		if($subscription && $subscription["item_id"] && $subscription["user_id"]) {

			$item_id = $subscription["item_id"];
			$user_id = $subscription["user_id"];
			$order_id = NULL;
			$price = NULL;
			
			if(isset($subscription["order"])) {
				$order = $subscription["order"];
				$item_key = arrayKeyValue($order["items"], "item_id", $item_id);
				$order_id = $order["id"];
				$order_item = $order["items"][$item_key];
				
				// variables for email
				$price = formatPrice(["price" => $order_item["total_price"], "vat" => $order_item["total_vat"],  $order_item["total_price"], "country" => $order["country"], "currency" => $order["currency"]]);
			}

			$message_id = $subscription["item"]["subscribed_message_id"];

			$IC = new Items();
			$model = $IC->typeObject("message");

			$model->sendMessage([
				"item_id" => $message_id, 
				"user_id" => $user_id, 
				"values" => ["PRICE" => $price]
			]);

			global $page;
			$page->addLog("membership->subscribed: item_id:$item_id, user_id:$user_id, order_id:".$order_id);


//
//
// 			$classname = $subscription["item"]["classname"];
//
//
// 			$UC = new User();
//
// 			// switch user id to enable user data collection
// 			$current_user_id = session()->value("user_id");
// 			session()->value("user_id", $user_id);
//
// 			// get user, order and  info
// 			$user = $UC->getUser();
//
// 			// switch back to correct user
// 			session()->value("user_id", $current_user_id);
//
//
// //			print "subscription:\n";
// //			print_r($subscription);
//
// 			// variables for email
// 			$nickname = $user["nickname"];
// 			$email = $user["email"];
// 			$membership = $user["membership"];
//
// 			// print "nickname:" . $nickname."<br>\n";
// 			// print "email:" . $email."<br>\n";
// 			// print "classname:" . $classname."<br>\n";
// 			// print "member no:" . $membership["id"]."<br>\n";
// 			// print "membership:" . $membership["item"]["name"]."<br>\n";
// 			// print "price:" . $price."\n";
//
//
// 			//$nickname = false;
// 			if($nickname && $email && $membership && $price && $classname) {
//
// 				mailer()->send(array(
// 					"values" => array(
// 						"ORDER_NO" => $order["order_no"],
// 						"MEMBER_ID" => $membership["id"],
// 						"MEMBERSHIP" => $membership["item"]["name"],
// 						"PRICE" => $price,
// 						"EMAIL" => $email,
// 						"NICKNAME" => $nickname
// 					),
// 					"recipients" => $email,
// 					"template" => "subscription_".$classname
// 				));
//
// 				// send notification email to admin
// 				mailer()->send(array(
// 					"recipients" => SHOP_ORDER_NOTIFIES,
// 					"subject" => SITE_URL . " - New ".$subscription["item"]["name"].": " . $email,
// 					"message" => "Do something"
// 				));
//
// 			}
// 			else {
//
// 				// send notification email to admin
// 				mailer()->send(array(
// 					"subject" => "ERROR: subscription creation: " . $email,
// 					"message" => "Do something",
// 					"template" => "system"
// 				));
//
// 			}

		}

	}

	function unsubscribed($subscription) {

		// check for subscription error
		if($subscription) {

			global $page;
			$page->addLog("membership->unsubscribed: item_id:".$subscription["item_id"].", user_id:".$subscription["user_id"]);

		}

	}

}

?>