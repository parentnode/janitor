<?php
/**
* @package janitor.subscription
* This file contains simple subscription functionality
*
* The Subscription class is supposed to be a minimal interface to Subscription maintenance and the subscription tables
* It is vital that this class does not expose anything but the current user's information
*
* For extended Subscription manipulator, see SuperSubscription.
*
* Only for NON-Admin creation of subscriptions, like
* - maillist signup
* - subscription to products bought by current user
*
*
*/

class SubscriptionCore extends Model {


	public $db_subscriptions;


	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		
		$this->db_subscriptions = SITE_DB.".user_item_subscriptions";

		// order id
		$this->addToModel("order_id", array(
			"type" => "integer",
			"label" => "Order",
			"hint_message" => "Order ID",
			"error_message" => "Invalid order"
		));
		// subscription_id
		$this->addToModel("subscription_id", array(
			"type" => "string",
			"label" => "Membership",
			"required" => true,
			"hint_message" => "Please select a membership",
			"error_message" => "Please select a membership"
		));
		// // payment_method
		// $this->addToModel("payment_method", array(
		// 	"type" => "string",
		// 	"label" => "Payment method",
		// 	"required" => true,
		// 	"hint_message" => "Please select a payment method",
		// 	"error_message" => "Please select a payment method"
		// ));
		// Upgrade subscription switch
		$this->addToModel("subscription_upgrade", array(
			"type" => "boolean",
			"required" => true
		));
		// expiration date
		$this->addToModel("expires_at", array(
			"type" => "datetime",
			"label" => "Expiration date (yyyy-mm-dd hh:mm)",
			"hint_message" => "Expiration date of the item.", 
			"error_message" => "Datetime must be of format (yyyy-mm-dd hh:mm)"
		));
		// custom price
		$this->addToModel("custom_price", array(
			"type" => "string",
			"label" => "Custom price (overrides default item price)",
			"pattern" => "^(\d+)(\.|,)?(\d+)?$",
			"class" => "custom_price",
			"hint_message" => "State the custom price INCLUDING VAT.",
			"error_message" => "Invalid price"
		));
		
		parent::__construct(get_class());

	}

	/**
	 * Get subscriptions for current user
	 * 
	 * Passing no parameters in $_options will return all the current user's subscriptions
	 *
	 * @param array|false $_options
	 * - item_id – get specific subscription for current user (can be used to check if user has subscription or not)
	 * - subscription_id – get subscription by subscription_id
	 * 
	 * @return array|false One or several subscription objects. False on error.
	 */
	function getSubscriptions($_options = false) {


		// get current user
		$user_id = session()->value("user_id");
		$item_id = false;
		$subscription_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "item_id"             : $item_id               = $_value; break;
					case "subscription_id"     : $subscription_id       = $_value; break;
				}
			}
		}

		$query = new Query();
		$IC = new Items();
		$SC = new Shop();

		// check for specific subscription for current user
		if($item_id !== false) {
			$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE user_id = $user_id AND item_id = $item_id LIMIT 1";
			// debug([$sql]);
			if($query->sql($sql)) {
				$subscription = $query->result(0);
				$subscription["item"] = $IC->getItem(array("id" => $subscription["item_id"], "extend" => array("prices" => true, "subscription_method" => true)));
				$subscription["membership"] = $subscription["item"]["itemtype"] == "membership" ? true : false;

				// // extend payment method details
				// if($subscription["payment_method"]) {
				// 	$payment_method = $subscription["payment_method"];
				// 	$subscription["payment_method"] = $page->paymentMethods($payment_method);
				// }

				// payment status
				if($subscription["order_id"]) {
					$subscription["order"] = $SC->getOrders(array("order_id" => $subscription["order_id"]));
				}

				return $subscription;
			}
		}
		// get subscription by subscription id
		else if($subscription_id !== false) {
			$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE user_id = $user_id AND id = $subscription_id LIMIT 1";
			// debug([$sql]);
			if($query->sql($sql)) {
				$subscription = $query->result(0);
				$subscription["item"] = $IC->getItem(array("id" => $subscription["item_id"], "extend" => array("prices" => true, "subscription_method" => true)));
				$subscription["membership"] = $subscription["item"]["itemtype"] == "membership" ? true : false;

				// // extend payment method details
				// if($subscription["payment_method"]) {
				// 	$payment_method = $subscription["payment_method"];
				// 	$subscription["payment_method"] = $page->paymentMethods($payment_method);
				// }

				// payment status
				if($subscription["order_id"]) {
					$subscription["order"] = $SC->getOrders(array("order_id" => $subscription["order_id"]));
				}

				return $subscription;
			}
		}

		// get list of all subscriptions for current user
		else {
			$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE user_id = $user_id";
			// debug([$sql]);
			if($query->sql($sql)) {
				$subscriptions = $query->results();
				foreach($subscriptions as $i => $subscription) {
					$subscriptions[$i]["item"] = $IC->getItem(array("id" => $subscription["item_id"], "extend" => array("prices" => true, "subscription_method" => true)));
					$subscriptions[$i]["membership"] = $subscriptions[$i]["item"]["itemtype"] == "membership" ? true : false;

					// // extend payment method details
					// if($subscription["payment_method"]) {
					// 	$payment_method = $subscription["payment_method"];
					// 	$subscriptions[$i]["payment_method"] = $page->paymentMethods($payment_method);
					// }

					// payment status
					if($subscription["order_id"]) {
						$subscriptions[$i]["order"] = $SC->getOrders(array("order_id" => $subscription["order_id"]));
					}
				}
				return $subscriptions;
			}
		}

		return false;
	}


	/**
	 * Add subscription to item for current user
	 * 
	 * will only add paid subscription if order_id is passed
	 * will not add subscription if subscription already exists, but returns existing subscription instead
	 * 
	 * @param array $action
	 * /#controller#/addSubscription
	 * required in $_POST: item_id 
	 * optional in $_POST: order_id
	 * 
	 * @return array|false Subscription object. False on error.
	 */
	function addSubscription($action) {

		// get current user
		$user_id = session()->value("user_id");

		// get posted values to make them available for models
		$this->getPostedEntities();

		// values are valid
		if(count($action) == 1 && $this->validateList(array("item_id"))) {

			$query = new Query();
			$IC = new Items();
			$SC = new Shop; 
			// $MC = new Member();
	
			$item_id = $this->getProperty("item_id", "value");
			$order_id = $this->getProperty("order_id", "value");
			// $payment_method = $this->getProperty("payment_method", "value");
			$custom_price = $this->getProperty("custom_price", "value");


			// safety valve
			// check if subscription already exists (somehow something went wrong)
			$subscription = $this->getSubscriptions(array("item_id" => $item_id));
			if($subscription) {

				// forward request to update method
				return $this->updateSubscription(["updateSubscription", $subscription["id"]]);
			}


			// get item prices and subscription method details to create subscription correctly
			$item = $IC->getItem(array("id" => $item_id, "extend" => array("subscription_method" => true, "prices" => true)));
			if($item && $item["subscription_method"]) {

				// order flag
				$order = false;
	
				if(SITE_SHOP) {

					// item has price
					if($item["prices"]) {

						// no order_id? - don't do anything else
						if(!$order_id) {
							return false;
						}
		
						$SC = new Shop();
						// check if order_id is valid
						$order = $SC->getOrders(array("order_id" => $order_id));
						if(!$order) {
							return false;
						}
		
					}
					
					// item has no price (is free)
					else {
	
						if($order_id) {

							// free items can't have orders
							return false;
						}
		
					}
				}
	
	
				// does subscription expire
				$expires_at = false; 
	
				if($item["subscription_method"] && $item["subscription_method"]["duration"]) {
					$expires_at = $this->calculateSubscriptionExpiry($item["subscription_method"]["duration"]);
				}
	
	
				$sql = "INSERT INTO ".$this->db_subscriptions." SET user_id = $user_id, item_id = $item_id";
				if($order_id) {
					$sql .= ", order_id = $order_id";
				}
				// if($payment_method) {
				// 	$sql .= ", payment_method = $payment_method";
				// }
				if($expires_at) {
					$sql .= ", expires_at = '$expires_at'";
				}
				if($custom_price || $custom_price === "0") {
					$sql .= ", custom_price = $custom_price";
				}

				// debug(["sql", $sql]);
				if($query->sql($sql)) {
	
					// get created subscription
					$subscription = $this->getSubscriptions(array("item_id" => $item_id));
		
	
					// perform special action on subscribe
					// this must be done after membership has been updated with new subscription id
					$model = $IC->typeObject($item["itemtype"]);
					if(method_exists($model, "subscribed")) {
						$model->subscribed($subscription);
					}
	
	
					// add to log
					logger()->addLog("user->addSubscription: item_id:$item_id, user_id:$user_id");
	
	
					return $subscription;
				}
	
			}

		}

		return false;
	}

	/**
	 * Update subscription for current user
	 *
	 * @param array $action
	 * /#controller#/updateSubscription/#subscription_id#
	 * 
	 * optional parameters in $_POST: 
	 * – item_id (item must have a subscription_method. If passed without an order_id, it will create an orderless subscription)
	 * – expires_at
	 * — order_id
	 * – custom_price
	 * 
	 * @return array|false Subscription object. False on error.
	 */
	function updateSubscription($action) {
		
		// get current user
		$user_id = session()->value("user_id");

		// get posted values to make them available for models
		$this->getPostedEntities();

		// values are valid
		if(count($action) == 2) {

			$SC = new Shop();
			$query = new Query();
			$IC = new Items();
			$MC = new Member();

			$subscription_id = $action[1];
			$item_id = $this->getProperty("item_id", "value");
			$order_id = $this->getProperty("order_id", "value");
			// $payment_method = $this->getProperty("payment_method", "value");
			$expires_at = $this->getProperty("expires_at", "value");
			$custom_price = $this->getProperty("custom_price", "value");


			// get original subscription and user_id
			$subscription = $this->getSubscriptions(array("subscription_id" => $subscription_id));
			$user_id = $subscription ? $subscription["user_id"] : false;
			
			// get original item_id and use as fallback
			$org_item_id = $subscription ? $subscription["item_id"] : false;
			if(!$item_id) {
				$item_id = $org_item_id;
			}

			// get item prices and subscription method details to create subscription correctly
			$item = $IC->getItem(array("id" => $item_id, "extend" => array("subscription_method" => true, "prices" => true)));
			
			// item and user_id are valid
			if($user_id && $item && $item["subscription_method"] && $item["subscription_method"]["duration"]) {
				
				// order_id was passed but item_id was not
				if($order_id && $item_id == $org_item_id) {

					// item has no price
					// or item already has order_id
					if(!$item["prices"] || $subscription["order_id"]) {
						
						// a priceless item and an order cannot be combined in a subscription
						// cannot overwrite existing order
						return false;
					}

				}
				// item_id was passed but order_id was not
				elseif($item_id != $org_item_id && !$order_id) {

					// item has no price
					if(!$item["prices"]) {
						
						// empty order_id to delete existing order_id
						$order_id = 'NULL';
					}
					// item has a price
					else {

						// cannot update to paid subscription without order_id
						return false;
					}
					
				}
				
				// special handling of eternal subscriptions
				if($subscription["expires_at"] === NULL) {
					
					if($expires_at) {
					
						// cannot set expiration date for eternal subscription
						return false;
					}
				}

				// expiration date for new subscription is not directly specified and must be calculated
				if(!$expires_at) {
					
					// current subscription has an expiration date 
					if($subscription["expires_at"]) {
						
						$expires_at = $subscription["expires_at"];
					}
					// current subscription never expires
					else {
						
						// new subscription will expire at some point
						if($item["subscription_method"]["duration"] != "*") {
							
							// calculate new expiration date, counting from current time
							$expires_at = $this->calculateSubscriptionExpiry($item["subscription_method"]["duration"]);
						}
					}

				}
					
				$sql = "UPDATE ".$this->db_subscriptions." SET item_id = $item_id, modified_at=CURRENT_TIMESTAMP";
				if($order_id || $order_id === 'NULL') {
					$sql .= ", order_id = $order_id";
				}
				// if($payment_method) {
				// 	$sql .= ", payment_method = $payment_method";
				// }
				if($expires_at) {
					$sql .= ", expires_at = '$expires_at'";
				}
				else {
					$sql .= ", expires_at = NULL";
				}
				if($custom_price || $custom_price === "0") {
					$sql .= ", custom_price = $custom_price";
				}
				else {
					$sql .= ", custom_price = NULL";
				}
	
				$sql .= " WHERE user_id = $user_id AND id = $subscription_id";
	
				if($query->sql($sql)) {	
	
					// add to log
					logger()->addLog("SuperUser->updateSubscription: subscription_id:$subscription_id, item_id:$item_id, user_id:$user_id");
	
	
					// get new subscription
					$subscription = $this->getSubscriptions(array("subscription_id" => $subscription_id));
	
	
					// perform special action on subscribe to new item
					if($item_id != $org_item_id) {
						$model = $IC->typeObject($item["itemtype"]);
						if(method_exists($model, "subscribed")) {
							$model->subscribed($subscription);
						}
					}

					return $subscription;
	
				}
	
			}

		}

		return false;
	}

	/**
	 * Delete subscription for current user
	 *
	 * @param array $action
	 * /#controller#/deleteSubscription/#subscription_id#
	 * 
	 * @return boolean True on successful deletion. False on error.
	 */
	function deleteSubscription($action) {

		// does values validate
		if(count($action) == 2) {

			$user_id = session()->value("user_id");
			$subscription_id = $action[1];
		
			$query = new Query();
	
			// check membership dependency
			$sql = "SELECT id FROM ".SITE_DB.".user_members WHERE subscription_id = $subscription_id";
			if(!$query->sql($sql)) {
	
				// get item id from subscription, before deleting it
				$subscription = $this->getSubscriptions(array("subscription_id" => $subscription_id));
	
	
				// perform special action on unsubscribe
				// before removing subscription (because unsubscribe uses it as information source)
				$IC = new Items();
				$unsubscribed_item = $subscription ? $IC->getItem(array("id" => $subscription["item_id"])) : false;
				if($unsubscribed_item) {
					$model = $IC->typeObject($unsubscribed_item["itemtype"]);
					if(method_exists($model, "unsubscribed")) {
						$model->unsubscribed($subscription);
					}
				}
	
	
				$sql = "DELETE FROM ".$this->db_subscriptions." WHERE id = $subscription_id AND user_id = $user_id";
				if($query->sql($sql)) {
	
					logger()->addLog("user->deleteSubscription: $subscription_id ($user_id)");
	
					return true;
				}
			}
		}

		return false;
	}


	// calculate expiry date for subscription
	// TODO: enable more flexible duration "settings"
	/**
	 * Calculate expiry date for subscription
	 * 
	 * @todo enable more flexible duration "settings"
	 *
	 * @param string $duration – "annually", "monthly", or "weekly"
	 * @param string|false $start_time
	 * @return string|false Formatted timestamp. False on error.
	 */
	function calculateSubscriptionExpiry($duration, $start_time = false) {
//		print "calculateSubscriptionExpiry:" . $duration;

		$expires_at = false;

		if($start_time) {
			$timestamp = strtotime($start_time);
		}
		else {
			$timestamp = time();
		}


		// annually
		if($duration == "annually") {

			// check whether it's February 29th in a leap year
			if(date("L", $timestamp) && date("m", $timestamp) == 02 && date("d", $timestamp) == 29) {
				
				// expire on February 28th next year
				$expires_at = date("Y-m-d 00:00:00", mktime(0, 0, 0, date("n", $timestamp), date("j", $timestamp)-1, date("Y", $timestamp)+1));
			}
			else {
				
				// expire same date next year
				$expires_at = date("Y-m-d 00:00:00", mktime(0, 0, 0, date("n", $timestamp), date("j", $timestamp), date("Y", $timestamp)+1));
			}

		}

		// bi-annually
		else if($duration == "biannually") {

			$days_of_month = date("t", $timestamp);
			$date_of_month = date("j", $timestamp);

			$days_of_next_month = date("t", mktime(0, 0, 0, date("n", $timestamp)+6, 1, date("Y", $timestamp)));
			
			// if current date doesn't exist 6 months from now (fx. 30 or 31/01)
			// if current date is last date in month 
			// - choose last day of month 6 month from now
			if($date_of_month > $days_of_next_month || $date_of_month == $days_of_month) {

				$expires_at = date("Y-m-d 00:00:00", mktime(0, 0, 0, date("n", $timestamp)+6, $days_of_next_month, date("Y", $timestamp)));
			}
			// just use same date next month
			else {

				$expires_at = date("Y-m-d 00:00:00", mktime(0, 0, 0, date("n", $timestamp)+6, date("j", $timestamp), date("Y", $timestamp)));
			}

		}

		// quaterly
		else if($duration == "quarterly") {

			$days_of_month = date("t", $timestamp);
			$date_of_month = date("j", $timestamp);

			$days_of_next_month = date("t", mktime(0, 0, 0, date("n", $timestamp)+3, 1, date("Y", $timestamp)));
			
			// if current date doesn't exist 3 months from now (fx. 30 or 31/01)
			// if current date is last date in month 
			// - choose last day of month 3 months ahead
			if($date_of_month > $days_of_next_month || $date_of_month == $days_of_month) {

				$expires_at = date("Y-m-d 00:00:00", mktime(0, 0, 0, date("n", $timestamp)+3, $days_of_next_month, date("Y", $timestamp)));
			}
			// just use same date next month
			else {

				$expires_at = date("Y-m-d 00:00:00", mktime(0, 0, 0, date("n", $timestamp)+3, date("j", $timestamp), date("Y", $timestamp)));
			}

		}

		// monthly
		else if($duration == "monthly") {

			$days_of_month = date("t", $timestamp);
			$date_of_month = date("j", $timestamp);

			$days_of_next_month = date("t", mktime(0, 0, 0, date("n", $timestamp)+1, 1, date("Y", $timestamp)));
			
			// if current date doesn't exist in next month (fx. 30 or 31/01)
			// if current date is last date in month 
			// - choose last day of next month
			if($date_of_month > $days_of_next_month || $date_of_month == $days_of_month) {

				$expires_at = date("Y-m-d 00:00:00", mktime(0, 0, 0, date("n", $timestamp)+1, $days_of_next_month, date("Y", $timestamp)));
			}
			// just use same date next month
			else {

				$expires_at = date("Y-m-d 00:00:00", mktime(0, 0, 0, date("n", $timestamp)+1, date("j", $timestamp), date("Y", $timestamp)));
			}

		}

		// weekly
		else if($duration == "weekly") {

			$expires_at = date("Y-m-d 00:00:00", $timestamp + (7*24*60*60));
		}

		return $expires_at;
	}





}

?>