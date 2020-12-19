<?php
/**
* @package janitor.shop
*/



/**
* Cart and Order helper class
*
* CART STATUS
* 1 = Active
* 2 = Associated with order
* 3 = Order has been paid, cart cannot be updated
*
* ORDER STATUS
* 1 = Active
* 2 = Order has been paid, cannot be updated
*/
class ShopCore extends Model {

	/**
	*
	*/
	function __construct() {

		// cart and order tables
		$this->db_carts = SITE_DB.".shop_carts";
		$this->db_cart_items = SITE_DB.".shop_cart_items";
		$this->db_orders = SITE_DB.".shop_orders";
		$this->db_order_items = SITE_DB.".shop_order_items";
		$this->db_payments = SITE_DB.".shop_payments";

		$this->db_cancelled_orders = SITE_DB.".shop_cancelled_orders";


		// TODO: needs to be updated throughout class
		// TODO: these are suggestions
		// TODO: update to 
		$this->order_statuses = array(0 => "Pending", 1 => "Waiting", 2 => "Complete", 3 => "Cancelled");


		// payment and shipping statuses
		$this->payment_statuses = array(0 => "Not paid", 1 => "Partially paid", 2 => "Paid");
		$this->shipping_statuses = array(0 => "Not shipped", 1 => "Partially shipped", 2 => "Shipped");
		

//		$this->cart_statuses = array(0 => "Open", 1 => "Associated with order");



		// email
		$this->addToModel("email", array(
			"type" => "email",
			"label" => "Your email",
			"required" => true,
			"hint_message" => "Write your email so we can contact you regarding your order.",
			"error_message" => "Invalid email."
		));
		// mobile
		$this->addToModel("mobile", array(
			"type" => "tel",
			"label" => "Your mobile",
			"required" => true,
			"hint_message" => "Write your mobile number so we can contact you regarding your order.",
			"error_message" => "Invalid mobile number."
		));




		$this->addToModel("country", array(
			"type" => "string",
			"label" => "Country",
			"required" => true,
			"hint_message" => "Country for order.", 
			"error_message" => "Country for order must be filled out."
		));

		$this->addToModel("currency", array(
			"type" => "string",
			"label" => "Currency",
			"required" => true,
			"hint_message" => "Currency for order.", 
			"error_message" => "Currency for order must be filled out."
		));

		$this->addToModel("quantity", array(
			"type" => "integer",
			"label" => "Quantity",
			"required" => true,
			"hint_message" => "Quantity of items.", 
			"error_message" => "Quantity must be a number."
		));

		// for custom order items
		$this->addToModel("item_name", array(
			"type" => "string",
			"label" => "Description",
			"required" => true,
			"hint_message" => "Description of this order item.", 
			"error_message" => "Description must be a string."
		));


		// custom values for cart items
		$this->addToModel("custom_name", array(
			"type" => "string",
			"label" => "Custom name",
			"required" => true,
			"hint_message" => "Custom name for cart item.", 
			"error_message" => "The name must be a string."
		));

		$this->addToModel("custom_price", array(
			"type" => "string",
			"label" => "Custom price (overrides default item price)",
			"pattern" => "^(\d+)(\.|,)?(\d+)?$",
			"class" => "custom_price",
			"hint_message" => "State the custom price INCLUDING VAT.",
			"error_message" => "Invalid price"
		));


		// Nickname
		$this->addToModel("billing_name", array(
			"type" => "string",
			"label" => "Full name",
			"required" => true,
			"hint_message" => "Write your full name for the invoice.", 
			"error_message" => "Name must be filled out."
		));


		// BILLING ADDRESS

		// billing address ID
		$this->addToModel("billing_address_id", array(
			"type" => "integer",
			"label" => "Billing address",
			"hint_message" => "Select billing address.",
			"error_message" => "Invalid billing address."
		));
		// att
		$this->addToModel("billing_att", array(
			"type" => "string",
			"label" => "Att",
			"hint_message" => "Att - contact person at destination."
		));
		// address 1
		$this->addToModel("billing_address1", array(
			"type" => "string",
			"label" => "Address",
			"required" => true,
			"hint_message" => "Address",
			"error_message" => "Invalid address."
		));
		// address 2
		$this->addToModel("billing_address2", array(
			"type" => "string",
			"label" => "Additional address",
			"hint_message" => "Additional address info.",
			"error_message" => "Invalid address."
		));
		// city
		$this->addToModel("billing_city", array(
			"type" => "string",
			"label" => "City",
			"required" => true,
			"hint_message" => "Write your city.",
			"error_message" => "Invalid city."
		));
		// postal code
		$this->addToModel("billing_postal", array(
			"type" => "string",
			"label" => "Postal code",
			"required" => true,
			"hint_message" => "Postalcode of your city.",
			"error_message" => "Invalid postal code."
		));
		// state
		$this->addToModel("billing_state", array(
			"type" => "string",
			"label" => "State/region",
			"hint_message" => "Write your state/region, if applicaple.",
			"error_message" => "Invalid state/region."
		));
		// country
		$this->addToModel("billing_country", array(
			"type" => "string",
			"label" => "Country",
			"required" => true,
			"hint_message" => "Country.",
			"error_message" => "Invalid country."
		));


		// DELIVERY ADDRESS
		// delivery address ID
		$this->addToModel("delivery_address_id", array(
			"type" => "integer",
			"label" => "Delivery address",
			"hint_message" => "Select delivery address",
			"error_message" => "Invalid delivery address"
		));
		// address name
		$this->addToModel("delivery_name", array(
			"type" => "string",
			"label" => "Name/Company",
			"required" => true,
			"hint_message" => "Name on door at address, your name or company name",
			"error_message" => "Invalid name"
		));
		// att
		$this->addToModel("delivery_att", array(
			"type" => "string",
			"label" => "Att",
			"hint_message" => "Att for delivery address",
			"error_message" => "Invalid att"
		));
		// address 1
		$this->addToModel("delivery_address1", array(
			"type" => "string",
			"label" => "Address",
			"required" => true,
			"hint_message" => "Address",
			"error_message" => "Invalid address"
		));
		// address 2
		$this->addToModel("delivery_address2", array(
			"type" => "string",
			"label" => "Additional address",
			"hint_message" => "Additional address info",
			"error_message" => "Invalid address"
		));
		// city
		$this->addToModel("delivery_city", array(
			"type" => "string",
			"label" => "City",
			"required" => true,
			"hint_message" => "Write your city",
			"error_message" => "Invalid city"
		));
		// postal code
		$this->addToModel("delivery_postal", array(
			"type" => "string",
			"label" => "Postal code",
			"required" => true,
			"hint_message" => "Postalcode of your city",
			"error_message" => "Invalid postal code"
		));
		// state
		$this->addToModel("delivery_state", array(
			"type" => "string",
			"label" => "State/region",
			"hint_message" => "Write your state/region, if applicaple",
			"error_message" => "Invalid state/region"
		));
		// country
		$this->addToModel("delivery_country", array(
			"type" => "string",
			"label" => "Country",
			"required" => true,
			"hint_message" => "Country",
			"error_message" => "Invalid country"
		));


		// order comment
		$this->addToModel("order_comment", array(
			"type" => "text",
			"label" => "Comment",
			"hint_message" => "Comments to your order",
			"error_message" => "Invalid comment"
		));


		// order id
		$this->addToModel("order_id", array(
			"type" => "integer",
			"label" => "Order",
			"required" => true,
			"hint_message" => "Select order to associate payment with.",
			"error_message" => "Invalid order."
		));
		// cart id
		$this->addToModel("cart_id", array(
			"type" => "integer",
			"label" => "Cart",
			"required" => true,
			"hint_message" => "Select cart.",
			"error_message" => "Invalid cart."
		));
		// order ids
		$this->addToModel("order_ids", array(
			"type" => "string",
			"label" => "Orders",
			"required" => true,
			"hint_message" => "Select orders to associate payment with.",
			"error_message" => "Invalid orders."
		));

		// transactions id
		$this->addToModel("transaction_id", array(
			"type" => "string",
			"label" => "Transasction id",
			"hint_message" => "Unique transaction id. This helps to identify the exact origin of the payment.",
			"error_message" => "Invalid id."
		));
		// payment amount
		$this->addToModel("payment_amount", array(
			"type" => "string",
			"label" => "Payment amount",
			"required" => true,
			"hint_message" => "Payment amount including tax. Use . (point) as decimal separator.",
			"error_message" => "Invalid amount."
		));
		// payment method id
		$this->addToModel("payment_method_id", array(
			"type" => "integer",
			"label" => "Payment method",
			"required" => true,
			"hint_message" => "Please select a payment method.",
			"error_message" => "Invalid payment method."
		));
		// user payment method id
		$this->addToModel("user_payment_method_id", array(
			"type" => "integer",
			"label" => "User payment method",
			"required" => true,
			"hint_message" => "Please select a payment method.",
			"error_message" => "Invalid payment method."
		));
		// payment intent id
		$this->addToModel("payment_intent_id", array(
			"type" => "string",
			"label" => "Payment intent",
			"required" => true,
			"hint_message" => "Please select a payment intent.",
			"error_message" => "Invalid payment intent."
		));


		// cardnumber
		$this->addToModel("card_number", array(
			"type" => "string",
			"label" => "Card number",
			"class" => "card",
			"required" => true,
			"hint_message" => "State your payment card number",
			"error_message" => "Invalid card number"
		));

		// card expiration month
		$this->addToModel("card_exp_month", array(
			"type" => "string",
			"label" => "MM",
			"class" => "exp_month",
			"required" => true,
			"hint_message" => "Expiration month for payment card",
			"error_message" => "Invalid month"
		));

		// card expiration year
		$this->addToModel("card_exp_year", array(
			"type" => "string",
			"label" => "YY",
			"class" => "exp_year",
			"required" => true,
			"hint_message" => "Expiration year for payment card",
			"error_message" => "Invalid year"
		));

		// card cvc code
		$this->addToModel("card_cvc", array(
			"type" => "string",
			"label" => "CVC",
			"class" => "cvc",
			"required" => true,
			"hint_message" => "CVC for payment card",
			"error_message" => "Invalid CVC"
		));


		// is_internal flag for cart
		$this->addToModel("is_internal", [
			"type" => "boolean",
		]);


		parent::__construct();
	}




	/**
	 * Get next available order number
	 * Retries recursively if insertion into db fails.
	 *
	 * @return string|false New order number with the format "WEBx" where x is an iterated number. False on error.
	 */
	function getNewOrderNumber() {

		$query = new Query();

		$sql = "SELECT order_no FROM ".$this->db_orders." ORDER BY id DESC LIMIT 1";
//		print $sql."<br />\n";

		if($query->sql($sql)) {
			$last_order_no = $query->result(0, "order_no");
			$order_no = "WEB".(intval(preg_replace("/WEB/", "", $last_order_no))+1);
		}
		else {
			$order_no = "WEB1";
		}

		$sql = "INSERT INTO ".$this->db_orders." SET order_no='$order_no'";
		// print $sql."<br />\n";

		if($query->sql($sql)) {
			return $order_no;
		}
		// insert failed - try again
		else {
			$order_no = $this->getNewOrderNumber();
		}

		return false;
	}

	// delete unused order number (if order creation fails)
	function deleteOrderNumber($order_no) {
		$query = new Query();

		$sql = "DELETE FROM ".$this->db_orders." WHERE order_no='$order_no' AND user_id IS NULL AND country IS NULL AND currency IS NULL";
		if($query->sql($sql)) {
			return true;
		}

		return false;
	}


	// get next available creditnote number
	function getNewCreditnoteNumber() {

		$query = new Query();
		$query->checkDbExistence($this->db_cancelled_orders);

		$sql = "SELECT creditnote_no FROM ".$this->db_cancelled_orders." ORDER BY id DESC LIMIT 1";
		//		print $sql."<br>\n";

		if($query->sql($sql)) {
			$last_creditnote_no = $query->result(0, "creditnote_no");
			$creditnote_no = "WCRE".(intval(preg_replace("/WCRE/", "", $last_creditnote_no))+1);
		}
		else {
			$creditnote_no = "WCRE1";
		}

		$sql = "INSERT INTO ".$this->db_cancelled_orders." SET creditnote_no='$creditnote_no'";
//		print $sql."<br>\n";
		if($query->sql($sql)) {
			return $creditnote_no;
		}
		// insert failed - try again
		else {
			$creditnote_no = $this->getNewCreditnoteNumber();
		}

		return false;
	}

	// delete unused creditnote number (if order cancellation fails)
	function deleteCreditnoteNumber($creditnote_no) {
		$query = new Query();

		$sql = "DELETE FROM ".$this->db_cancelled_orders." WHERE creditnote_no='$creditnote_no'";
		if($query->sql($sql)) {
			return true;
		}

		return false;
	}


	/**
	* Get total order price
	*
	* Calculate total order price by adding each order item + vat
	*
	* @return price object
	*/
	function getTotalOrderPrice($order_id) {
		$order = $this->getOrders(array("order_id" => $order_id));
		$total_price = 0;
		$total_vat = 0;

		if($order["items"]) {
			foreach($order["items"] as $item) {
				$total_price += $item["total_price"];
				$total_vat += $item["total_vat"];
			}
		}
		return array("price" => $total_price, "vat" => $total_vat, "currency" => $order["currency"], "country" => $order["country"]);
	}

	/**
	* Get remaining order price
	*
	* @return price object without VAT info
	*/
	function getRemainingOrderPrice($order_id) {
		$total_order_price = $this->getTotalOrderPrice($order_id);
		$total_order_amount = $total_order_price["price"];

		// Loop through all payments to get remaining payment amount
		$payments = $this->getPayments(["order_id" => $order_id]);
		$total_payments = 0;
		if($payments) {
			foreach($payments as $payment) {
				$total_payments += $payment["payment_amount"];
			}
		}

		$total_order_amount = $total_order_amount-$total_payments;

		return array("price" => $total_order_amount, "currency" => $total_order_price["currency"]);
	}

	/**
	* Get total price for cart
	*
	* @return price object
	*/
	function getTotalCartPrice($cart_id) {

		$cart = $this->getCarts(array("cart_id" => $cart_id));
		$total_price = 0;
		$total_vat = 0;

		if($cart) {

			if(isset($cart["items"]) && $cart["items"]) {
				foreach($cart["items"] as $cart_item) {
					// $price = $this->getPrice($cart_item["item_id"], array("quantity" => $cart_item["quantity"], "currency" => $cart["currency"], "country" => $cart["country"]));
					$price = $this->getCartItemPrice($cart_item, $cart);
					if($price) {
						$total_price += $price["cart_price"] * $cart_item["quantity"];
						$total_vat += $price["cart_vat"] * $cart_item["quantity"];
					}
				}
			}
			return array("price" => $total_price, "vat" => $total_vat, "currency" => $cart["currency"], "country" => $cart["country"]);
		}

		return false;

	}

	// Get item price for cart item – observing custom price in cart
	function getCartItemPrice($cart_item, $cart) {

		$price = $this->getPrice($cart_item["item_id"], array("quantity" => $cart_item["quantity"], "currency" => $cart["currency"], "country" => $cart["country"]));

		if($price) {

			if($cart_item["custom_price"]) {
				$price["cart_price"] = $cart_item["custom_price"];
			}
			else {
				$price["cart_price"] = $price["price"];
			}

			// $custom_price / (100 + $price["vatrate"]) * 100;
			$price["cart_price_without_vat"] = ($price["cart_price"] / (100 + $price["vatrate"]) * 100);
			$price["cart_vat"] = $price["cart_price"] - $price["cart_price_without_vat"];

		}

		return $price;

	}

	// get best available price for item
	function getPrice($item_id, $_options = false) {
		global $page;
		$IC = new Items();
		$MC = new Member();

		$user_id = session()->value("user_id");

		$quantity = false;
		$currency = false;

		// when different vat rates apply
		$country = false;


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "quantity"           : $quantity             = $_value; break;
					case "currency"           : $currency             = $_value; break;

					case "country"            : $country              = $_value; break;
				}
			}
		}

		if(!$currency) {
			$currency = $page->currency();
		}

		if(!$country) {
			$country = $page->country();
		}

		// get prices
		$prices = $IC->getPrices(array("item_id" => $item_id, "currency" => $currency, "country" => $country));

		if($prices) {

			$offer = arrayKeyValue($prices, "type", "offer");
			if($offer !== false) {
				$offer_price = $prices[arrayKeyValue($prices, "type", "offer")];
			}

			$default = arrayKeyValue($prices, "type", "default");
			if($default !== false) {

				$default_price = $prices[arrayKeyValue($prices, "type", "default")];
			}

			// use membership-specific price if applicable
			$membership = $MC->getMembership();
			if($membership && $membership["item"]) {
				$price_types = $page->price_types();
				$where_pricetype_matches_membership = arrayKeyValue($price_types, "item_id", $membership["item"]["item_id"]);
				$membership_price_type_id = $price_types[$where_pricetype_matches_membership]["id"];
				$where_price_matches_membership_price_type = arrayKeyValue($prices, "type_id", $membership_price_type_id);
				if($user_id != 1 && $membership["item"]["status"] == 1 && $where_price_matches_membership_price_type !== false) {
					$membership_price = $prices[$where_price_matches_membership_price_type];
				}
			}



			if($quantity && arrayKeyValue($prices, "type", "bulk") !== false) {
				$current_best_price = false;
				foreach($prices as $price) {
					if($price["type"] == "bulk" && $quantity >= $price["quantity"]) {
						$bulk_price = $price;
					}
				} 
			}

			if(isset($default_price)) {
				$return_price = $default_price;
			}

			if(isset($offer_price) && (!isset($return_price) || $return_price["price"] > $offer_price["price"])) {
				$return_price = $offer_price;
			}

			if(isset($bulk_price) && (!isset($return_price) || $return_price["price"] > $bulk_price["price"])) {
				$return_price = $bulk_price;
			}
			
			if(isset($membership_price) && (!isset($return_price) || $return_price["price"] > $membership_price["price"])) {
				$return_price = $membership_price;
			}
			

			if(isset($return_price)) {
				return $return_price;
			} 

		}

		return false;
	}



	// get cart for current user
	function getCart() {

		$user_id = session()->value("user_id");

		// look in session
		$cart_reference = session()->value("cart_reference");

		// no luck, then look in cookie
		if(!$cart_reference) {
			$cart_reference = isset($_COOKIE["cart_reference"]) ? $_COOKIE["cart_reference"] : false;
		}

		// debug(["cart ref", $cart_reference]);
		if($cart_reference) {

			$cart = $this->getCarts(array("cart_reference" => $cart_reference));
			if ($cart) {

				// This is the current session cart now
				session()->value("cart_reference", $cart_reference);
				// Update cookie for user
				setcookie("cart_reference", $cart_reference, time()+60*60*24*60, "/");

				// user is not logged in but cart has a user_id
				if($user_id == 1 && $cart["user_id"]) {
					$UC = new User();
					// check status of cart user
					$cart_user = $UC->getUserInfo(array("user_id" => $cart["user_id"]));
					// user has not been activated yet
					// - enable as temp user to allow user to complete checkout without login
					if($cart_user && !$cart_user["status"]) {
						session()->value("user_id", $cart["user_id"]);
					}
				}

				return $cart;
			}

		}
		// try to find a cart for user ( != 1)
		$carts = $this->getCarts();
		if($carts) {

			$cart = $carts[0];
			// This is the current session cart now
			session()->value("cart_reference", $cart["cart_reference"]);
			// Update cookie for user
			setcookie("cart_reference", $cart["cart_reference"], time()+60*60*24*60, "/");
			
			return $cart;
		}


		session()->reset("cart_reference");

		// Delete cart reference cookie
		setcookie("cart_reference", "", time() - 3600, "/");

		return false;
	}


	// get carts - default all carts
	// - optional cart with cart_id or cart_reference
	// - optional carts for user_id
	// - optional multiple carts, based on content match
	function getCarts($_options=false) {

		$user_id = session()->value("user_id");

		// get specific cart
		$cart_id = false;
		$cart_reference = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "cart_reference"  : $cart_reference    = $_value; break;
					case "cart_id"         : $cart_id           = $_value; break;
				}
			}
		}

		$query = new Query();


		// get specific cart
		if($cart_id !== false || $cart_reference !== false) {

			if($cart_id) {
				$sql = "SELECT * FROM ".$this->db_carts." WHERE id = $cart_id LIMIT 1";
			}
			else {
				$sql = "SELECT * FROM ".$this->db_carts." WHERE cart_reference = '$cart_reference' LIMIT 1";
			}

//			print $sql."<br>";
			if($query->sql($sql)) {
				$cart = $query->result(0);

				$cart["items"] = array();
				$cart["total_items"] = 0;

				$sql = "SELECT * FROM ".$this->db_cart_items." WHERE cart_id = ".$cart["id"];
				if($query->sql($sql)) {
					$cart["items"] = $query->results();

					// get total cart count
					$sql = "SELECT SUM(quantity) as total_items FROM ".$this->db_cart_items." WHERE cart_id = ".$cart["id"];
//					print $sql;

					if($query->sql($sql)) {
						$cart["total_items"] = $query->result(0, "total_items");
					}

				}

				return $cart;
			}

		}

		// get all carts
		else if($user_id != 1) {
			
			$sql = "SELECT * FROM ".$this->db_carts." WHERE user_id = $user_id ORDER BY id DESC";
//			print $sql."<br>";
			if($query->sql($sql)) {

				$carts = $query->results();

				foreach($carts as $i => $cart) {

					$carts[$i]["items"] = array();
					$carts[$i]["total_items"] = 0;
					$sql = "SELECT * FROM ".$this->db_cart_items." WHERE cart_id = ".$cart["id"];

//					print $sql;

					if($query->sql($sql)) {
						$carts[$i]["items"] = $query->results();

						// get total cart count
						$sql = "SELECT SUM(quantity) as total_items FROM ".$this->db_cart_items." WHERE cart_id = ".$cart["id"];
//						print $sql;

						if($query->sql($sql)) {
							$carts[$i]["total_items"] = $query->result(0, "total_items");
						}
					}

				}

				return $carts;
			}
		}

		return false;

	}


	/**
	 * Add a new cart with optional user, currency and country
	 * 
	 * /shop/addCart
	 * An optional is_internal flag can be passed via $_POST to avoid saving cart_reference in session and cookie
	 *
	 * @param array $action
	 * @return array|false Cart object. False on error.
	 */
	function addCart($action) {
		global $page;

		// get posted values to make them available for models
		$this->getPostedEntities();

		$user_id = session()->value("user_id");
		
		// values are valid
		if(count($action) == 1 && $user_id) {
			
			$query = new Query();
			
			$currency = $this->getProperty("currency", "value");
			$country = $this->getProperty("country", "value");
			
			$billing_address_id = $this->getProperty("billing_address_id", "value");
			$delivery_address_id = $this->getProperty("delivery_address_id", "value");
			$is_internal = $this->getProperty("is_internal", "value");


			// set user_id to default (NULL) if not passed
			if(!$user_id || $user_id == 1) {
				$user_id = "DEFAULT";
			}

			// set default currency if not passed
			if(!$currency) {
				$currency = $page->currency();
			}

			// set default country if not passed
			if(!$country) {
				$country = $page->country();
			}

			// update delivery address
			if(!$delivery_address_id) {
				$delivery_address_id = "DEFAULT";
			}

			// update billing address
			if(!$billing_address_id) {
				$billing_address_id = "DEFAULT";
			}

			// find valid cart_reference
			$cart_reference = randomKey(12);
			while($query->sql("SELECT id FROM ".$this->db_carts." WHERE cart_reference = '".$cart_reference."'")) {
				$cart_reference = randomKey(12);
			}

			// add cart
			$sql = "INSERT INTO ".$this->db_carts." VALUES(DEFAULT, $user_id, '$cart_reference', '$country', '$currency', $delivery_address_id, $billing_address_id, CURRENT_TIMESTAMP, DEFAULT)";
//			print $sql;
			if($query->sql($sql)) {

				// cart is internal
				if($is_internal) {
					
					return $this->getCarts(["cart_reference" => $cart_reference]);
				}

				// make sure cart reference is set for user
				session()->value("cart_reference", $cart_reference);

				// add cookie for user
				setcookie("cart_reference", $cart_reference, time()+60*60*24*60, "/");
				
				// return cart object
				return $this->getCart();
				
			}
		}

		return false;
	}

	// Update cart
	# /shop/updateCart
	function updateCart($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1) {

			$query = new Query();


			$cart = $this->getCart();

			if($cart) {

				$user_id = $this->getProperty("user_id", "value");
				$currency = $this->getProperty("currency", "value");
				$country = $this->getProperty("country", "value");

				$billing_address_id = $this->getProperty("billing_address_id", "value");
				$delivery_address_id = $this->getProperty("delivery_address_id", "value");


				// create base data update sql
				$sql = "UPDATE ".$this->db_carts." SET modified_at=CURRENT_TIMESTAMP";

				// update currency
				if($currency) {
					$sql .= ", currency='$currency'";
				}

				// update country
				if($country) {
					$sql .= ", country='$country'";
				}

				// update delivery address
				if($delivery_address_id) {
					$sql .= ", delivery_address_id='$delivery_address_id'";
				}

				// update billing address
				if($billing_address_id) {
					$sql .= ", billing_address_id='$billing_address_id'";
				}


				// update user_id
				if($user_id) {
					$sql .= ", user_id='$user_id'";
				}
				// Remove user from cart
				if($user_id === "0") {
					$sql .= ", user_id=NULL";
				}


				// finalize sql
				$sql .= " WHERE cart_reference='".$cart["cart_reference"]."'";

//				print $sql;
				if($query->sql($sql)) {

					return $this->getCart();
				}

			}
		}

		return false;

	}

	/**
	 * ### Add item to cart
	 * 
	 * /shop/addToCart
	 * 
	 * Values in $_POST
	 * - item_id (required)
	 * - quantity (required)
	 * - custom_price
	 * - custom_name
	 * 
	 * @param array $action
	 * @return array|false Cart object. False on error.
	 */
	function addToCart($action) {

		if(count($action) >= 1) {
			
			$user_id = session()->value("user_id");
			
			$cart = false;
			// get cart
			// check for cart_reference in session and cookie, or look for cart for current user ( != 1)
			$cart = $this->getCart();
			// get posted values to make them available for models
			$this->getPostedEntities();
			
			// cart exists
			if($cart) {
				$cart_reference = $cart["cart_reference"];
			}
			
			else {
				// add a new cart
				$cart = $this->addCart(array("addCart"));
				// print_r($cart);
				
				$cart_reference = $cart ? $cart["cart_reference"] : false;
			}
			
			// cart exists and values are valid
			if($cart && $this->validateList(array("quantity", "item_id"))) {

				$query = new Query();
				$IC = new Items();

				$custom_name = $this->getProperty("custom_name", "value");
				$custom_price = $this->getProperty("custom_price", "value");
				$quantity = $this->getProperty("quantity", "value");
				$item_id = $this->getProperty("item_id", "value");
				$item = $IC->getItem(array("id" => $item_id));
				$price = $this->getPrice($item_id);

				// item has a price (price can be zero)
				if ($price !== false) {

					// look in cart to see if the added item is already there
					// if added item already exists with a different custom_name or custom_price, create new line
					if ($custom_price !== false && $custom_name) {

						$existing_cart_item = $this->getCartItem($cart_reference, $item_id, ["custom_price" => $custom_price, "custom_name" => $custom_name]);
					}
					else if($custom_price !== false) {

						$existing_cart_item = $this->getCartItem($cart_reference, $item_id, ["custom_price" => $custom_price]);
					}
					else if($custom_name) {
						
						$existing_cart_item = $this->getCartItem($cart_reference, $item_id, ["custom_name" => $custom_name]);
					}
					else {
						
						$existing_cart_item = $this->getCartItem($cart_reference, $item_id);
					}

					// added item is already in cart
					if($existing_cart_item) {
						
						$existing_quantity = $existing_cart_item["quantity"];
						$new_quantity = intval($quantity) + intval($existing_quantity);
	
						// update item quantity
						$sql = "UPDATE ".$this->db_cart_items." SET quantity=$new_quantity WHERE id = ".$existing_cart_item["id"]." AND cart_id = ".$cart["id"];
	//					print $sql;
					}
					else {
						
						// insert new cart item
						$sql = "INSERT INTO ".$this->db_cart_items." SET cart_id=".$cart["id"].", item_id=$item_id, quantity=$quantity";

						if($custom_price !== false) {
							
							// use correct decimal seperator
							$custom_price = preg_replace("/,/", ".", $custom_price);

							$sql .= ", custom_price=$custom_price";
						}
						if($custom_name) {
							$sql .= ", custom_name='".$custom_name."'";
						}
						// print $sql;	
					}
	
					if($query->sql($sql)) {
						
						// update modified at time
						$sql = "UPDATE ".$this->db_carts." SET modified_at=CURRENT_TIMESTAMP WHERE id = ".$cart["id"];
						$query->sql($sql);
	
						$cart = $this->getCart();
	
						// add callback to addedToCart
						$model = $IC->typeObject($item["itemtype"]);
						if(method_exists($model, "addedToCart")) {
							$model->addedToCart($item, $cart);
						}
	
						return $cart;
	
					}
				}
				

			}
		}
		return false;
	}

	/**
	 * ### Add item to new internal cart
	 * 
	 *
	 * @param int $item_id
	 * @param int $_options
	 * – quantity (default is 1)
	 * – custom_name
	 * – custom_price
	 * 
	 * @return array|false Cart object. False on error.
	 */
	function addToNewInternalCart($item_id, $_options = false) {

		$quantity = 1;
		$custom_name = false;
		$custom_price = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "quantity"          : $quantity            = $_value; break;
					case "custom_name"       : $custom_name         = $_value; break;
					case "custom_price"      : $custom_price        = $_value; break;
				}
			}
		}
		
		$price = $this->getPrice($item_id);
		// item has a price (price can be zero)
		if($price !== false) {

			// use custom price if available
			if(isset($custom_price) && $custom_price !== false) {

				// use correct decimal seperator
				$custom_price = preg_replace("/,/", ".", $custom_price);

				$price["price"] = $custom_price;

				$custom_price_without_vat = $custom_price / (100 + $price["vatrate"]) * 100;
				$price["price_without_vat"] = $custom_price_without_vat;
				$price["vat"] = $custom_price - $custom_price_without_vat;
			}

			// create new internal cart
			$_POST["is_internal"] = true;
			$cart = $this->addCart(["addCart"]);
			unset($_POST);
			$cart_reference = $cart["cart_reference"];
			if($cart) {

				$query = new Query();
				$IC = new Items();
				$item = $IC->getItem(array("id" => $item_id));
				
				// insert new cart item
				$sql = "INSERT INTO ".$this->db_cart_items." SET cart_id=".$cart["id"].", item_id=$item_id, quantity=$quantity";
				
				if(isset($custom_price) && $custom_price !== false) {

					$sql .= ", custom_price=$custom_price";
				}
				if($custom_name) {
					$sql .= ", custom_name='".$custom_name."'";
				}
				
				if($query->sql($sql)) {

					// get updated cart
					$cart = $this->getCarts(["cart_reference" => $cart_reference]);
	
					// add callback to addedToCart
					$model = $IC->typeObject($item["itemtype"]);
					if(method_exists($model, "addedToCart")) {
						$model->addedToCart($item, $cart);
					}

					return $cart;
				}
			}
		}
		return false;
	}

	/**
	 * ### Get cart item from cart
	 *
	 * Passing no optional parameters will get cart_item without any custom values 
	 * 
	 * @param string $cart_reference
	 * @param integer $item_id
	 * @param array|false $_options – can be freely combined
	 * * custom_price (string) get item with the specified custom_price
	 * * custom_name (string) get item with the specified custom_name
	 * 
	 * @return array|false Cart item object. False if no match is found. False on error.
	 */
	function getCartItem($cart_reference, $item_id, $_options = false) {

		$cart = $this->getCarts(["cart_reference" => $cart_reference]);
		$custom_price = false;
		$standard_price = false;
		$custom_name = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "custom_price"            : $custom_price            = $_value; break;
					case "custom_name"             : $custom_name             = $_value; break;
				}
			}
		}

		foreach ($cart["items"] as $cart_item) {

			if($cart_item["item_id"] == $item_id) {

				if(isset($custom_price) && $custom_price !== false && $custom_name) {
					if(isset($cart_item["custom_price"]) && $cart_item["custom_price"] !== false && isset($cart_item["custom_name"]) && $cart_item["custom_price"] == $custom_price && $cart_item["custom_name"] == $custom_name) {
						return $cart_item;
					}
				}
				else if(isset($custom_price) && $custom_price !== false) {
					if(isset($cart_item["custom_price"]) && $cart_item["custom_price"] !== false && !isset($cart_item["custom_name"]) && $cart_item["custom_price"] == $custom_price) {
						return $cart_item;
					}
				}
				else if($custom_name) {
					if(isset($cart_item["custom_name"]) && !isset($cart_item["custom_price"]) && $cart_item["custom_name"] == $custom_name) {
						return $cart_item;
					}
				}
				else {
					if(!isset($cart_item["custom_price"]) && !isset($cart_item["custom_name"])) {
						return $cart_item;
					}
				}
				
			}
			
		}
		
		return false;

	}

	// Update quantity of item in cart
	// requires cart_reference to minimize "accidental" requests 
	# /shop/updateCartItemQuantity/#cart_reference#/#cart_item_id#
	// new quantity in $_POST
	function updateCartItemQuantity($action) {
		if(count($action) == 3) {

			$cart_reference = $action[1];
			$cart_item_id = $action[2];
			$cart = $this->getCarts(array("cart_reference" => $cart_reference));

			// Get posted values to make them available for models
			$this->getPostedEntities();

			// does values validate
			if($cart && $this->validateList(array("quantity"))) {

				$query = new Query();
				$IC = new Items();

				$quantity = $this->getProperty("quantity", "value");

				// find item_id in cart items?
				if($cart["items"] && arrayKeyValue($cart["items"], "id", $cart_item_id) !== false) {
					$existing_item_index = arrayKeyValue($cart["items"], "id", $cart_item_id);
					$item_id = $cart["items"][$existing_item_index]["item_id"];

					$sql = "UPDATE ".$this->db_cart_items." SET quantity=$quantity WHERE id = ".$cart_item_id." AND cart_id = ".$cart["id"];
//					print $sql;
					if($query->sql($sql)) {

						// update modified at time
						$sql = "UPDATE ".$this->db_carts." SET modified_at=CURRENT_TIMESTAMP WHERE id = ".$cart["id"];
						$query->sql($sql);

						$item = $IC->getItem(array("id" => $item_id, "extend" => true)); 
						$item["unit_price"] = $this->getPrice($item_id, array("quantity" => $quantity, "currency" => $cart["currency"], "country" => $cart["country"]));
						$item["unit_price_formatted"] = formatPrice($item["unit_price"]);
						$item["total_price"] = array(
							"price" => $item["unit_price"]["price"]*$quantity, 
							"vat" => $item["unit_price"]["vat"]*$quantity, 
							"currency" => $cart["currency"], 
							"country" => $cart["country"]
						);
						$item["total_price_formatted"] = formatPrice($item["total_price"], array("vat" => true));
						$item["total_cart_price"] = $this->getTotalCartPrice($cart["id"]);
						$item["total_cart_price_formatted"] = formatPrice($item["total_cart_price"]);

						// add callback to addedToCart
						$model = $IC->typeObject($item["itemtype"]);
						if(method_exists($model, "addedToCart")) {
							$model->addedToCart($item, $cart);
						}
 
						return $item;

					}

				}

			}

		}

		return false;
	}

	// Delete item from cart
	// requires cart_reference to minimize "accidental" requests 
	# /shop/deleteFromCart/#cart_reference#/#cart_item_id#
	function deleteFromCart($action) {

		if(count($action) > 2) {

			$cart_reference = $action[1];
			$cart_item_id = $action[2];
			$cart = $this->getCarts(array("cart_reference" => $cart_reference));

			if($cart && $cart["items"]) {
				
				// get item
				$IC = new Items();
				$item_id = $cart["items"][arrayKeyValue($cart["items"], "id", $cart_item_id)]["item_id"];
				$item = $IC->getItem(["id" => $item_id]);
				
				$query = new Query();
				$sql = "DELETE FROM ".$this->db_cart_items." WHERE id = $cart_item_id AND cart_id = ".$cart["id"];
				// print $sql;
				if($query->sql($sql)) {
					$cart = $this->getCarts(array("cart_id" => $cart["id"]));
					
					// add total price info to enable UI update
					$cart["total_cart_price"] = $this->getTotalCartPrice($cart["id"]);
					$cart["total_cart_price_formatted"] = formatPrice($cart["total_cart_price"]);
					
					// add callback to deletedFromCart
					$model = $IC->typeObject($item["itemtype"]);
					if(method_exists($model, "deletedFromCart")) {
						$model->deletedFromCart($item, $cart);
					}

					return $cart;
				}
			}
		}

		return false;
	}


	// Delete itemtypes from cart
	// #controller#/deleteItemtypeFromCart
	function deleteItemtypeFromCart($itemtype) {
		
		$cart = $this->getCart();
		
		if($cart) {
			$IC = new Items();
			foreach($cart["items"] as $key => $cart_item) {
				$existing_item = $IC->getItem(array("id" => $cart_item["item_id"]));
				if($existing_item["itemtype"] == $itemtype) {
					$cart = $this->deleteFromCart(array("deleteFromCart", $cart["cart_reference"], $cart_item["id"]));
				}
			}
			return $cart;
		}
		return false;	
	}


	// Empty cart
	# #controller#/emptyCart
	function emptyCart($action) {

		$cart = $this->getCart();
		if($cart) {
			if($cart["items"]) {
				foreach($cart["items"] as $cart_item) {
					$this->deleteFromCart(array("deleteFromCart", $cart["cart_reference"], $cart_item["id"]));
				}
			}

			return true;
		}
		return false;
	}



	/**
	 * ### Convert cart to order
	 * 
	 * /shop/newOrderFromCart/#cart_reference#
	 * order_comment in $_POST
	 *
	 * @param array $action
	 * @return array|false Order object. False on error. 
	 */
	function newOrderFromCart($action) {
//		debug(["newOrderFromCart", $action]);

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 2) {

			$query = new Query();
			$UC = new User();
			$IC = new Items();

			$order_comment = $this->getProperty("order_comment", "value");

			$cart_reference = $action[1];
			$received_cart = $this->getCarts(["cart_reference" => $cart_reference]);

			// you can never create a cart for someone else, so ignore cart user_id
			$user_id = session()->value("user_id");

			$cart = $this->getCart();
			
			// user cart matches cart received via REST
			if($cart && $cart["cart_reference"] == $cart_reference) {
				$cart_match = true;
			}
			// received cart is an internal cart
			else if($received_cart && $received_cart["user_id"] == $user_id) {
				$cart_match = true;
				$cart = $received_cart;
			}			
			// cart mismatch
			else {
				$cart_match = false;
			}

			// is cart registered and has content
//			print $cart_reference ." ==". $cart["cart_reference"];
			if($cart && $user_id && $cart["items"] && $cart_match) {

				$user = $UC->getUser();

				// get new order number
				$order_no = $this->getNewOrderNumber();
				if($order_no) {


					// get data from cart
					$currency = $cart["currency"];
					$country = $cart["country"];

					$delivery_address_id = $cart["delivery_address_id"];
					$delivery_address = false;
					$billing_address_id = $cart["billing_address_id"];
					$billing_address = false;

					// create base data update sql
					$sql = "UPDATE ".$this->db_orders." SET user_id=$user_id, country='$country', currency='$currency'";
//					print $sql."<br />\n";

					// add delivery address
					if($delivery_address_id) {
						$delivery_address = $UC->getAddresses(array("address_id" => $delivery_address_id));
						if($delivery_address) {
							$sql .= ", delivery_name='".prepareForDB($delivery_address["address_name"])."'";
							$sql .= ", delivery_att='".prepareForDB($delivery_address["att"])."'";
							$sql .= ", delivery_address1='".prepareForDB($delivery_address["address1"])."'";
							$sql .= ", delivery_address2='".prepareForDB($delivery_address["address2"])."'";
							$sql .= ", delivery_city='".prepareForDB($delivery_address["city"])."'";
							$sql .= ", delivery_postal='".prepareForDB($delivery_address["postal"])."'";
							$sql .= ", delivery_state='".prepareForDB($delivery_address["state"])."'";
							$sql .= ", delivery_country='".prepareForDB($delivery_address["country"])."'";
						}
					}

					// add billing address
					if($billing_address_id) {
						$billing_address = $UC->getAddresses(array("address_id" => $billing_address_id));
						if($billing_address) {
							$sql .= ", billing_name='".prepareForDB($billing_address["address_name"])."'";
							$sql .= ", billing_att='".prepareForDB($billing_address["att"])."'";
							$sql .= ", billing_address1='".prepareForDB($billing_address["address1"])."'";
							$sql .= ", billing_address2='".prepareForDB($billing_address["address2"])."'";
							$sql .= ", billing_city='".prepareForDB($billing_address["city"])."'";
							$sql .= ", billing_postal='".prepareForDB($billing_address["postal"])."'";
							$sql .= ", billing_state='".prepareForDB($billing_address["state"])."'";
							$sql .= ", billing_country='".prepareForDB($billing_address["country"])."'";
						}
					}

					// no billing info is provided
					if(!$billing_address) {

						// use available account info
						$user = $UC->getUser();
						if($user["firstname"] && $user["lastname"]) {
							$sql .= ", billing_name='".prepareForDB($user["firstname"])." ".prepareForDB($user["lastname"])."'";
						}
						else {
							$sql .= ", billing_name='".prepareForDB($user["nickname"])."'";
						}
					}


					// finalize sql
					$sql .= " WHERE order_no='$order_no'";

//					print $sql;
					// execute "create order" query 
					if($query->sql($sql)) {


						// get the new order
						$order = $this->getOrders(array("order_no" => $order_no));


						$admin_summary = [];
//						print "items";
//						print_r($cart["items"]);


						// add the items from the cart
						foreach($cart["items"] as $cart_item) {

							$order_item = $this->addCartItemToOrder($cart_item, $order);

							if($order_item) {
								
								$admin_summary[] = $order_item["item_name"];

								// get item details
								$item = $IC->getItem(["id" => $order_item["item_id"], "extend" => true]);

								// add callback to 'ordered'
								$model = $IC->typeObject($item["itemtype"]);
								if(method_exists($model, "ordered")) {
	
									$model->ordered($order_item, $order);
								}
							}
						}
				

						// update cart_reference cookie and session
						session()->reset("cart_reference");

						// Delete cart reference cookie
						setcookie("cart_reference", "", time() - 3600, "/");


						// make sure order no is set for user
						session()->value("order_no", $order_no);

						// Add cookie for user
						setcookie("order_no", $order_no, time()+60*60*24*60, "/");

						if($order_comment) {
							
							$sql = "UPDATE ".$this->db_orders." SET comment = '".$order_comment."' WHERE order_no='$order_no'";
							$query->sql($sql);
						}
						
						// only autoship order if every item should be autoshipped
						$order["autoship"] = true;
						foreach($cart["items"] as $cart_item) {
							if(!isset($item["autoship"]) || !$item["autoship"]) {
								$order["autoship"] = false;
							}
						}
						if($order["autoship"]) {
							// update shipping_status to shipped
							$sql = "UPDATE ".$this->db_orders." SET shipping_status = 2 WHERE order_no='$order_no'";
							$query->sql($sql);
						}


						// set payment status for 0-prices orders
						$order = $this->getOrders(array("order_no" => $order_no));
						$total_order_price = $this->getTotalOrderPrice($order["id"]);
						if($total_order_price["price"] === 0) {
							$sql = "UPDATE ".$this->db_orders." SET status = 1, payment_status = 2 WHERE order_no='$order_no'";
							$query->sql($sql);
						}


						// delete cart
						$sql = "DELETE FROM $this->db_carts WHERE id = ".$cart["id"]." AND cart_reference = '".$cart["cart_reference"]."'";
						// debug([$sql]);
						$query->sql($sql);


						// send notification email to admin
						mailer()->send(array(
							"recipients" => SHOP_ORDER_NOTIFIES,
							"subject" => SITE_URL . " - New order ($order_no) created by: $user_id",
							"message" => "Check out the new order: " . SITE_URL . "/janitor/admin/user/orders/" . $user_id . "\n\nOrder content: ".implode(",", $admin_summary),
							"tracking" => false
							// "template" => "system"
						));


						// order confirmation mail
						mailer()->send(array(
							"recipients" => $user["email"],
							"values" => array(
								"NICKNAME" => $user["nickname"], 
								"ORDER_NO" => $order_no, 
								"ORDER_ID" => $order["id"], 
								"ORDER_PRICE" => formatPrice($total_order_price) 
							),
							// "subject" => SITE_URL . " – Thank you for your order!",
							"tracking" => false,
							"template" => "order_confirmation"
						));

						

						global $page;
						$page->addLog("Shop->newOrderFromCart: order_no:".$order_no);


						return $this->getOrders(array("order_no" => $order_no));

					}
				}

				// order creation failed, remove unused order number
				$this->deleteOrderNumber($order_no);

			}

		}

		return false;
	}

	function addCartItemToOrder($cart_item, $order) {
		
		if($cart_item && $order) {
			
			$query = new Query();
			$IC = new Items();
			
			$quantity = $cart_item["quantity"];
			$item_id = $cart_item["item_id"];
	
			// get item details
			$item = $IC->getItem(["id" => $item_id, "extend" => true]);
	
			if($item) {
	
				// get best price for item
				$price = $this->getPrice($item_id, array("quantity" => $quantity, "currency" => $order["currency"], "country" => $order["country"]));
				// print_r("price: ".$price);
	
				// use custom price if available
				if(isset($cart_item["custom_price"]) && $cart_item["custom_price"] !== false) {
					$custom_price = $cart_item["custom_price"];
					
					$price["price"] = $custom_price;
					$custom_price_without_vat = $custom_price / (100 + $price["vatrate"]) * 100;
					$price["price_without_vat"] = $custom_price_without_vat;
					$price["vat"] = $custom_price - $custom_price_without_vat;
				}
	
				$unit_price = $price["price"];
				$unit_vat = $price["vat"];
				$total_price = $unit_price * $quantity;
				$total_vat = $unit_vat * $quantity;
	
				// use custom name for cart item if available
				$item_name = isset($cart_item["custom_name"]) ? $cart_item["custom_name"] : $item["name"];
	
				$sql = "INSERT INTO ".$this->db_order_items." SET order_id=".$order["id"].", item_id=$item_id, name='".prepareForDB($item_name)."', quantity=$quantity, unit_price=$unit_price, unit_vat=$unit_vat, total_price=$total_price, total_vat=$total_vat";
				// print $sql;
	
	
				// Add item to order
				if($query->sql($sql)) {
					$order_item_id = $query->lastInsertId();
					
					// get order_item
					$sql = "SELECT * FROM ".$this->db_order_items." WHERE id = $order_item_id";
					if($query->sql($sql)) {
						$order_item = $query->result(0);
	
						$order_item["custom_price"] = isset($custom_price) ? $custom_price : null;
						$order_item["item_name"] = $item_name;
	
						return $order_item;
						
					}
					
				}
	
			}
		}


		return false;
	}




	/**
	* get orders
	*
	* get orders by order_id or order_no (if current user_id matches)
	* get orders for current user_id
	*/
	function getOrders($_options=false) {

		$user_id = session()->value("user_id");

		// get specific order
		$order_id = false;
		$order_no = false;

		// get all orders containing item_id
		$item_id = false;
		$itemtype = false;

		// get all orders with status as specified
		$status = false;


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "order_id"          : $order_id            = $_value; break;
					case "order_no"          : $order_no            = $_value; break;

					case "item_id"           : $item_id             = $_value; break;
					case "itemtype"          : $itemtype            = $_value; break;

					case "status"            : $status              = $_value; break;
				}
			}
		}

		$query = new Query();

		// get specific order
		if($order_id !== false || $order_no !== false) {

			if($order_id) {
				$sql = "SELECT * FROM ".$this->db_orders." WHERE id = $order_id AND user_id = $user_id LIMIT 1";
			}
			else {
				$sql = "SELECT * FROM ".$this->db_orders." WHERE order_no = '$order_no' AND user_id = $user_id  LIMIT 1";
			}

//			print $sql."<br>";
			if($query->sql($sql)) {
				$order = $query->result(0);
				$order["items"] = array();

				// get items for order
				if($query->sql("SELECT * FROM ".$this->db_order_items." as items WHERE items.order_id = ".$order["id"])) {
					$order["items"] = $query->results();
				}

				// text values
				$order["order_status_text"] = $this->order_statuses[$order["status"]];
				$order["shipping_status_text"] = $this->shipping_statuses[$order["shipping_status"]];
				$order["payment_status_text"] = $this->payment_statuses[$order["payment_status"]];
				return $order;
			}

		}

		// all orders for user_id
		else {

			if($itemtype) {

				$sql = "SELECT orders.* FROM ".$this->db_orders." as orders, ".$this->db_order_items." as order_items, ".UT_ITEMS." as items WHERE orders.user_id=$user_id".($status !== false ? " AND status=$status" : "")." AND order_items.order_id = orders.id AND items.itemtype = '$itemtype' AND order_items.item_id = items.id ORDER BY orders.id DESC";
				// print $sql;
				if($query->sql($sql)) {
					$orders = $query->results();

					foreach($orders as $i => $order) {
						$orders[$i]["items"] = array();
						if($query->sql("SELECT * FROM ".$this->db_order_items." WHERE order_id = ".$order["id"])) {
							$orders[$i]["items"] = $query->results();
						}
					}

					return $orders;
				}

			}

			else {
			
				if($query->sql("SELECT * FROM ".$this->db_orders." WHERE user_id=$user_id".($status !== false ? " AND status=$status" : "")." ORDER BY id DESC")) {
					$orders = $query->results();

					foreach($orders as $i => $order) {
						$orders[$i]["items"] = array();
						if($query->sql("SELECT * FROM ".$this->db_order_items." WHERE order_id = ".$order["id"])) {
							$orders[$i]["items"] = $query->results();
						}
					}

					return $orders;
				}

			}

		}

		return false;
	}


	function getUnpaidOrders($_options=false) {
		
		// get all unpaid orders for user_id
		$user_id = session()->value("user_id");

		// get all unpaid orders containing item_id
		$item_id = false;
		$itemtype = false;


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "item_id"           : $item_id             = $_value; break;
					case "itemtype"          : $itemtype            = $_value; break;
				}
			}
		}


		$query = new Query();


		if($user_id) {

			if($itemtype) {

				$sql = "SELECT orders.id, orders.order_no FROM ".$this->db_orders." as orders, ".$this->db_order_items." as order_items, ".UT_ITEMS." as items WHERE orders.user_id=$user_id AND orders.payment_status != 2 AND orders.status != 3 AND order_items.order_id = orders.id AND items.itemtype = '$itemtype' AND order_items.item_id = items.id ORDER BY orders.id DESC";
//				print $sql;
				$query->sql($sql);
				return $query->results();

			}
			// get all unpaid orders with item_id in it
			else if($item_id) {

				$sql = "SELECT orders.id, orders.order_no FROM ".$this->db_orders." as orders, ".$this->db_order_items." as items WHERE orders.user_id=$user_id AND orders.payment_status != 2 AND orders.status != 3 AND orders.id = items.order_id AND items.item_id = $item_id GROUP BY order_id";
	//			print $sql;
				$query->sql($sql);
				return $query->results();

			}
			else {
				$sql = "SELECT id, order_no FROM ".$this->db_orders." WHERE user_id=$user_id AND payment_status != 2 AND status != 3 ORDER BY id DESC";
//				print $sql;
				$query->sql($sql);
				return $query->results();
			}

		}

		return false;
	}

	// get credit note number associated with cancelled order
	function getCreditnoteNo($_options = false) {

		$user_id = session()->value("user_id");
		$order_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "order_id"          : $order_id            = $_value; break;
				}
			}
		}

		if($order_id) {
			$query = new Query();
			$sql = "SELECT * FROM ".$this->db_cancelled_orders." WHERE order_id = $order_id LIMIT 1";
			$query->sql($sql);

			return $query->result(0, "creditnote_no");
		}

		return false;
	}



	// Select payment method for cart
	function selectPaymentMethodForCart($action) {

		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("payment_method_id", "cart_id"))) {

			$cart_id = $this->getProperty("cart_id", "value");
			$payment_method_id = $this->getProperty("payment_method_id", "value");

			// $payment_object["cart"] = $this->getCarts(array("cart_id" => $cart_id));
			$cart = $this->getCarts(array("cart_id" => $cart_id));

			// $payment_object["payment_method"] = $page->paymentMethods($payment_method_id);
			$payment_method = $page->paymentMethods($payment_method_id);

			if($payment_method && $payment_method["state"] === "public") {

				if($payment_method["gateway"]) {

					return [
						"status" => "PROCEED_TO_GATEWAY", 
						"payment_gateway" => $payment_method["gateway"],
						"cart_reference" => $cart["cart_reference"]
					];

				}
				else {

					// no automatic payment processing available
					// create order from cart
					$order = $this->newOrderFromCart(["newOrderFromCart", $cart["cart_reference"]]);
					if($order) {

						// Clear messages
						message()->resetMessages();

						return [
							"status" => "PROCEED_TO_RECEIPT", 
							"payment_name" => $payment_method["name"], 
							"order_no" => $order["order_no"],
						];

					}
					else {

						return [
							"status" => "ORDER_FAILED"
						];

					}
				}

				// return $payment_method;
			}

			return false;

		}

		return false;
	}

	// Select user payment method for cart
	function selectUserPaymentMethodForCart($action) {

		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("payment_method_id", "cart_id", "user_payment_method_id"))) {

			$UC = new User();

			$cart_id = $this->getProperty("cart_id", "value");
			$payment_method_id = $this->getProperty("payment_method_id", "value");
			$user_payment_method_id = $this->getProperty("user_payment_method_id", "value");
			
			$gateway_payment_method_id = getPost("gateway_payment_method_id");

			$cart = $this->getCarts(array("cart_id" => $cart_id));

			$user_payment_method = $UC->getPaymentMethods([
				"user_payment_method_id" => $user_payment_method_id, 
				"payment_method_id" => $payment_method_id, 
				"gateway_payment_method_id" => $gateway_payment_method_id, 
				"extend" => true
			]);

			if($user_payment_method) {

				if($user_payment_method["gateway"]) {

					return [
						"status" => "PROCEED_TO_INTENT",
						"gateway_payment_method_id" => $gateway_payment_method_id,
						"payment_gateway" => $user_payment_method["gateway"],
						"cart" => $cart
					];

				}
				else {

					// no automatic payment processing available
					// create order from cart
					$order = $this->newOrderFromCart(["newOrderFromCart", $cart["cart_reference"]]);
					if($order) {

						// Clear messages
						message()->resetMessages();

						return [
							"status" => "PROCEED_TO_RECEIPT", 
							"payment_name" => $user_payment_method["name"], 
							"order_no" => $order["order_no"],
						];

					}
					else {

						return [
							"status" => "ORDER_FAILED"
						];

					}

				}

			}

		}

		return false;
	}



	// Select payment method for order
	function selectPaymentMethodForOrder($action) {
		// debug(["selectPaymentMethodForOrder", $action]);

		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("payment_method_id", "order_id"))) {

			$order_id = $this->getProperty("order_id", "value");
			$payment_method_id = $this->getProperty("payment_method_id", "value");

			$order = $this->getOrders(array("order_id" => $order_id));

			$payment_method = $page->paymentMethods($payment_method_id);

			if($payment_method && $payment_method["state"] === "public") {

				if($payment_method["gateway"]) {

					return [
						"status" => "PROCEED_TO_GATEWAY", 
						"payment_gateway" => $payment_method["gateway"],
						"order_no" => $order["order_no"]
					];

				}
				else {

					return [
						"status" => "PROCEED_TO_RECEIPT", 
						"payment_name" => $payment_method["name"], 
						"order_no" => $order["order_no"]
					];

				}

			}

		}

		return false;
	}

	// Select user payment method for order
	function selectUserPaymentMethodForOrder($action) {

		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("payment_method_id", "order_id", "user_payment_method_id"))) {

			$UC = new User();

			$order_id = $this->getProperty("order_id", "value");
			$payment_method_id = $this->getProperty("payment_method_id", "value");
			$user_payment_method_id = $this->getProperty("user_payment_method_id", "value");
			
			$gateway_payment_method_id = getPost("gateway_payment_method_id");

			$order = $this->getOrders(array("order_id" => $order_id));

			$user_payment_method = $UC->getPaymentMethods([
				"user_payment_method_id" => $user_payment_method_id, 
				"payment_method_id" => $payment_method_id, 
				"gateway_payment_method_id" => $gateway_payment_method_id, 
				"extend" => true
			]);

			if($user_payment_method) {

				if($user_payment_method["gateway"]) {

					return [
						"status" => "PROCEED_TO_INTENT",
						"gateway_payment_method_id" => $gateway_payment_method_id,
						"payment_gateway" => $user_payment_method["gateway"],
						"order" => $order
					];

				}
				else {

					return [
						"status" => "PROCEED_TO_RECEIPT", 
						"payment_name" => $user_payment_method["name"], 
						"order_no" => $order["order_no"]
					];

				}

			}

		}

		return false;
	}



	// Select payment method for orders
	function selectPaymentMethodForOrders($action) {
		// debug(["selectPaymentMethodForOrders", $action]);

		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("payment_method_id", "order_ids"))) {

			$order_ids = $this->getProperty("order_ids", "value");
			$payment_method_id = $this->getProperty("payment_method_id", "value");

			$payment_method = $page->paymentMethods($payment_method_id);

			if($payment_method && $payment_method["state"] === "public") {

				if($payment_method["gateway"]) {

					return [
						"status" => "PROCEED_TO_GATEWAY", 
						"payment_gateway" => $payment_method["gateway"],
						"order_ids" => $order_ids
					];

				}
				else {

					$order_id_list = explode(",", $order_ids);
					$order_no_list = [];
					foreach($order_id_list as $order_id) {
						$order = $this->getOrders(["order_id" => $order_id]);
						$order_no_list[] = $order["order_no"];
					}

					return [
						"status" => "PROCEED_TO_RECEIPT", 
						"payment_name" => $payment_method["name"], 
						"order_nos" => implode(",", $order_no_list),
					];

				}

			}

		}

		return false;
	}

	// Select user payment method for orders
	function selectUserPaymentMethodForOrders($action) {

		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("payment_method_id", "order_ids", "user_payment_method_id"))) {

			// $query = new Query();
			$UC = new User();

			$order_ids = $this->getProperty("order_ids", "value");
			$payment_method_id = $this->getProperty("payment_method_id", "value");
			$user_payment_method_id = $this->getProperty("user_payment_method_id", "value");
			
			$gateway_payment_method_id = getPost("gateway_payment_method_id");

			// $order = $this->getOrders(array("order_id" => $order_id));
			$order_id_list = explode(",", $order_ids);
			$order_no_list = [];
			$orders = [];
			foreach($order_id_list as $order_id) {
				$order = $this->getOrders(["order_id" => $order_id]);
				$orders[] = $order;
				$order_no_list[] = $order["order_no"];
			}


			$user_payment_method = $UC->getPaymentMethods([
				"user_payment_method_id" => $user_payment_method_id, 
				"payment_method_id" => $payment_method_id, 
				"gateway_payment_method_id" => $gateway_payment_method_id, 
				"extend" => true
			]);

			if($user_payment_method) {

				if($user_payment_method["gateway"]) {

					return [
						"status" => "PROCEED_TO_INTENT",
						"gateway_payment_method_id" => $gateway_payment_method_id,
						"payment_gateway" => $user_payment_method["gateway"],
						"order_ids" => $order_ids,
						"orders" => $orders,
					];

				}
				else {


					return [
						"status" => "PROCEED_TO_RECEIPT", 
						"payment_name" => $user_payment_method["name"], 
						"order_nos" => implode(",", $order_no_list),
					];

				}

			}

		}

		return false;
	}



	// Process gateway data for cart
	function processCardForCart($action) {
		// debug(["processCardForCart shop"]);
		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();


		// does values validate
		if(count($action) == 5 && $this->validateList(array("card_number", "card_exp_month", "card_exp_year", "card_cvc"))) {

			// $gateway = $action[1];
			$cart_reference = $action[3];
			$cart = $this->getCarts(["cart_reference" => $cart_reference]);

			if($cart) {

				$card_number = preg_replace("/ /", "", $this->getProperty("card_number", "value"));
				$card_exp_month = $this->getProperty("card_exp_month", "value");
				$card_exp_year = $this->getProperty("card_exp_year", "value");
				$card_cvc = $this->getProperty("card_cvc", "value");

				// Use payment gateway for further processing
				return payments()->processCardForCart($cart, $card_number, $card_exp_month, $card_exp_year, $card_cvc);

			}

		}

		return false;

	}

	// Process gateway data for order
	function processCardForOrder($action) {
		// debug(["processCardForCart shop"]);
		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();


		// does values validate
		if(count($action) == 5 && $this->validateList(array("card_number", "card_exp_month", "card_exp_year", "card_cvc"))) {

			// $gateway = $action[1];
			$order_no = $action[3];
			$order = $this->getOrders(["order_no" => $order_no]);

			if($order) {

				$card_number = preg_replace("/ /", "", $this->getProperty("card_number", "value"));
				$card_exp_month = $this->getProperty("card_exp_month", "value");
				$card_exp_year = $this->getProperty("card_exp_year", "value");
				$card_cvc = $this->getProperty("card_cvc", "value");

				// Use payment gateway for further processing
				return payments()->processCardForOrder($order, $card_number, $card_exp_month, $card_exp_year, $card_cvc);

			}

		}

		return false;

	}

	// Process gateway data for orders
	function processCardForOrders($action) {
		// debug(["processCardForCart shop"]);
		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();


		// does values validate
		if(count($action) == 5 && $this->validateList(array("card_number", "card_exp_month", "card_exp_year", "card_cvc"))) {

			// $gateway = $action[1];
			$order_ids = explode(",", $action[3]);
			$orders = [];
			foreach($order_ids as $order_id) {

				$order = $this->getOrders(["order_id" => $order_id]);
				if($order) {
					$orders[] = $order;
				}
			}
			

			if($orders) {

				$card_number = preg_replace("/ /", "", $this->getProperty("card_number", "value"));
				$card_exp_month = $this->getProperty("card_exp_month", "value");
				$card_exp_year = $this->getProperty("card_exp_year", "value");
				$card_cvc = $this->getProperty("card_cvc", "value");

				// Use payment gateway for further processing
				return payments()->processCardForOrders($orders, $card_number, $card_exp_month, $card_exp_year, $card_cvc);

			}

		}

		return false;

	}



	// PAYMENTS

	function getPayments($_options=false) {

		$user_id = session()->value("user_id");
		$order_id = false;
		$payment_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "payment_id"        : $payment_id          = $_value; break;
					case "order_id"          : $order_id            = $_value; break;
				}
			}
		}

		$query = new Query();

		// get specific order
		if($order_id !== false) {

			$sql = "SELECT * FROM ".$this->db_payments.", ".$this->db_orders." as orders WHERE order_id = $order_id AND orders.id = $order_id AND orders.user_id = $user_id";

			// print $sql."<br>\n";
			if($query->sql($sql)) {
				return $query->results();
			}

		}
		// get specific payment
		else if($payment_id !== false) {

			$sql = "SELECT * FROM ".$this->db_payments." as payments, ".$this->db_orders." as orders WHERE payments.order_id = orders.id AND payments.id = $payment_id AND orders.user_id = $user_id";

			// print $sql."<br>\n";
			if($query->sql($sql)) {
				return $query->result(0);
			}

		}
		else {

			$sql = "SELECT * FROM ".$this->db_payments." as payments, ".$this->db_orders." as orders WHERE payments.order_id = orders.id AND orders.user_id = ".$user_id." ORDER BY payments.created_at, payments.id DESC";

			// print $sql."<br>\n";
			if($query->sql($sql)) {
				return $query->results();
			}

		}

		return false;
	}


}

?>