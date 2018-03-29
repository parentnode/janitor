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
			"label" => "CSS Class",
			"hint_message" => "CSS class for custom styling. If you don't know what this is, just leave it empty"
		));

		// subscribed_message
		$this->addToModel("subscribed_message_id", array(
			"type" => "integer",
			"label" => "Welcome message",
			"hint_message" => "Select a message to send to users when they subscribe to this membership"
		));

		// Description
		$this->addToModel("description", array(
			"type" => "text",
			"label" => "SEO description",
			"hint_message" => "Write a short description of the membership for SEO.",
			"error_message" => "A short description without any words? How weird."
		));

		// HTML
		$this->addToModel("introduction", array(
			"type" => "html",
			"label" => "Introduction for overview",
			"allowed_tags" => "p,h2,h3,h4,ul",
			"hint_message" => "Write a short introduction of the membership.",
			"error_message" => "A short introduction without any words? How weird."
		));

		// HTML
		$this->addToModel("html", array(
			"type" => "html",
			"label" => "Full description",
			"hint_message" => "Write a full description of the membership.",
			"error_message" => "A full description without any words? How weird."
		));

	}

	function shipped($order_item_id, $order) {

		// print "\n<br>###$order_item_id### shipped\n<br>";

	}

	// user subscribed to an item
	function subscribed($subscription) {
//		print_r($subscription);

		// check for subscription error
		if($subscription && $subscription["item_id"] && $subscription["user_id"] && $subscription["order"]) {

			$item_id = $subscription["item_id"];
			$user_id = $subscription["user_id"];
			$order = $subscription["order"];
			$item_key = arrayKeyValue($order["items"], "item_id", $item_id);
			$order_item = $order["items"][$item_key];

			$message_id = $subscription["item"]["subscribed_message_id"];

			// variables for email
			$price = formatPrice(array("price" => $order_item["total_price"], "vat" => $order_item["total_vat"],  $order_item["total_price"], "country" => $order["country"], "currency" => $order["currency"]));


			$IC = new Items();
			$model = $IC->typeObject("message");

			$model->sendMessage([
				"item_id" => $message_id, 
				"user_id" => $user_id, 
				"values" => ["PRICE" => $price]
			]);

			global $page;
			$page->addLog("membership->subscribed: item_id:$item_id, user_id:$user_id, order_id:".$order["id"]);


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