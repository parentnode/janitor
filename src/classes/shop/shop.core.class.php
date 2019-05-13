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
		// payment method
		$this->addToModel("payment_method", array(
			"type" => "string",
			"label" => "Payment method",
			"required" => true,
			"hint_message" => "Please select a payment method.",
			"error_message" => "Invalid payment method."
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


	// get next available order number
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

		if(isset($cart["items"]) && $cart["items"]) {
			foreach($cart["items"] as $cart_item) {
				$price = $this->getPrice($cart_item["item_id"], array("quantity" => $cart_item["quantity"], "currency" => $cart["currency"], "country" => $cart["country"]));
				if($price) {
					$total_price += $price["price"] * $cart_item["quantity"];
					$total_vat += $price["vat"] * $cart_item["quantity"];
				}
			}
		}
		return array("price" => $total_price, "vat" => $total_vat, "currency" => $cart["currency"], "country" => $cart["country"]);
	}


	// get best available price for item
	function getPrice($item_id, $_options = false) {
		global $page;
		$IC = new Items();

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
		if($cart_id || $cart_reference) {

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


	// Add a new cart with optional user, currency and country
	# /shop/addCart
	function addCart($action) {
		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();

		$user_id = session()->value("user_id");

		// does values validate
		if(count($action) == 1 && $user_id) {

			$query = new Query();

			$currency = $this->getProperty("currency", "value");
			$country = $this->getProperty("country", "value");

			$billing_address_id = $this->getProperty("billing_address_id", "value");
			$delivery_address_id = $this->getProperty("delivery_address_id", "value");


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

				// make sure cart reference is set for user
				session()->value("cart_reference", $cart_reference);

				// Add cookie for user
				setcookie("cart_reference", $cart_reference, time()+60*60*24*60, "/");

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

	// Add item to cart
	# /shop/addToCart
	// Items and quantity in $_post
	function addToCart($action) {

		if(count($action) >= 1) {

			// Get posted values to make them available for models
			$this->getPostedEntities();

			$user_id = session()->value("user_id");

			$cart = false;

			// getCart checks for cart_reference in session and cookie or looks for cart for current user ( != 1)
			$cart = $this->getCart();
			if($cart) {
				$cart_reference = $cart["cart_reference"];
			}
			// still no cart
			// then add a new cart
			else {
				$cart = $this->addCart(array("addCart"));
//				print_r($cart);
				
				$cart_reference = $cart["cart_reference"];
			}

			// does values validate
			if($cart && $this->validateList(array("quantity", "item_id"))) {

				$query = new Query();
				$IC = new Items();

				$quantity = $this->getProperty("quantity", "value");
				$item_id = $this->getProperty("item_id", "value");


				// make sure only one membership exists in cart at any given time

				// is there any items in cart already?
				if($cart["items"]) {

					// what kind of itemtype is being added
					$item = $IC->getItem(array("id" => $item_id));

					// if it is a membership, then remove existing memberships from cart
					if($item["itemtype"] == "membership") {

						foreach($cart["items"] as $key => $cart_item) {
							$existing_item = $IC->getItem(array("id" => $cart_item["item_id"]));
							if($existing_item["itemtype"] == "membership") {
								$cart = $this->deleteFromCart(array("deleteFromCart", $cart_reference, $cart_item["id"]));
							}
						}
					}
				}



				// check if item is already in cart?
				if($cart["items"] && arrayKeyValue($cart["items"], "item_id", $item_id) !== false) {
					$existing_item_index = arrayKeyValue($cart["items"], "item_id", $item_id);


					$existing_item = $cart["items"][$existing_item_index];
					$existing_quantity = $existing_item["quantity"];
					$new_quantity = intval($quantity) + intval($existing_quantity);

					$sql = "UPDATE ".$this->db_cart_items." SET quantity=$new_quantity WHERE id = ".$existing_item["id"]." AND cart_id = ".$cart["id"];
//					print $sql;
				}
				// insert new cart item
				else {

					$sql = "INSERT INTO ".$this->db_cart_items." SET cart_id=".$cart["id"].", item_id=$item_id, quantity=$quantity";
//					print $sql;
				}

				if($query->sql($sql)) {

					// update modified at time
					$sql = "UPDATE ".$this->db_carts." SET modified_at=CURRENT_TIMESTAMP WHERE id = ".$cart["id"];
					$query->sql($sql);

					return $this->getCart();

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

			if($cart) {

				$query = new Query();
				$sql = "DELETE FROM ".$this->db_cart_items." WHERE id = $cart_item_id AND cart_id = ".$cart["id"];
				// print $sql;
				if($query->sql($sql)) {
					$cart = $this->getCarts(array("cart_id" => $cart["id"]));

					// add total price info to enable UI update
					$cart["total_cart_price"] = $this->getTotalCartPrice($cart["id"]);
					$cart["total_cart_price_formatted"] = formatPrice($cart["total_cart_price"]);

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
	 * Convert cart to order
	 * 
	 * /shop/newOrderFromCart/#cart_reference#
	 *
	 * @param array $action
	 * @return array|false Order object. False on error. 
	 */
	function newOrderFromCart($action) {
//		print "newOrderFromCart";

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 2) {

			$query = new Query();
			$UC = new User();
			$IC = new Items();

			$cart_reference = $action[1];

			// you can never create a cart for someone else, so ignore cart user_id
			$user_id = session()->value("user_id");

			$cart = $this->getCart();

			// is cart registered and has content
			// and enforce a sanity check by matching cart far with REST param
//			print $cart_reference ." ==". $cart["cart_reference"];
			if($cart && $user_id && $cart["items"] && $cart_reference == $cart["cart_reference"]) {

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

					// use account info, if no billing info is provided
					if(!$billing_address) {

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
					// execute "create order"" query 
					if($query->sql($sql)) {


						// get the new order
						$order = $this->getOrders(array("order_no" => $order_no));


						$admin_summary = [];
//						print "items";
//						print_r($cart["items"]);

						// add the items from the cart
						foreach($cart["items"] as $cart_item) {

							$quantity = $cart_item["quantity"];
							$item_id = $cart_item["item_id"];

							// get item details
							$item = $IC->getItem(array("id" => $item_id, "extend" => array("subscription_method" => true)));

							if($item) {

								// get best price for item
								$price = $this->getPrice($item_id, array("quantity" => $quantity, "currency" => $order["currency"], "country" => $order["country"]));
				//				print_r($price);

								$unit_price = $price["price"];
								$unit_vat = $price["vat"];
								$total_price = $unit_price * $quantity;
								$total_vat = $unit_vat * $quantity;

								$sql = "INSERT INTO ".$this->db_order_items." SET order_id=".$order["id"].", item_id=$item_id, name='".prepareForDB($item["name"])."', quantity=$quantity, unit_price=$unit_price, unit_vat=$unit_vat, total_price=$total_price, total_vat=$total_vat";
//								print $sql;


								// Add item to order
								if($query->sql($sql)) {

									// additional tasks
									$admin_summary[] = $item["name"];


									$membership = false;

									// item is membership
									if(SITE_MEMBERS && $item["itemtype"] == "membership") {

										// check if user already has membership
										$membership = $UC->getMembership();

										// membership does not exist
										if(!$membership) {
											// add new membership
											$membership = $UC->addMembership(array("addMembership"));
										}

									}


									// subscription method available for item
									if(SITE_SUBSCRIPTIONS && $item["subscription_method"]) {

										// set values for updating/creating subscription
										$_POST["order_id"] = $order["id"];
										$_POST["item_id"] = $item_id;

										// if membership variable is not false
										// it means that membership exists and current type is membership
										// avoid creating new membership subscription
										if($membership && $membership["item"]) {

											// get the current membership subscription
											$subscription = $UC->getSubscriptions(array("item_id" => $membership["item"]["id"]));
										}
										else {

											// check if subscription already exists
											$subscription = $UC->getSubscriptions(array("item_id" => $item_id));
										}

										// if subscription is for itemtype=membership
										// add/updateSubscription will also update subscription_id on membership 

										// update existing subscription
										if($subscription) {
											$subscription = $UC->updateSubscription(array("updateSubscription", $subscription["id"]));
										}
										// add new subscription
										else {
											$subscription = $UC->addSubscription(array("addSubscription"));
										}

										// clean up POST array
										unset($_POST);

//										print_r($subscription);
										$order["comment"] .= $subscription["item"]["name"] . ($subscription["expires_at"] ? " (" . ($subscription["renewed_at"] ? date("d/m/Y", strtotime($subscription["renewed_at"])) : date("d/m/Y", strtotime($subscription["created_at"]))) ." - ". date("d/m/Y", strtotime($subscription["expires_at"])).")" : "");
//										print_r($order);

									}

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


						// Update order comment
						$sql = "UPDATE ".$this->db_orders." SET comment = '".$order["comment"]."' WHERE order_no='$order_no'";
//						print $sql."<br>\n";
						$query->sql($sql);


						// set payment status for 0-prices orders
						$order = $this->getOrders(array("order_no" => $order_no));
						$total_order_price = $this->getTotalOrderPrice($order["id"]);
						if($total_order_price["price"] === 0) {
							// create base data update sql
							$sql = "UPDATE ".$this->db_orders." SET status = 1, payment_status = 2 WHERE order_no='$order_no'";
							$query->sql($sql);
						}


						// delete cart
						$sql = "DELETE FROM $this->db_carts WHERE id = ".$cart["id"]." AND cart_reference = '".$cart["cart_reference"]."'";
			//			print $sql;
						$query->sql($sql);


						// send notification email to admin
						mailer()->send(array(
							"recipients" => SHOP_ORDER_NOTIFIES,
							"subject" => SITE_URL . " - New order ($order_no) created by: $user_id",
							"message" => "Check out the new order: " . SITE_URL . "/janitor/admin/user/orders/" . $user_id . "\n\nOrder content: ".implode(",", $admin_summary),
							"tracking" => false
							// "template" => "system"
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

				$sql = "SELECT orders.* FROM ".$this->db_orders." as orders, ".$this->db_order_items." as order_items, ".UT_ITEMS." as items WHERE orders.user_id=$user_id AND orders.payment_status != 2 AND orders.status != 3 AND order_items.order_id = orders.id AND items.itemtype = '$itemtype' AND order_items.item_id = items.id ORDER BY orders.id DESC";
//				print $sql;
				$query->sql($sql);
				return $query->results();

			}
			// get all unpaid orders with item_id in it
			else if($item_id) {

				$sql = "SELECT orders.* FROM ".$this->db_orders." as orders, ".$this->db_order_items." as items WHERE orders.user_id=$user_id AND orders.payment_status != 2 AND orders.status != 3 AND orders.id = items.order_id AND items.item_id = $item_id GROUP BY order_id";
	//			print $sql;
				$query->sql($sql);
				return $query->results();

			}
			else {
				$sql = "SELECT * FROM ".$this->db_orders." WHERE user_id=$user_id AND payment_status != 2 AND status != 3 ORDER BY id DESC";
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

	// Select payment method
	// If order is for subscription, then also set this payment method for the subscription
	function selectPaymentMethod($action) {

		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("payment_method", "order_id"))) {

			$query = new Query();
			$UC = new User();


			$user_id = session()->value("user_id");

			$order_id = $this->getProperty("order_id", "value");
			$order = $this->getOrders(array("order_id" => $order_id));
			
			$payment_method_id = $this->getProperty("payment_method", "value");
			$payment_method = $page->paymentMethods($payment_method_id);

			if($order && $payment_method) {

				// add order no to return object - because receipt requires and order_no to display correctly
				$payment_method["order_no"] = $order["order_no"];


				// get subscriptions related to this order and update their payment method for future reference
				$sql = "SELECT * FROM ".$UC->db_subscriptions." WHERE order_id = ".$order["id"];
//				print $sql;
				if($query->sql($sql)) {

					$subscriptions = $query->results();
					foreach($subscriptions as $subscription) {

						$sql = "UPDATE ".$UC->db_subscriptions." SET modified_at = CURRENT_TIMESTAMP, payment_method = $payment_method_id WHERE user_id = $user_id AND id = ".$subscription["id"];
//						print $sql;
						if($query->sql($sql)) {


							global $page;
							$page->addLog("Shop->selectPaymentMethod: order_id:$order_id, payment_method: $payment_method_id");

						}

					}

				}

				return $payment_method;

			}

		}

		return false;
	}


	// select bulk payment method
	// make payment method details and order ids ready
	function selectBulkPaymentMethod($action) {

		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("payment_method", "order_ids"))) {

			$query = new Query();
			$UC = new User();


			$user_id = session()->value("user_id");
			$order_ids = $this->getProperty("order_ids", "value");

			$payment_method_id = $this->getProperty("payment_method", "value");
			$payment_method = $page->paymentMethods($payment_method_id);

 			if($order_ids && $payment_method) {

				// add order no to return object - because receipt requires and order_no to display correctly
				$payment_method["order_ids"] = $order_ids;

				return $payment_method;

			}

		}

		return false;
	}


	// Process gateway data
	function processOrderPayment($action) {

		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();


		// does values validate
		if(count($action) == 4 && $this->validateList(array("card_number", "card_exp_month", "card_exp_year", "card_cvc"))) {

			$order_no = $action[1];
			$gateway = $action[2];

			$card_number = preg_replace("/ /", "", $this->getProperty("card_number", "value"));
			$card_exp_month = $this->getProperty("card_exp_month", "value");
			$card_exp_year = $this->getProperty("card_exp_year", "value");
			$card_cvc = $this->getProperty("card_cvc", "value");


			if($order_no) {
				$order = $this->getOrders(array("order_no" => $order_no));

				if($order && $order["payment_status"] !== 2) {

					$UC = new User();
					$order["user"] = $UC->getUser();
					$order["total_price"] = $this->getTotalOrderPrice($order["id"]);

					return payments()->processCardAndPayOrder($order, $card_number, $card_exp_month, $card_exp_year, $card_cvc);

				}

			}

		}

		return false;

	}

	// Process gateway data
	function processBulkOrderPayment($action) {

		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();


		// does values validate
		if(count($action) == 4 && $this->validateList(array("card_number", "card_exp_month", "card_exp_year", "card_cvc"))) {

			$order_ids = explode(",", $action[1]);
			$gateway = $action[2];

			$card_number = preg_replace("/ /", "", $this->getProperty("card_number", "value"));
			$card_exp_month = $this->getProperty("card_exp_month", "value");
			$card_exp_year = $this->getProperty("card_exp_year", "value");
			$card_cvc = $this->getProperty("card_cvc", "value");


			if($order_ids) {
				$bulk_order = ["total_price" => 0];

				$order_nos = [];
				$UC = new User();
				$bulk_order["user"] = $UC->getUser();
				$bulk_order["user_id"] = $bulk_order["user"]["id"];
				foreach($order_ids as $order_id) {

					$order = $this->getOrders(array("order_id" => $order_id));
					$remaining_order_price = false;

					if($order && $order["payment_status"] !== 2) {
						$remaining_order_price = $this->getRemainingOrderPrice($order_id);

						$bulk_order["currency"] = $order["currency"];

						$order_nos[] = $order["order_no"];
//						$order_ids[] = $order["id"];

						$bulk_order["total_price"] += $remaining_order_price["price"];
					}

				}

				$description = implode(", ", $order_nos);
				$bulk_order["order_no"]	= strlen($description) > 22 ? cutString($description, 14)." and more" : $description;
				$bulk_order["id"]	= implode(", ", $order_ids);
				$bulk_order["custom_description"] = "Bulk payment of ".implode(", ", $order_nos);

				return payments()->processCardAndPayOrders($bulk_order, $card_number, $card_exp_month, $card_exp_year, $card_cvc);

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


	// check if we have gateway user info (indicates we can charge)
	function canBeCharged($_options = false) {

		$user_id = session()->value("user_id");
		$gateway = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "gateway"           : $gateway             = $_value; break;
				}
			}
		}


		$customer_id = payments()->getGatewayUserId($user_id);
		if($customer_id) {
			return true;
		}

		// if($gateway == "stripe") {
		//
		// 	include_once("classes/adapters/stripe.class.php");
		// 	$GC = new JanitorStripe();
		//
		// 	$customer_id = $GC->getCustomerId($user_id);
		// 	if($customer_id) {
		// 		return true;
		// 	}
		// }

		return false;
	}

}

?>