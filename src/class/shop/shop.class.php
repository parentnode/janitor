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
class Shop extends Model {

	/**
	*
	*/
	function __construct() {

		// cart databases
		$this->db_carts = SITE_DB.".shop_carts";
		$this->db_cart_items = SITE_DB.".shop_cart_items";
		$this->db_orders = SITE_DB.".shop_orders";
		$this->db_order_items = SITE_DB.".shop_order_items";


		// TODO: needs to be updated throughout class
		// TODO: these are suggestions
		$this->order_statuses = array(0 => "New", 1 => "", 2 => "Charged", 3 => "Delivered", 4 => "Delivered and charged");
		$this->cart_statuses = array(0 => "Open", 1 => "Associated with order");


		// Nickname
		$this->addToModel("billing_name", array(
			"type" => "string",
			"label" => "Full name",
			"required" => true,
			"hint_message" => "Write your full name for the invoice", 
			"error_message" => "Name must be filled out"
		));
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


		// BILLING ADDRESS
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

		print "testHH";

		parent::__construct();
	}



	// get carts - default all carts
	// - optional cart with cart_id or cart_reference
	// - optional carts for user_id
	// - optional multiple carts, based on content match
	function getCarts($_options=false) {

		// get specific cart
		$cart_id = false;
		$cart_reference = false;

		// get all carts containing $item_id
		$item_id = false;

		// get all carts containing $item_id
		$user_id = false;

		// get carts based on timestamps
		$before = false;
		$after = false;

		$order = "status DESC, id DESC";

		// status
		$status = "";

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "cart_reference"  : $cart_reference    = $_value; break;
					case "cart_id"         : $cart_id           = $_value; break;

					case "item_id"         : $item_id           = $_value; break;
					case "user_id"         : $user_id           = $_value; break;

					case "before"          : $before            = $_value; break;
					case "after"           : $after             = $_value; break;

					case "order"           : $order             = $_value; break;

					case "status"          : $status            = $_value; break;
				}
			}
		}

		$query = new Query();


		// get specific cart
		if($cart_id || $cart_reference) {

			$cart = false;

			if($cart_id) {
				$sql = "SELECT * FROM ".$this->db_carts." WHERE id = $cart_id LIMIT 1";
			}
			else {
				$sql = "SELECT * FROM ".$this->db_carts." WHERE cart_reference = '$cart_reference' LIMIT 1";
			}

//			print $sql."<br>";
			if($query->sql($sql)) {
				$cart = $query->result(0);
				$cart["items"] = false;

				// get cart items
				$sql = "SELECT * FROM ".$this->db_cart_items." as items WHERE items.cart_id = ".$cart["id"];
//				print $sql."<br>";
				if($query->sql($sql)) {

					$cart["items"] = $query->results();

					// calculate total quantity
					$sql = "SELECT SUM(quantity) as q FROM ".$this->db_cart_items." as items WHERE items.cart_id = ".$cart["id"];
					if($query->sql($sql)) {
						$cart["total_quantity"] = $query->result(0, "q");
					}
					else {
						$cart["total_quantity"] = 0;
					}
				}
			}

			return $cart;
		}

		// get all carts with item_id in it
		else if($item_id) {

			$carts = false;

			$sql = "SELECT * FROM ".$this->db_cart_items." WHERE item_id = $item_id GROUP BY cart_id";

//			print $sql."<br>";
			if($query->sql($sql)) {
				$results = $query->results();
				foreach($results as $result) {
					$carts[] = $this->getCarts(array("cart_id" => $result["cart_id"]));
				}
			}
			return $carts;
		}

		// get all carts for user_id
		else if($user_id) {

			$carts = false;

			if($status) {
				$sql = "SELECT * FROM ".$this->db_carts." WHERE user_id = $user_id AND status = $status ORDER BY $order";
			}
			else {
				$sql = "SELECT * FROM ".$this->db_carts." WHERE user_id = $user_id ORDER BY $order";
			}

//			print $sql."<br>";
			if($query->sql($sql)) {
				$carts = $query->results();

				foreach($carts as $i => $cart) {
					$carts[$i]["items"] = false;
					if($query->sql("SELECT * FROM ".$this->db_cart_items." WHERE cart_id = ".$cart["id"])) {
						$carts[$i]["items"] = $query->results();
					}
				}
			}
			return $carts;
		}
		else {
			
			$carts = false;

			if($status) {
				$sql = "SELECT * FROM ".$this->db_carts."  WHERE status = $status ORDER BY $order";
			}
			else {
				$sql = "SELECT * FROM ".$this->db_carts." ORDER BY $order";
			}

//			print $sql."<br>";
			if($query->sql($sql)) {
				$carts = $query->results();

				foreach($carts as $i => $cart) {
					$carts[$i]["items"] = false;
					if($query->sql("SELECT * FROM ".$this->db_cart_items." WHERE cart_id = ".$cart["id"])) {
						$carts[$i]["items"] = $query->results();
					}
				}
			}
			return $carts;
		}
	}


	/**
	* Create new cart
	*
	* @return Array of cart_id and cart_reference
	*/
	// TODO: add user id to cart creation when users are implemented
	function createCart() {

		global $page;

		$query = new Query();

		$query->checkDbExistance($this->db_carts);
		$query->checkDbExistance($this->db_cart_items);
		$query->checkDbExistance($this->db_orders);
		$query->checkDbExistance($this->db_order_items);

		$currency = $page->currency();

		// find valid cart_reference
		$cart_reference = randomKey(12);
		while($query->sql("SELECT id FROM ".$this->db_carts." WHERE cart_reference = '".$cart_reference."'")) {
			$cart_reference = randomKey(12);
		}

		$query->sql("INSERT INTO ".$this->db_carts." VALUES(DEFAULT, '$cart_reference', '".$page->country()."', '".$currency["id"]."', DEFAULT, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
		$cart_id = $query->lastInsertId();

		session()->value("cart", $cart_reference);
		// save cookie
		setcookie("cart", $cart_reference, time()+60*60*24*60, "/");

		return array($cart_id, $cart_reference);
	}


	/**
	* Check cart_reference and create new cart if validation fails
	* Optional create to auto create new cart in case of validation failure
	*
	* @return Array of cart_id and cart_reference if create option is true, otherwise true|false
	*/
	function validateCart($cart_reference, $_options=false) {

		$create = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "create"    : $create    = $_value; break;
				}
			}
		}

		$query = new Query();

		// if cart_reference is in action, prioritize it
		$cart_reference = stringOr($cart_reference, session()->value("cart"));

		print $cart_reference."<br>";

		// no cart_reference
		// create new cart
		if(!$cart_reference) {
			if($create) {
				list($cart_id, $cart_reference) = $this->createCart();
			}
			else {
				return false;
			}
		}
		// has cart_reference
		else {

			// cart validation
			$cart = $this->getCarts(array("cart_reference" => $cart_reference));

			// no cart was found
			if(!$cart) {
				if($create) {
					list($cart_id, $cart_reference) = $this->createCart();
				}
				else {
					return false;
				}
			}
			// proceed with validation
			else {

				// get cart_id for cart_reference
				$cart_id = $cart["id"];

				// check if cart is associated with order and that order has not yet been paid
				// otherwise create new cart
				if($query->sql("SELECT * FROM ".$this->db_orders." WHERE cart_id = $cart_id AND status != 3")) {
					if($create) {
						list($cart_id, $cart_reference) = $this->createCart();
					}
					else {
						return false;
					}
				}
				else {
					// update cart modified_at column
					$query->sql("UPDATE ".$this->db_carts." SET modified_at = CURRENT_TIMESTAMP WHERE cart_id = $cart_id");
				}
			}

		}

		return array($cart_id, $cart_reference);
	}


	// add product to cart - 2 parameters minimum
	// addItemToCart/#item_id#/[#cart_id#]
	function addItemToCart($action) {

		if(count($action) >= 2) {

			$query = new Query();
			$IC = new Item();

			$query->checkDbExistance($this->db_carts);
			$query->checkDbExistance($this->db_cart_items);
			$query->checkDbExistance($this->db_orders);
			$query->checkDbExistance($this->db_order_items);


			$item_id = $action[1];
			list($cart_id, $cart_reference) = $this->validateCart($action[2], array("create" => true));


			// add item or add one to existing item_id in cart
			if($item_id && $IC->getItem(array("id" => $item_id))) {
				// item already exists in cart, update quantity
				if($query->sql("SELECT * FROM ".$this->db_cart_items." items WHERE items.cart_id = ".$cart_id." AND item_id = ".$item_id)) {
					$cart_item = $query->result(0);

					// INSERT current quantity+1
					$query->sql("UPDATE ".$this->db_cart_items." SET quantity = ".($cart_item["quantity"]+1)." WHERE id = ".$cart_item["id"]);
				}
				// just add item to cart
				else {

					$query->sql("INSERT INTO ".$this->db_cart_items." VALUES(DEFAULT, '".$item_id."', '".$cart_id."', 1)");
				}
			}
		}
	}


	// update cart quantity - 2 parameters minimum
	// updateQuantity/#item_id#/[#cart_id#]
	// quantity is posted
	function updateQuantity($action) {

		if(count($action) >= 2) {

			$query = new Query();
			$IC = new Item();

			$item_id = $action[1];
			list($cart_id, $cart_reference) = $this->validateCart($action[2], array("create" => true));

			// Quantity
			$quantity = getPost("quantity");

			// update quantity if item exists in cart
			if($query->sql("SELECT * FROM ".$this->db_cart_items." as items WHERE items.cart_id = ".$cart_id." AND item_id = ".$item_id)) {
				$cart_item = $query->result(0);

				if($quantity) {
					// INSERT current quantity+1
					$query->sql("UPDATE ".$this->db_cart_items." SET quantity = ".$quantity." WHERE id = ".$cart_item["id"]);
				}
				// no quantity value, must mean delete item from cart (quantity = 0)
				else {
					// DELETE
					$query->sql("DELETE FROM ".$this->db_cart_items." WHERE id = ".$cart_item["id"]);
				}
			}
		}
	}


	// delete cart - 2 parameters exactly
	// /deleteCart/#cart_id#
	function deleteCart($action) {

		if(count($action) == 2) {

			$query = new Query();
			if($query->sql("DELETE FROM ".$this->db_carts." WHERE id = ".$action[1])) {
				message()->addMessage("Cart deleted");
				return true;
			}

		}

		message()->addMessage("Cart could not be deleted - refresh your browser", array("type" => "error"));
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

		$user = new Simpleuser();

		// get specific cart
		$cart_id = false;
		$cart_reference = false;
		$order_id = false;

		// get all orders containing $user_id
		$user_id = false;

		// get all orders containing $item_id
		$item_id = false;

		// get carts based on timestamps
		$before = false;
		$after = false;

		$order = "status DESC, id DESC";

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "cart_id"           : $cart_id             = $_value; break;
					case "cart_reference"    : $cart_reference      = $_value; break;
					case "order_id"          : $order_id            = $_value; break;

					case "user_id"           : $user_id             = $_value; break;

					case "item_id"           : $item_id             = $_value; break;

					case "before"            : $before              = $_value; break;
					case "after"             : $after               = $_value; break;

					case "order"             : $order               = $_value; break;
				}
			}
		}

		$query = new Query();

		// get specific order
		if($order_id) {

			$order = false;

			$sql = "SELECT * FROM ".$this->db_orders." WHERE id = ".$order_id." LIMIT 1";
//			print $sql."<br>";
			if($query->sql($sql)) {
				$order = $query->result(0);
				$order["items"] = false;

				if($query->sql("SELECT * FROM ".$this->db_order_items." as items WHERE items.order_id = ".$order_id)) {
					$order["items"] = $query->results();
				}

				$order["email"] = $user->getUsernames(array("user_id" => $order["user_id"], "type" => "email"));
				$order["mobile"] = $user->getUsernames(array("user_id" => $order["user_id"], "type" => "mobile"));
				$order["newsletter"] = $user->getNewsletters(array("user_id" => $order["user_id"], "newsletter" => "general"));
			}

			return $order;
		}

		// get specific cart
		else if($cart_id || $cart_reference) {

			$order = false;

			// get cart_id from cart_reference
			if($cart_reference) {
				$sql = "SELECT id FROM ".$this->db_carts." WHERE cart_reference = '$cart_reference'";
//				print $sql."<br>";
				if($query->sql($sql)) {
					$cart_id = $query->result(0, "id");
				}
			}

			// cart_id available
			if($cart_id) {

				$sql = "SELECT * FROM ".$this->db_orders." WHERE cart_id = ".$cart_id." LIMIT 1";
	//			print $sql."<br>";
				if($query->sql($sql)) {
					$order = $query->result(0);
					$order["items"] = false;

					if($query->sql("SELECT * FROM ".$this->db_order_items." as items WHERE items.order_id = ".$order["id"])) {
						$order["items"] = $query->results();
					}

					$order["email"] = $user->getUsernames(array("user_id" => $order["user_id"], "type" => "email"));
					$order["mobile"] = $user->getUsernames(array("user_id" => $order["user_id"], "type" => "mobile"));
					$order["newsletter"] = $user->getNewsletters(array("user_id" => $order["user_id"], "newsletter" => "general"));

				}
			}

			return $order;
		}

		// all orders for user_id
		else if($user_id) {

			$orders = false;

			if($query->sql("SELECT * FROM ".$this->db_orders." WHERE user_id = $user_id ORDER BY $order")) {
				$orders = $query->results();

				foreach($orders as $i => $order) {
					$orders[$i]["items"] = false;
					if($query->sql("SELECT * FROM ".$this->db_order_items." WHERE order_id = ".$order["id"])) {
						$orders[$i]["items"] = $query->results();
					}
				}
			}

			return $orders;
		}

		// TODO: get all orders with item_id in it - not tested
		else if($item_id) {

			$orders = false;

			if($query->sql("SELECT order_id as id FROM ".$this->db_order_items." WHERE item_id = $item_id GROUP BY order_id")) {
				$orders = $query->results();
			}

			return $orders;
		}
		// return all orders
		else {

			$orders = false;

			if($query->sql("SELECT * FROM ".$this->db_orders." ORDER BY $order")) {
				$orders = $query->results();

				foreach($orders as $i => $order) {
					$orders[$i]["items"] = false;
					if($query->sql("SELECT * FROM ".$this->db_order_items." WHERE order_id = ".$order["id"])) {
						$orders[$i]["items"] = $query->results();
					}

					$orders[$i]["email"] = $user->getUsernames(array("user_id" => $order["user_id"], "type" => "email"));
					$orders[$i]["mobile"] = $user->getUsernames(array("user_id" => $order["user_id"], "type" => "mobile"));
					$orders[$i]["newsletter"] = $user->getNewsletters(array("user_id" => $order["user_id"], "newsletter" => "general"));

				}
			}

			return $orders;
		}

		return false;
	}


	/**
	* Get total order price
	*
	* Calculate total order price by adding each order item + vat
	*
	* @return float total price
	*/
	function getTotalOrderPrice($order_id) {
		$order = $this->getOrders(array("order_id" => $order_id));
		$total = 0;

		if($order["items"]) {
			foreach($order["items"] as $item) {
				$total += ($item["total_price"] + $item["total_vat"]);
			}
		}
		return $total;
	}





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




	// TODO: update all functionality
	function updateOrder($action) {

		if(count($action) == 2) {

			$user = new Simpleuser();
			$IC = new Item();
			$query = new Query();


			$cart_reference = $action[1];

			// get cart
			$cart = $this->getCarts(array("cart_reference" => $cart_reference));
			$cart_id = $cart["id"];


			// does values validate
			if($cart_id && $this->validateList(array(
				"billing_name",
				"email",
				"mobile",

				"billing_att",
				"billing_address1",
				"billing_address2",
				"billing_city",
				"billing_postal",
				"billing_state",
				"billing_country"
			))) {


				// separate delivery address
				$delivery_address = getPost("delivery_address");
				// validate delivery address content
				if($delivery_address && !$this->validateList(array(
					"delivery_name",
					"delivery_att",
					"delivery_address1",
					"delivery_address2",
					"delivery_city",
					"delivery_postal",
					"delivery_state",
					"delivery_country"
				))) {
					return false;
				}

				// continue with processing order data
				$entities = $this->data_entities;


				// make sure order tables exist
				$query->checkDbExistance($this->db_orders);
				$query->checkDbExistance($this->db_order_items);


				// check for existing order
				$order = $this->getOrders(array("cart_id" => $cart_id));
				if($order) {
					$user_id = $order["user_id"];
					$order_id = $order["id"];
				}
				// if order do not exist
				// create order, but look for existing user before creating new one for this order
				else {

					// Find user, based on email or mobile
					$user_id = $user->matchUsernames(array("email" => $entities["email"]["value"], "mobile" => $entities["mobile"]["value"]));

					// no matching user, create a new one
					if(!$user_id) {

						$user_id = $user->createUser(array(
							"nickname" => $entities["billing_name"]["value"],
							"email"    => $entities["email"]["value"],
							"mobile"   => $entities["mobile"]["value"]
						));
					}




					// need user_id to continue order creation
					// create order
					if($user_id && $cart_id) {

						// Update status on cart to 2 to indicate it is now associated with order
						// Update user_id
						$query->sql("UPDATE ".$this->db_carts." SET status = 2, user_id = $user_id WHERE is = $cart_id");

						// create order
						$order_no = randomKey(10);
						// create order
						$sql = "INSERT INTO ".$this->db_orders." SET order_no = '$order_no', user_id = $user_id, cart_id = $cart_id";
						if($query->sql($sql)) {
							$order_id = $query->lastInsertId();
						}
					}
				}


				// we should have enough info to update order
				// this will update both user info and order
				//
				// TODO: there is a slight chance that this is not the intended action
				// but it is likely more often so than not 
				// (could be situations where new information should be added to new user or addressses)
				if($order_id && $user_id && $cart_id) {

					session()->value("order_id", $order_id);


					// update user info
					$user->updateUser($user_id, array(
						"nickname" => $entities["billing_name"]["value"],
						"email"    => $entities["email"]["value"],
						"mobile"   => $entities["mobile"]["value"]
					));


					// update newsletters
					$newsletters = getPost("newsletters");
					$user->updateNewsletters($user_id, $newsletters);


					// update/add addresses
					// billing address
					$billing_address_label = getPost("billing_address_label");
					$billing_address = array(
						"address_label" => $billing_address_label,
						"address_name"  => $entities["billing_name"]["value"],
						"att"           => $entities["billing_att"]["value"],
						"address1"      => $entities["billing_address1"]["value"],
						"address2"      => $entities["billing_address2"]["value"],
						"city"          => $entities["billing_city"]["value"],
						"postal"        => $entities["billing_postal"]["value"],
						"state"         => $entities["billing_state"]["value"],
						"country"       => $entities["billing_country"]["value"]
					);

					// looking for matching address label
					$billing_address_id = $user->matchAddress($user_id, array("address_label" => $billing_address_label));
					// update existing billing address
					if($billing_address_id) {
						$user->updateAddress($billing_address_id, $billing_address);
					}
					// add new billing address
					else {
						$user->addAddress($user_id, $billing_address);
					}

					// delivery address
					// is delivery address specified
					if($delivery_address) {
						$delivery_address_label = getPost("delivery_address_label");
						$delivery_address = array(
							"address_label" => $delivery_address_label,
							"address_name"  => $entities["delivery_name"]["value"],
							"att"           => $entities["delivery_att"]["value"],
							"address1"      => $entities["delivery_address1"]["value"],
							"address2"      => $entities["delivery_address2"]["value"],
							"city"          => $entities["delivery_city"]["value"],
							"postal"        => $entities["delivery_postal"]["value"],
							"state"         => $entities["delivery_state"]["value"],
							"country"       => $entities["delivery_country"]["value"]
						);

						// looking for matching address label
						$delivery_address_id = $user->matchAddress($user_id, array("address_label" => $delivery_address_label));
						// update existing delivery address
						if($delivery_address_id) {
							$user->updateAddress($delivery_address_id, $delivery_address);
						}
						// add new delivery address
						else {
							$user->addAddress($user_id, $delivery_address);
						}
					}


					// update general order info
					$sql = "UPDATE ".$this->db_orders." SET ";
					$sql .= "country='".$cart["country"]."',";
					$sql .= "currency='".$cart["currency"]."',";

					$sql .= "billing_name='".$entities["billing_name"]["value"]."',";
					$sql .= "billing_att='".$entities["billing_att"]["value"]."',";
					$sql .= "billing_address1='".$entities["billing_address1"]["value"]."',";
					$sql .= "billing_address2='".$entities["billing_address2"]["value"]."',";
					$sql .= "billing_city='".$entities["billing_city"]["value"]."',";
					$sql .= "billing_postal='".$entities["billing_postal"]["value"]."',";
					$sql .= "billing_state='".$entities["billing_state"]["value"]."',";
					$sql .= "billing_country='".$entities["billing_country"]["value"]."',";

					if($delivery_address) {
						$sql .= "delivery_name='".$entities["delivery_name"]["value"]."',";
						$sql .= "delivery_att='".$entities["delivery_att"]["value"]."',";
						$sql .= "delivery_address1='".$entities["delivery_address1"]["value"]."',";
						$sql .= "delivery_address2='".$entities["delivery_address2"]["value"]."',";
						$sql .= "delivery_city='".$entities["delivery_city"]["value"]."',";
						$sql .= "delivery_postal='".$entities["delivery_postal"]["value"]."',";
						$sql .= "delivery_state='".$entities["delivery_state"]["value"]."',";
						$sql .= "delivery_country='".$entities["delivery_country"]["value"]."',";
					}

					$sql .= "modified_at=CURRENT_TIMESTAMP";
					$sql .= " WHERE id=$order_id";

					$query->sql($sql);


					// remove existing order items
					$sql = "DELETE FROM ".$this->db_order_items." WHERE order_id = $order_id";
					$query->sql($sql);

					// update order items
					if($cart["items"]) {
						foreach($cart["items"] as $cart_item) {

							$item = $IC->getCompleteItem(array("id" => $cart_item["item_id"]));
							$price = $IC->extendPrices($item["prices"], array("currency" => $cart["currency"]));

							$name = $item["name"];
							$quantity = $cart_item["quantity"];

							// TODO: update price handling, when currencies are finalized
							if($price) {
								$item_price = $price["price"];
								$item_vat = $price["vat_of_price"];
								$total_price = $item_price * $quantity;
								$total_vat = $item_vat * $quantity;
							}
							// no price - how did it end up a cart??
							else {
								$price = 0;
								$vat = 0;
								$total_price = 0;
								$total_vat = 0;
							}

							$sql = "INSERT INTO ".$this->db_order_items." SET ";
							$sql .= "id=DEFAULT, ";
							$sql .= "order_id=$order_id, ";
							$sql .= "item_id=".$cart_item["item_id"].",";
							$sql .= "name='".$name."',";
							$sql .= "quantity='".$quantity."',";
							$sql .= "price='".$item_price."',";
							$sql .= "vat='".$item_vat."',";
							$sql .= "total_price='".$total_price."',";
							$sql .= "total_vat='".$total_vat."'";
					
	//						print $sql."<br>";
							$query->sql($sql);
						}
					}


					return true;
				}
			}
		}

		return false;
	}


}

?>