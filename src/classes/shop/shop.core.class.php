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
			"hint_message" => "Write your email so we can contact you regarding your order",
			"error_message" => "Invalid email"
		));
		// mobile
		$this->addToModel("mobile", array(
			"type" => "tel",
			"label" => "Your mobile",
			"required" => true,
			"hint_message" => "Write your mobile number so we can contact you regarding your order",
			"error_message" => "Invalid number"
		));




		$this->addToModel("country", array(
			"type" => "string",
			"label" => "Country",
			"required" => true,
			"hint_message" => "Country for order", 
			"error_message" => "Country for order"
		));

		$this->addToModel("currency", array(
			"type" => "string",
			"label" => "Currency",
			"required" => true,
			"hint_message" => "Currency for order", 
			"error_message" => "Currency for order"
		));

		$this->addToModel("quantity", array(
			"type" => "integer",
			"label" => "Quantity",
			"required" => true,
			"hint_message" => "Quantity of items", 
			"error_message" => "Quantity must be a number"
		));



		// Nickname
		$this->addToModel("billing_name", array(
			"type" => "string",
			"label" => "Full name",
			"required" => true,
			"hint_message" => "Write your full name for the invoice", 
			"error_message" => "Name must be filled out"
		));


		// BILLING ADDRESS

		// billing address ID
		$this->addToModel("billing_address_id", array(
			"type" => "integer",
			"label" => "Billing address",
			"hint_message" => "Select billing address",
			"error_message" => "Invalid billing address"
		));
		// att
		$this->addToModel("billing_att", array(
			"type" => "string",
			"label" => "Att",
			"hint_message" => "Att - contact person at destination"
		));
		// address 1
		$this->addToModel("billing_address1", array(
			"type" => "string",
			"label" => "Address",
			"required" => true,
			"hint_message" => "Address",
			"error_message" => "Invalid address"
		));
		// address 2
		$this->addToModel("billing_address2", array(
			"type" => "string",
			"label" => "Additional address",
			"hint_message" => "Additional address info",
			"error_message" => "Invalid address"
		));
		// city
		$this->addToModel("billing_city", array(
			"type" => "string",
			"label" => "City",
			"required" => true,
			"hint_message" => "Write your city",
			"error_message" => "Invalid city"
		));
		// postal code
		$this->addToModel("billing_postal", array(
			"type" => "string",
			"label" => "Postal code",
			"required" => true,
			"hint_message" => "Postalcode of your city",
			"error_message" => "Invalid postal code"
		));
		// state
		$this->addToModel("billing_state", array(
			"type" => "string",
			"label" => "State/region",
			"hint_message" => "Write your state/region, if applicaple",
			"error_message" => "Invalid state/region"
		));
		// country
		$this->addToModel("billing_country", array(
			"type" => "string",
			"label" => "Country",
			"required" => true,
			"hint_message" => "Country",
			"error_message" => "Invalid country"
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
			"hint_message" => "Select order to associate payment with",
			"error_message" => "Invalid order"
		));
		// transactions id
		$this->addToModel("transaction_id", array(
			"type" => "string",
			"label" => "Transasction id",
			"hint_message" => "Unique transaction id",
			"error_message" => "Invalid id"
		));
		// payment amount
		$this->addToModel("payment_amount", array(
			"type" => "string",
			"label" => "Payment amount",
			"required" => true,
			"hint_message" => "Payment amount including tax",
			"error_message" => "Invalid amount"
		));
		// payment method
		$this->addToModel("payment_method", array(
			"type" => "string",
			"label" => "Payment method",
			"required" => true,
			"hint_message" => "Please select a payment method",
			"error_message" => "Please select a payment method"
		));


		parent::__construct();
	}





	// get next available order number
	function getNewOrderNumber() {

		$query = new Query();

		$sql = "SELECT order_no FROM ".$this->db_orders." ORDER BY id DESC LIMIT 1";
		if($query->sql($sql)) {
			$last_order_no = $query->result(0, "order_no");
			$order_no = "WEB".(intval(preg_replace("/WEB/", "", $last_order_no))+1);
		}
		else {
			$order_no = "WEB1";
		}

		$sql = "INSERT INTO ".$this->db_orders." SET order_no='$order_no'";
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
	* Get total price for cart
	*
	* @return price object
	*/
	function getTotalCartPrice($cart_id) {

		$cart = $this->getCarts(array("cart_id" => $cart_id));
		$total_price = 0;
		$total_vat = 0;

		if($cart["items"]) {
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

				$quantity = $this->getProperty("quantity", "value");
				$item_id = $this->getProperty("item_id", "value");


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



	// Convert cart to order
	# /shop/newOrderFromCart
	function newOrderFromCart($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 2) {

			$query = new Query();
			$UC = new User();
			$IC = new Items();

			$cart_reference = $action[1];

			$cart = $this->getCart();

			// is cart registered and has content
			// and enforce a sanity check by matching cart far with REST param
//			print $cart_reference ." ==". $cart["cart_reference"];
			if($cart && $cart["user_id"] && $cart["items"] && $cart_reference == $cart["cart_reference"]) {

				// get new order number
				$order_no = $this->getNewOrderNumber();
				if($order_no) {

					// you can never create a cart for someone else, so ignore cart user_id
					$user_id = session()->value("user_id");

					// get data from cart
					$currency = $cart["currency"];
					$country = $cart["country"];

					$delivery_address_id = $cart["delivery_address_id"];
					$billing_address_id = $cart["billing_address_id"];


					// create base data update sql
					$sql = "UPDATE ".$this->db_orders." SET user_id=$user_id, country='$country', currency='$currency'";


					// add delivery address
					if($delivery_address_id) {
						$delivery_address = $UC->getAddresses(array("address_id" => $delivery_address_id));
						if($delivery_address) {
							$sql .= ", delivery_name='".$delivery_address["address_name"]."'";
							$sql .= ", delivery_att='".$delivery_address["att"]."'";
							$sql .= ", delivery_address1='".$delivery_address["address1"]."'";
							$sql .= ", delivery_address2='".$delivery_address["address2"]."'";
							$sql .= ", delivery_city='".$delivery_address["city"]."'";
							$sql .= ", delivery_postal='".$delivery_address["postal"]."'";
							$sql .= ", delivery_state='".$delivery_address["state"]."'";
							$sql .= ", delivery_country='".$delivery_address["country"]."'";
						}
					}

					// add billing address
					if($billing_address_id) {
						$billing_address = $UC->getAddresses(array("address_id" => $billing_address_id));
						if($billing_address) {
							$sql .= ", billing_name='".$billing_address["address_name"]."'";
							$sql .= ", billing_att='".$billing_address["att"]."'";
							$sql .= ", billing_address1='".$billing_address["address1"]."'";
							$sql .= ", billing_address2='".$billing_address["address2"]."'";
							$sql .= ", billing_city='".$billing_address["city"]."'";
							$sql .= ", billing_postal='".$billing_address["postal"]."'";
							$sql .= ", billing_state='".$billing_address["state"]."'";
							$sql .= ", billing_country='".$billing_address["country"]."'";
						}
					}

					// finalize sql
					$sql .= " WHERE order_no='$order_no'";

//					print $sql;
					// execute "create order"" query 
					if($query->sql($sql)) {


						// get the new order
						$order = $this->getOrders(array("order_no" => $order_no));

//						print "items";
//						print_r($cart["items"]);

						// add the items from the cart
						foreach($cart["items"] as $cart_item) {

							$quantity = $cart_item["quantity"];
							$item_id = $cart_item["item_id"];

							// get item details
							$item = $IC->getItem(array("id" => $item_id, "extend" => true));

							if($item) {

								// get best price for item
								$price = $this->getPrice($item_id, array("quantity" => $quantity, "currency" => $order["currency"], "country" => $order["country"]));
				//				print_r($price);

								$unit_price = $price["price"];
								$unit_vat = $price["vat"];
								$total_price = $unit_price * $quantity;
								$total_vat = $unit_vat * $quantity;

								$sql = "INSERT INTO ".$this->db_order_items." SET order_id=".$order["id"].", item_id=$item_id, name='".$item["name"]."', quantity=$quantity, unit_price=$unit_price, unit_vat=$unit_vat, total_price=$total_price, total_vat=$total_vat";
//								print $sql;

								// Add item to order
								$query->sql($sql);

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


						// delete cart
						$sql = "DELETE FROM $this->db_carts WHERE id = ".$cart["id"]." AND cart_reference = '".$cart["cart_reference"]."'";
			//			print $sql;
						$query->sql($sql);

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
	* get all orders
	* get orders by cart_id or cart_reference
	* get orders by order_id
	* get orders for user_id
	* get orders containing specific item_id
	*
	* TODO: implement timeranges
	*/
	function getOrders($_options=false) {

		$user_id = session()->value("user_id");

		// get specific order
		$order_id = false;
		$order_no = false;

		// get all orders containing item_id
		$item_id = false;

		// get all orders with status as specified
		$status = false;


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "order_id"          : $order_id            = $_value; break;
					case "order_no"          : $order_no            = $_value; break;

					case "item_id"           : $item_id             = $_value; break;

					case "status"            : $status              = $_value; break;

				}
			}
		}

		$query = new Query();

		// get specific order
		if($order_id || $order_no) {

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

				$order["order_status_text"] = $this->order_statuses[$order["status"]];
				$order["shipping_status_text"] = $this->shipping_statuses[$order["shipping_status"]];
				$order["payment_status_text"] = $this->payment_statuses[$order["payment_status"]];
				return $order;
			}

		}

		// all orders for user_id
		else {

			if($query->sql("SELECT * FROM ".$this->db_orders." WHERE user_id=$user_id".($status !== false ? " AND status=$status" : "")." ORDER BY order_no DESC")) {
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

		return false;
	}





	//
	// /**
	// * Create new cart
	// *
	// * @return Array of cart_id and cart_reference
	// */
	// // TODO: add user id to cart creation when users are implemented
	// function createCart() {
	//
	// 	global $page;
	//
	// 	$query = new Query();
	//
	// 	$query->checkDbExistance($this->db_carts);
	// 	$query->checkDbExistance($this->db_cart_items);
	// 	$query->checkDbExistance($this->db_orders);
	// 	$query->checkDbExistance($this->db_order_items);
	//
	// 	$currency = $page->currency();
	//
	// 	// find valid cart_reference
	// 	$cart_reference = randomKey(12);
	// 	while($query->sql("SELECT id FROM ".$this->db_carts." WHERE cart_reference = '".$cart_reference."'")) {
	// 		$cart_reference = randomKey(12);
	// 	}
	//
	// 	$query->sql("INSERT INTO ".$this->db_carts." VALUES(DEFAULT, '$cart_reference', '".$page->country()."', '".$currency["id"]."', DEFAULT, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
	// 	$cart_id = $query->lastInsertId();
	//
	// 	session()->value("cart", $cart_reference);
	// 	// save cookie
	// 	setcookie("cart", $cart_reference, time()+60*60*24*60, "/");
	//
	// 	return array($cart_id, $cart_reference);
	// }
	//
	//
	// /**
	// * Check cart_reference and create new cart if validation fails
	// * Optional create to auto create new cart in case of validation failure
	// *
	// * @return Array of cart_id and cart_reference if create option is true, otherwise true|false
	// */
	// function validateCart($cart_reference, $_options=false) {
	//
	// 	$create = false;
	//
	// 	if($_options !== false) {
	// 		foreach($_options as $_option => $_value) {
	// 			switch($_option) {
	// 				case "create"    : $create    = $_value; break;
	// 			}
	// 		}
	// 	}
	//
	// 	$query = new Query();
	//
	// 	// if cart_reference is in action, prioritize it
	// 	$cart_reference = stringOr($cart_reference, session()->value("cart"));
	//
	// 	print $cart_reference."<br>";
	//
	// 	// no cart_reference
	// 	// create new cart
	// 	if(!$cart_reference) {
	// 		if($create) {
	// 			list($cart_id, $cart_reference) = $this->createCart();
	// 		}
	// 		else {
	// 			return false;
	// 		}
	// 	}
	// 	// has cart_reference
	// 	else {
	//
	// 		// cart validation
	// 		$cart = $this->getCarts(array("cart_reference" => $cart_reference));
	//
	// 		// no cart was found
	// 		if(!$cart) {
	// 			if($create) {
	// 				list($cart_id, $cart_reference) = $this->createCart();
	// 			}
	// 			else {
	// 				return false;
	// 			}
	// 		}
	// 		// proceed with validation
	// 		else {
	//
	// 			// get cart_id for cart_reference
	// 			$cart_id = $cart["id"];
	//
	// 			// check if cart is associated with order and that order has not yet been paid
	// 			// otherwise create new cart
	// 			if($query->sql("SELECT * FROM ".$this->db_orders." WHERE cart_id = $cart_id AND status != 3")) {
	// 				if($create) {
	// 					list($cart_id, $cart_reference) = $this->createCart();
	// 				}
	// 				else {
	// 					return false;
	// 				}
	// 			}
	// 			else {
	// 				// update cart modified_at column
	// 				$query->sql("UPDATE ".$this->db_carts." SET modified_at = CURRENT_TIMESTAMP WHERE cart_id = $cart_id");
	// 			}
	// 		}
	//
	// 	}
	//
	// 	return array($cart_id, $cart_reference);
	// }
	//
	//
	// // add product to cart - 2 parameters minimum
	// // addItemToCart/#item_id#/[#cart_id#]
	// function addItemToCart($action) {
	//
	// 	if(count($action) >= 2) {
	//
	// 		$query = new Query();
	// 		$IC = new Items();
	//
	// 		$query->checkDbExistance($this->db_carts);
	// 		$query->checkDbExistance($this->db_cart_items);
	// 		$query->checkDbExistance($this->db_orders);
	// 		$query->checkDbExistance($this->db_order_items);
	//
	//
	// 		$item_id = $action[1];
	// 		list($cart_id, $cart_reference) = $this->validateCart($action[2], array("create" => true));
	//
	//
	// 		// add item or add one to existing item_id in cart
	// 		if($item_id && $IC->getItem(array("id" => $item_id))) {
	// 			// item already exists in cart, update quantity
	// 			if($query->sql("SELECT * FROM ".$this->db_cart_items." items WHERE items.cart_id = ".$cart_id." AND item_id = ".$item_id)) {
	// 				$cart_item = $query->result(0);
	//
	// 				// INSERT current quantity+1
	// 				$query->sql("UPDATE ".$this->db_cart_items." SET quantity = ".($cart_item["quantity"]+1)." WHERE id = ".$cart_item["id"]);
	// 			}
	// 			// just add item to cart
	// 			else {
	//
	// 				$query->sql("INSERT INTO ".$this->db_cart_items." VALUES(DEFAULT, '".$item_id."', '".$cart_id."', 1)");
	// 			}
	// 		}
	// 	}
	// }
	//
	//
	// // update cart quantity - 2 parameters minimum
	// // updateQuantity/#item_id#/[#cart_id#]
	// // quantity is posted
	// function updateQuantity($action) {
	//
	// 	if(count($action) >= 2) {
	//
	// 		$query = new Query();
	// 		$IC = new Items();
	//
	// 		$item_id = $action[1];
	// 		list($cart_id, $cart_reference) = $this->validateCart($action[2], array("create" => true));
	//
	// 		// Quantity
	// 		$quantity = getPost("quantity");
	//
	// 		// update quantity if item exists in cart
	// 		if($query->sql("SELECT * FROM ".$this->db_cart_items." as items WHERE items.cart_id = ".$cart_id." AND item_id = ".$item_id)) {
	// 			$cart_item = $query->result(0);
	//
	// 			if($quantity) {
	// 				// INSERT current quantity+1
	// 				$query->sql("UPDATE ".$this->db_cart_items." SET quantity = ".$quantity." WHERE id = ".$cart_item["id"]);
	// 			}
	// 			// no quantity value, must mean delete item from cart (quantity = 0)
	// 			else {
	// 				// DELETE
	// 				$query->sql("DELETE FROM ".$this->db_cart_items." WHERE id = ".$cart_item["id"]);
	// 			}
	// 		}
	// 	}
	// }
	//
	//
	// // delete cart - 2 parameters exactly
	// // /deleteCart/#cart_id#
	// function deleteCart($action) {
	//
	// 	if(count($action) == 2) {
	//
	// 		$query = new Query();
	// 		if($query->sql("DELETE FROM ".$this->db_carts." WHERE id = ".$action[1])) {
	// 			message()->addMessage("Cart deleted");
	// 			return true;
	// 		}
	//
	// 	}
	//
	// 	message()->addMessage("Cart could not be deleted - refresh your browser", array("type" => "error"));
	// 	return false;
	//
	// }
	//





	// /**
	// * Get total order price
	// *
	// * Calculate total order price by adding each order item + vat
	// *
	// * @return float total price
	// */
	// function getTotalOrderPrice($order_id) {
	// 	$order = $this->getOrders(array("order_id" => $order_id));
	// 	$total = 0;
	//
	// 	if($order["items"]) {
	// 		foreach($order["items"] as $item) {
	// 			$total += ($item["total_price"] + $item["total_vat"]);
	// 		}
	// 	}
	// 	return $total;
	// }





	// update order
	// if order does not exist, look for user matching email or mobile
	// 	if user is found
	// 	- what to do if email and mobile does not match
	// 	update user info
	// 	create order

	// update order
	// update order_items
	// check for address label match
	// update or add address(es)

	// BIG QUESTION 
	// - HOW TO HANDLE EXISTING USER WITHOUT LOGIN-REQUIREMENT
	// - HOW TO AVOID ABUSE (if I type a wrong email, should I then be able to overwrite account information?)

	// MAYBE SEPARATE CREATE ORDER FUNCTION TO MAKE IT LESS COMPLEX (SPLIT FORM IN TWO - BUT KEEP IN SAME PAGE)?



	//
	// // TODO: update all functionality
	// function updateOrder($action) {
	//
	// 	if(count($action) == 2) {
	//
	// 		$user = new User();
	// 		$IC = new Items();
	// 		$query = new Query();
	//
	//
	// 		$cart_reference = $action[1];
	//
	// 		// get cart
	// 		$cart = $this->getCarts(array("cart_reference" => $cart_reference));
	// 		$cart_id = $cart["id"];
	//
	//
	// 		// does values validate
	// 		if($cart_id && $this->validateList(array(
	// 			"billing_name",
	// 			"email",
	// 			"mobile",
	//
	// 			"billing_att",
	// 			"billing_address1",
	// 			"billing_address2",
	// 			"billing_city",
	// 			"billing_postal",
	// 			"billing_state",
	// 			"billing_country"
	// 		))) {
	//
	//
	// 			// separate delivery address
	// 			$delivery_address = getPost("delivery_address");
	// 			// validate delivery address content
	// 			if($delivery_address && !$this->validateList(array(
	// 				"delivery_name",
	// 				"delivery_att",
	// 				"delivery_address1",
	// 				"delivery_address2",
	// 				"delivery_city",
	// 				"delivery_postal",
	// 				"delivery_state",
	// 				"delivery_country"
	// 			))) {
	// 				return false;
	// 			}
	//
	// 			// continue with processing order data
	// 			$entities = $this->data_entities;
	//
	//
	// 			// make sure order tables exist
	// 			$query->checkDbExistance($this->db_orders);
	// 			$query->checkDbExistance($this->db_order_items);
	//
	//
	// 			// check for existing order
	// 			$order = $this->getOrders(array("cart_id" => $cart_id));
	// 			if($order) {
	// 				$user_id = $order["user_id"];
	// 				$order_id = $order["id"];
	// 			}
	// 			// if order do not exist
	// 			// create order, but look for existing user before creating new one for this order
	// 			else {
	//
	// 				// Find user, based on email or mobile
	// 				$user_id = $user->matchUsernames(array("email" => $entities["email"]["value"], "mobile" => $entities["mobile"]["value"]));
	//
	// 				// no matching user, create a new one
	// 				if(!$user_id) {
	//
	// 					$user_id = $user->createUser(array(
	// 						"nickname" => $entities["billing_name"]["value"],
	// 						"email"    => $entities["email"]["value"],
	// 						"mobile"   => $entities["mobile"]["value"]
	// 					));
	// 				}
	//
	//
	//
	//
	// 				// need user_id to continue order creation
	// 				// create order
	// 				if($user_id && $cart_id) {
	//
	// 					// Update status on cart to 2 to indicate it is now associated with order
	// 					// Update user_id
	// 					$query->sql("UPDATE ".$this->db_carts." SET status = 2, user_id = $user_id WHERE is = $cart_id");
	//
	// 					// create order
	// 					$order_no = randomKey(10);
	// 					// create order
	// 					$sql = "INSERT INTO ".$this->db_orders." SET order_no = '$order_no', user_id = $user_id, cart_id = $cart_id";
	// 					if($query->sql($sql)) {
	// 						$order_id = $query->lastInsertId();
	// 					}
	// 				}
	// 			}
	//
	//
	// 			// we should have enough info to update order
	// 			// this will update both user info and order
	// 			//
	// 			// TODO: there is a slight chance that this is not the intended action
	// 			// but it is likely more often so than not
	// 			// (could be situations where new information should be added to new user or addressses)
	// 			if($order_id && $user_id && $cart_id) {
	//
	// 				session()->value("order_id", $order_id);
	//
	//
	// 				// update user info
	// 				$user->updateUser($user_id, array(
	// 					"nickname" => $entities["billing_name"]["value"],
	// 					"email"    => $entities["email"]["value"],
	// 					"mobile"   => $entities["mobile"]["value"]
	// 				));
	//
	//
	// 				// update newsletters
	// 				$newsletters = getPost("newsletters");
	// 				$user->updateNewsletters($user_id, $newsletters);
	//
	//
	// 				// update/add addresses
	// 				// billing address
	// 				$billing_address_label = getPost("billing_address_label");
	// 				$billing_address = array(
	// 					"address_label" => $billing_address_label,
	// 					"address_name"  => $entities["billing_name"]["value"],
	// 					"att"           => $entities["billing_att"]["value"],
	// 					"address1"      => $entities["billing_address1"]["value"],
	// 					"address2"      => $entities["billing_address2"]["value"],
	// 					"city"          => $entities["billing_city"]["value"],
	// 					"postal"        => $entities["billing_postal"]["value"],
	// 					"state"         => $entities["billing_state"]["value"],
	// 					"country"       => $entities["billing_country"]["value"]
	// 				);
	//
	// 				// looking for matching address label
	// 				$billing_address_id = $user->matchAddress($user_id, array("address_label" => $billing_address_label));
	// 				// update existing billing address
	// 				if($billing_address_id) {
	// 					$user->updateAddress($billing_address_id, $billing_address);
	// 				}
	// 				// add new billing address
	// 				else {
	// 					$user->addAddress($user_id, $billing_address);
	// 				}
	//
	// 				// delivery address
	// 				// is delivery address specified
	// 				if($delivery_address) {
	// 					$delivery_address_label = getPost("delivery_address_label");
	// 					$delivery_address = array(
	// 						"address_label" => $delivery_address_label,
	// 						"address_name"  => $entities["delivery_name"]["value"],
	// 						"att"           => $entities["delivery_att"]["value"],
	// 						"address1"      => $entities["delivery_address1"]["value"],
	// 						"address2"      => $entities["delivery_address2"]["value"],
	// 						"city"          => $entities["delivery_city"]["value"],
	// 						"postal"        => $entities["delivery_postal"]["value"],
	// 						"state"         => $entities["delivery_state"]["value"],
	// 						"country"       => $entities["delivery_country"]["value"]
	// 					);
	//
	// 					// looking for matching address label
	// 					$delivery_address_id = $user->matchAddress($user_id, array("address_label" => $delivery_address_label));
	// 					// update existing delivery address
	// 					if($delivery_address_id) {
	// 						$user->updateAddress($delivery_address_id, $delivery_address);
	// 					}
	// 					// add new delivery address
	// 					else {
	// 						$user->addAddress($user_id, $delivery_address);
	// 					}
	// 				}
	//
	//
	// 				// update general order info
	// 				$sql = "UPDATE ".$this->db_orders." SET ";
	// 				$sql .= "country='".$cart["country"]."',";
	// 				$sql .= "currency='".$cart["currency"]."',";
	//
	// 				$sql .= "billing_name='".$entities["billing_name"]["value"]."',";
	// 				$sql .= "billing_att='".$entities["billing_att"]["value"]."',";
	// 				$sql .= "billing_address1='".$entities["billing_address1"]["value"]."',";
	// 				$sql .= "billing_address2='".$entities["billing_address2"]["value"]."',";
	// 				$sql .= "billing_city='".$entities["billing_city"]["value"]."',";
	// 				$sql .= "billing_postal='".$entities["billing_postal"]["value"]."',";
	// 				$sql .= "billing_state='".$entities["billing_state"]["value"]."',";
	// 				$sql .= "billing_country='".$entities["billing_country"]["value"]."',";
	//
	// 				if($delivery_address) {
	// 					$sql .= "delivery_name='".$entities["delivery_name"]["value"]."',";
	// 					$sql .= "delivery_att='".$entities["delivery_att"]["value"]."',";
	// 					$sql .= "delivery_address1='".$entities["delivery_address1"]["value"]."',";
	// 					$sql .= "delivery_address2='".$entities["delivery_address2"]["value"]."',";
	// 					$sql .= "delivery_city='".$entities["delivery_city"]["value"]."',";
	// 					$sql .= "delivery_postal='".$entities["delivery_postal"]["value"]."',";
	// 					$sql .= "delivery_state='".$entities["delivery_state"]["value"]."',";
	// 					$sql .= "delivery_country='".$entities["delivery_country"]["value"]."',";
	// 				}
	//
	// 				$sql .= "modified_at=CURRENT_TIMESTAMP";
	// 				$sql .= " WHERE id=$order_id";
	//
	// 				$query->sql($sql);
	//
	//
	// 				// remove existing order items
	// 				$sql = "DELETE FROM ".$this->db_order_items." WHERE order_id = $order_id";
	// 				$query->sql($sql);
	//
	// 				// update order items
	// 				if($cart["items"]) {
	// 					foreach($cart["items"] as $cart_item) {
	//
	// 						$item = $IC->getCompleteItem(array("id" => $cart_item["item_id"]));
	// 						$price = $IC->extendPrices($item["prices"], array("currency" => $cart["currency"]));
	//
	// 						$name = $item["name"];
	// 						$quantity = $cart_item["quantity"];
	//
	// 						// TODO: update price handling, when currencies are finalized
	// 						if($price) {
	// 							$item_price = $price["price"];
	// 							$item_vat = $price["vat_of_price"];
	// 							$total_price = $item_price * $quantity;
	// 							$total_vat = $item_vat * $quantity;
	// 						}
	// 						// no price - how did it end up a cart??
	// 						else {
	// 							$price = 0;
	// 							$vat = 0;
	// 							$total_price = 0;
	// 							$total_vat = 0;
	// 						}
	//
	// 						$sql = "INSERT INTO ".$this->db_order_items." SET ";
	// 						$sql .= "id=DEFAULT, ";
	// 						$sql .= "order_id=$order_id, ";
	// 						$sql .= "item_id=".$cart_item["item_id"].",";
	// 						$sql .= "name='".$name."',";
	// 						$sql .= "quantity='".$quantity."',";
	// 						$sql .= "price='".$item_price."',";
	// 						$sql .= "vat='".$item_vat."',";
	// 						$sql .= "total_price='".$total_price."',";
	// 						$sql .= "total_vat='".$total_vat."'";
	//
	// //						print $sql."<br>";
	// 						$query->sql($sql);
	// 					}
	// 				}
	//
	//
	// 				return true;
	// 			}
	// 		}
	// 	}
	//
	// 	return false;
	// }
	//

}

?>