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
class SuperShop extends Shop {

	/**
	*
	*/
	function __construct() {

		// order comment
		$this->addToModel("order_status", array(
			"type" => "text",
			"label" => "Order status",
			"hint_message" => "Progress of order",
			"error_message" => "Invalid status"
		));


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
			$IC = new Items();

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
			$IC = new Items();

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

		include_once("classes/users/superuser.class.php");
		$UC = new SuperUser();

		// get specific cart
		$cart_id = false;
		$cart_reference = false;
		$order_id = false;
		$order_no = false;

		// get all orders for $user_id
		$user_id = false;

		// get all orders containing $item_id
		$item_id = false;

		// get all orders width status as specified
		$status = false;


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "cart_id"           : $cart_id             = $_value; break;
					case "cart_reference"    : $cart_reference      = $_value; break;
					case "order_id"          : $order_id            = $_value; break;
					case "order_no"          : $order_no            = $_value; break;

					case "user_id"           : $user_id             = $_value; break;

					case "item_id"           : $item_id             = $_value; break;

					case "status"            : $status              = $_value; break;

					case "status"            : $status              = $_value; break;

				}
			}
		}

		$query = new Query();

		// get specific order
		if($order_id || $order_no) {

			if($order_id) {
				$sql = "SELECT * FROM ".$this->db_orders." WHERE id = ".$order_id." LIMIT 1";
			}
			else {
				$sql = "SELECT * FROM ".$this->db_orders." WHERE order_no = '".$order_no."' LIMIT 1";
			}

//			print $sql."<br>";
			if($query->sql($sql)) {
				$order = $query->result(0);
				$order["items"] = array();

				// get items for order
				if($query->sql("SELECT * FROM ".$this->db_order_items." as items WHERE items.order_id = ".$order["id"])) {
					$order["items"] = $query->results();
				}

				// is order mapped to user
				if($order["user_id"]) {
					// get user info
					$order["user"] = $UC->getUsers(array("user_id" => $order["user_id"]));
					$order["user"]["email"] = $UC->getUsernames(array("user_id" => $order["user_id"], "type" => "email"));
					$order["user"]["mobile"] = $UC->getUsernames(array("user_id" => $order["user_id"], "type" => "mobile"));
				}

				return $order;
			}

		}

		// all orders for user_id
		else if($user_id) {

			if($query->sql("SELECT * FROM ".$this->db_orders." WHERE user_id=$user_id".($status !== false ? " AND status=$status" : "")." ORDER BY order_no DESC")) {
				$orders = $query->results();

				foreach($orders as $i => $order) {
					$orders[$i]["items"] = false;
					if($query->sql("SELECT * FROM ".$this->db_order_items." WHERE order_id = ".$order["id"])) {
						$orders[$i]["items"] = $query->results();
					}
				}

				return $orders;
			}

		}

		// TODO: get all orders with item_id in it - not tested
		else if($item_id) {


			if($query->sql("SELECT order_id as id FROM ".$this->db_order_items." WHERE item_id = $item_id GROUP BY order_id")) {
				$orders = $query->results();

				return $orders;
			}

		}

		// return all orders
		else {

			$sql = "SELECT * FROM ".$this->db_orders.($status !== false ? " WHERE status=$status" : "")." ORDER BY order_no DESC";
//			print $sql;
			if($query->sql($sql)) {
				$orders = $query->results();

				foreach($orders as $i => $order) {

					$orders[$i]["items"] = array();

					if($query->sql("SELECT * FROM ".$this->db_order_items." WHERE order_id = ".$order["id"])) {
						$orders[$i]["items"] = $query->results();
					}

					// is order mapped to user
					if($order["user_id"]) {
						// get user info
						$orders[$i]["user"] = $UC->getUsers(array("user_id" => $order["user_id"]));
						$orders[$i]["user"]["email"] = $UC->getUsernames(array("user_id" => $order["user_id"], "type" => "email"));
						$orders[$i]["user"]["mobile"] = $UC->getUsernames(array("user_id" => $order["user_id"], "type" => "mobile"));
					}
				}

				return $orders;
			}

		}

		return false;
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



	// ORDERS
	
	# /janitor/admin/shop/newOrderFromCart/#card_reference#
	function newOrderFromCart($action) {

		
//		$this->getCarts();
//		$this->createOrder();
//		$this->addToOrder();


	}




	

	// add a new order for specified user
	# /janitor/admin/shop/addOrder
	function addOrder($action) {
		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("user_id"))) {

			$query = new Query();


			include_once("classes/users/superuser.class.php");
			$UC = new SuperUser();


			// get new order number
			$order_no = $this->getNewOrderNumber();
			if($order_no) {

				$user_id = $this->getProperty("user_id", "value");
				$currency = $this->getProperty("currency", "value");
				$country = $this->getProperty("country", "value");

				$delivery_address_id = $this->getProperty("delivery_address_id", "value");
				$billing_address_id = $this->getProperty("billing_address_id", "value");

				$order_comment = $this->getProperty("order_comment", "value");


				// set default currency if not passed
				if(!$currency) {
					$currency = $page->currency();
				}

				// set default country if not passed
				if(!$country) {
					$country = $page->country();
				}


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

				// add order comment
				if($order_comment) {
					$sql .= ", comment='$order_comment'";
				}


				// finalize sql
				$sql .= " WHERE order_no='$order_no'";

//				print $sql;
				if($query->sql($sql)) {
					message()->addMessage("Order added");
					return $this->getOrders(array("order_no" => $order_no));
				}
			}

			// order creation failed, remove unused order number
			$this->deleteOrderNumber($order_no);

		}

		message()->addMessage("Order could not be added", array("type" => "error"));
		return false;
	}

	// add a new order for specified user
	# /janitor/admin/shop/updateOrder/#order_id#
	function updateOrder($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 2) {

			$query = new Query();


			include_once("classes/users/superuser.class.php");
			$UC = new SuperUser();


			$order_id = $action[1];

			$order = $this->getOrders(array("order_id" => $order_id));

			if($order && $order["status"] == 0) {

				$currency = $this->getProperty("currency", "value");
				$country = $this->getProperty("country", "value");

				$delivery_address_id = $this->getProperty("delivery_address_id", "value");
				$billing_address_id = $this->getProperty("billing_address_id", "value");

				$order_comment = $this->getProperty("order_comment", "value");

				// create base data update sql
				$sql = "UPDATE ".$this->db_orders." SET modified_at=CURRENT_TIMESTAMP";

				// update currency
				if($currency) {
					$sql .= ", currency='$currency'";
				}

				// update country
				if($country) {
					$sql .= ", country='$country'";
				}

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

				// add order comment
				if($order_comment) {
					$sql .= ", comment='$order_comment'";
				}


				// finalize sql
				$sql .= " WHERE id=$order_id";

//				print $sql;
				if($query->sql($sql)) {

					// if country or currency was changed, price should be updated
					if($country || $currency) {

						// update order items price for new currency and country
						$updated_order = $this->getOrders(array("order_id" => $order_id));
						if($updated_order["items"]) {
							foreach($updated_order["items"] as $order_item) {
								// get best price for item
								$price = $this->getPrice($order_item["item_id"], array("quantity" => $order_item["quantity"], "currency" => $updated_order["currency"], "country" => $updated_order["country"]));
								if($price) {
									$unit_price = $price["price"];
									$unit_vat = $price["vat"];
									$total_price = $unit_price * $order_item["quantity"];
									$total_vat = $unit_vat * $order_item["quantity"];

									$sql = "UPDATE ".$this->db_order_items." SET unit_price=$unit_price, unit_vat=$unit_vat, total_price=$total_price, total_vat=$total_vat WHERE id = ".$order_item["id"]." AND order_id = ".$order_id;
				//					print $sql;
									$query->sql($sql);
								}
							}
						}
					}

					message()->addMessage("Order updated");
					return $this->getOrders(array("order_id" => $order_id));
				}

			}
		}

		message()->addMessage("Order could not be updated", array("type" => "error"));
		return false;

	}

	// Delete order (only allowed if ststus is still 0)
	# /janitor/admin/shop/addItemToOrder/#order_id#/#user_id#
	function deleteOrder($action) {

		// does values validate
		if(count($action) == 3) {

			$query = new Query();
			$order_id = $action[1];
			$user_id = $action[2];

			// check overstatus
			$order = $this->getOrders(array("order_id" => $order_id));
			if($order && $order["status"] == 0) {
				$sql = "DELETE FROM $this->db_orders WHERE id = $order_id AND user_id = $user_id";
	//			print $sql;
				if($query->sql($sql)) {
					message()->addMessage("Order deleted");
					return true;
				}
			}
		}

		message()->addMessage("Order could not deleted", array("type" => "error"));
		return false;
	}

	// Update order status
	# /janitor/admin/shop/updateOrderStatus/#order_id#/#user_id#
	function updateOrderStatus($action) {

		$this->getPostedEntities();

		// does values validate
		if(count($action) == 3) {

			$query = new Query();
			$order_id = $action[1];
			$user_id = $action[2];

			$status = $this->getProperty("order_status", "value");
			if(isset($this->order_statuses[$status])) {
				// create base data update sql
				$sql = "UPDATE ".$this->db_orders." SET status=$status WHERE id = $order_id AND user_id = $user_id";
				if($query->sql($sql)) {
					message()->addMessage("Order status updated");
					return true;
				}
			}
		}
		message()->addMessage("Order status could not be updated", array("type" => "error"));
		return false;
	}

	// ORDER ITEMS

	# /janitor/admin/shop/addToOrder/#order_id#/
	// Items and quantity in $_post
	function addToOrder($action) {

		if(count($action) > 1) {

			$order_id = $action[1];
			$order = $this->getOrders(array("order_id" => $order_id));

			// Get posted values to make them available for models
			$this->getPostedEntities();

			// does values validate
			if($order && $order["status"] == 0 && $this->validateList(array("quantity", "item_id"))) {

				$query = new Query();

				$quantity = $this->getProperty("quantity", "value");
				$item_id = $this->getProperty("item_id", "value");


				// check if item is already in order?
				if($order["items"] && arrayKeyValue($order["items"], "item_id", $item_id) !== false) {
					$existing_item_index = arrayKeyValue($order["items"], "item_id", $item_id);


					$existing_item = $order["items"][$existing_item_index];
					$existing_quantity = $existing_item["quantity"];
					$new_quantity = intval($quantity) + intval($existing_quantity);

					// get best price for item
					$price = $this->getPrice($item_id, array("quantity" => $new_quantity, "currency" => $order["currency"], "country" => $order["country"]));
//					print_r($price);

					$unit_price = $price["price"];
					$unit_vat = $price["vat"];
					$total_price = $unit_price * $new_quantity;
					$total_vat = $unit_vat * $new_quantity;

					$sql = "UPDATE ".$this->db_order_items." SET quantity=$new_quantity, unit_price=$unit_price, unit_vat=$unit_vat, total_price=$total_price, total_vat=$total_vat WHERE id = ".$existing_item["id"]." AND order_id = ".$order_id;
//					print $sql;
				}
				// insert new order item
				else {

					$IC = new Items();
					$item = $IC->getItem(array("id" => $item_id, "extend" => true));

					// get best price for item
					$price = $this->getPrice($item_id, array("quantity" => $quantity, "currency" => $order["currency"], "country" => $order["country"]));
	//				print_r($price);

					$unit_price = $price["price"];
					$unit_vat = $price["vat"];
					$total_price = $unit_price * $quantity;
					$total_vat = $unit_vat * $quantity;

					$sql = "INSERT INTO ".$this->db_order_items." SET order_id=$order_id, item_id=$item_id, name='".$item["name"]."', quantity=$quantity, unit_price=$unit_price, unit_vat=$unit_vat, total_price=$total_price, total_vat=$total_vat";
	//				print $sql;
				
				}

				if($query->sql($sql)) {

					// update modified at time
					$sql = "UPDATE ".$this->db_orders." SET modified_at=CURRENT_TIMESTAMP WHERE id = ".$order_id;
					$query->sql($sql);

					message()->addMessage("Item added to order");
					return true;

				}



			}

		}

		message()->addMessage("Order could not be added", array("type" => "error"));
		return false;
	}

	# /janitor/admin/shop/updateOrderQuantity/#order_id#/#order_item_id#
	// new quantity in $_POST
	function updateOrderItemQuantity($action) {

		if(count($action) == 3) {

			$order_id = $action[1];
			$order_item_id = $action[2];
			$order = $this->getOrders(array("order_id" => $order_id));

			// Get posted values to make them available for models
			$this->getPostedEntities();

			// does values validate
			if($order && $order["status"] == 0 && $this->validateList(array("quantity"))) {

				$query = new Query();

				$quantity = $this->getProperty("quantity", "value");


				// find item_id in order items?
				if($order["items"] && arrayKeyValue($order["items"], "id", $order_item_id) !== false) {
					$existing_item_index = arrayKeyValue($order["items"], "id", $order_item_id);
					$item_id = $order["items"][$existing_item_index]["item_id"];


					// get best price for item
					$price = $this->getPrice($item_id, array("quantity" => $quantity, "currency" => $order["currency"], "country" => $order["country"]));
	//					print_r($price);

					$unit_price = $price["price"];
					$unit_vat = $price["vat"];
					$total_price = $unit_price * $quantity;
					$total_vat = $unit_vat * $quantity;


					$sql = "UPDATE ".$this->db_order_items." SET quantity=$quantity, unit_price=$unit_price, unit_vat=$unit_vat, total_price=$total_price, total_vat=$total_vat WHERE id = ".$order_item_id." AND order_id = ".$order_id;
//					print $sql;
					if($query->sql($sql)) {

						message()->addMessage("Item quantity updated");
						return true;

					}

				}

				// update modified at time
				$sql = "UPDATE ".$this->db_orders." SET modified_at=CURRENT_TIMESTAMP WHERE id = ".$order_id;
				$query->sql($sql);

			}

		}

		message()->addMessage("Quantity could not be updated", array("type" => "error"));
		return false;
	}

	# /janitor/admin/shop/addToOrder/#order_id#/#order_item_id#
	// Items and quantity in $_post
	function deleteFromOrder($action) {

		if(count($action) > 2) {

			$order_id = $action[1];
			$order_item_id = $action[2];
			$order = $this->getOrders(array("order_id" => $order_id));

			if($order && $order["status"] == 0) {

				$query = new Query();
				$sql = "DELETE FROM ".$this->db_order_items." WHERE id = $order_item_id AND order_id = $order_id";
				// print $sql;
				if($query->sql($sql)) {
					message()->addMessage("Order item deleted");
					return true;
				}
			}
		}

		message()->addMessage("Order item could not deleted", array("type" => "error"));
		return false;
	}


}

?>