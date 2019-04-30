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
class SuperShopCore extends Shop {

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

		// order payment_status
		$this->addToModel("payment_status", array(
			"type" => "text",
			"label" => "Payment status",
			"hint_message" => "Progress of order payment",
			"error_message" => "Invalid status"
		));

		// order shipping status
		$this->addToModel("shipping_status", array(
			"type" => "text",
			"label" => "Shipping status",
			"hint_message" => "Progress of order shipping",
			"error_message" => "Invalid status"
		));


		parent::__construct();
	}




	// get carts - default all carts
	// - optional cart with cart_id or cart_reference
	// - optional carts for user_id
	// - optional multiple carts, based on content match
	function getCarts($_options=false) {

		include_once("classes/users/superuser.class.php");
		$UC = new SuperUser();

		// get specific cart
		$cart_id = false;
		$cart_reference = false;

		// get all carts containing $item_id
		$item_id = false;

		// get all carts containing $item_id
		$user_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "cart_reference"  : $cart_reference    = $_value; break;
					case "cart_id"         : $cart_id           = $_value; break;

					case "item_id"         : $item_id           = $_value; break;
					case "user_id"         : $user_id           = $_value; break;
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

				// is order mapped to user
				if($cart["user_id"]) {
					// get user info
					$cart["user"] = $UC->getUsers(array("user_id" => $cart["user_id"]));
					$cart["user"]["email"] = $UC->getUsernames(array("user_id" => $cart["user_id"], "type" => "email"));
					$cart["user"]["mobile"] = $UC->getUsernames(array("user_id" => $cart["user_id"], "type" => "mobile"));
				}

				return $cart;
			}

		}

		// get all carts with item_id in it
		// TODO: not tested
		else if($item_id) {

			$carts = false;

			$sql = "SELECT * FROM ".$this->db_cart_items." WHERE item_id = $item_id GROUP BY cart_id";

//			print $sql."<br>";
			if($query->sql($sql)) {
				$results = $query->results();
				foreach($results as $result) {
					$carts[] = $this->getCarts(array("cart_id" => $result["cart_id"]));
				}

				return $carts;
			}
		}

		// get all carts for user_id
		else if($user_id) {

			$sql = "SELECT * FROM ".$this->db_carts." WHERE user_id = $user_id ORDER BY id DESC";
//			print $sql."<br>";
			if($query->sql($sql)) {
				$carts = $query->results();

				foreach($carts as $i => $cart) {
					$carts[$i]["items"] = array();
					$carts[$i]["total_items"] = 0;

					if($query->sql("SELECT * FROM ".$this->db_cart_items." WHERE cart_id = ".$cart["id"])) {
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

		// get all carts
		else {
			
			$sql = "SELECT * FROM ".$this->db_carts." ORDER BY id DESC";
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

					// is order mapped to user
					if($cart["user_id"]) {
						// get user info
						$carts[$i]["user"] = $UC->getUsers(array("user_id" => $cart["user_id"]));
						$carts[$i]["user"]["email"] = $UC->getUsernames(array("user_id" => $cart["user_id"], "type" => "email"));
						$carts[$i]["user"]["mobile"] = $UC->getUsernames(array("user_id" => $cart["user_id"], "type" => "mobile"));
					}
				}

				return $carts;
			}
		}

		return false;
	}


	// Add a new cart with optional user, currency and country
	# /janitor/admin/shop/addCart
	function addCart($action) {
		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("user_id"))) {

			$query = new Query();

			$user_id = $this->getProperty("user_id", "value");
			$currency = $this->getProperty("currency", "value");
			$country = $this->getProperty("country", "value");

			$billing_address_id = $this->getProperty("billing_address_id", "value");
			$delivery_address_id = $this->getProperty("delivery_address_id", "value");

			// set user_id to default (NULL) if not passed
			if(!$user_id) {
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
			if($query->sql("INSERT INTO ".$this->db_carts." VALUES(DEFAULT, $user_id, '$cart_reference', '$country', '$currency', $delivery_address_id, $billing_address_id, CURRENT_TIMESTAMP, DEFAULT)")) {

				message()->addMessage("Cart added");
				return $this->getCarts(array("cart_reference" => $cart_reference));

			}

		}

		message()->addMessage("Cart could not be added", array("type" => "error"));
		return false;
	}

	// Update cart
	# /janitor/admin/shop/updateCart/#cart_reference#
	function updateCart($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 2) {

			$query = new Query();


			$cart_reference = $action[1];
			$cart = $this->getCarts(array("cart_reference" => $cart_reference));

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
				else if($delivery_address_id === "0") {
					$sql .= ", delivery_address_id=DEFAULT";
				}

				// update billing address
				if($billing_address_id) {
					$sql .= ", billing_address_id='$billing_address_id'";
				}
				else if($billing_address_id === "0") {
					$sql .= ", billing_address_id=DEFAULT";
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
				$sql .= " WHERE cart_reference='$cart_reference'";

//				print $sql;
				if($query->sql($sql)) {

					message()->addMessage("Cart updated");
					return $this->getCarts(array("cart_reference" => $cart_reference));
				}

			}
		}

		message()->addMessage("Cart could not be updated", array("type" => "error"));
		return false;

	}

	// Delete cart
	# /janitor/admin/shop/deleteCart/#cart_id#/#cart_reference#
	function deleteCart($action) {

		// does values validate
		if(count($action) == 3) {

			$query = new Query();
			$cart_id = $action[1];
			$cart_reference = $action[2];

			$sql = "DELETE FROM $this->db_carts WHERE id = $cart_id AND cart_reference = '$cart_reference'";
//			print $sql;
			if($query->sql($sql)) {
				message()->addMessage("Cart deleted");
				return true;
			}
		}

		message()->addMessage("Cart could not deleted", array("type" => "error"));
		return false;
	}


	// Add item to cart
	# /janitor/admin/shop/addToCart/#cart_reference#/
	// Items and quantity in $_post
	function addToCart($action) {

		if(count($action) > 1) {

			$cart_reference = $action[1];
			$cart = $this->getCarts(array("cart_reference" => $cart_reference));

			// Get posted values to make them available for models
			$this->getPostedEntities();

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
	//				print $sql;	
				}

				if($query->sql($sql)) {

					// update modified at time
					$sql = "UPDATE ".$this->db_carts." SET modified_at=CURRENT_TIMESTAMP WHERE id = ".$cart["id"];
					$query->sql($sql);

					message()->addMessage("Item added to cart");
					return $this->getCarts(array("cart_id" => $cart["id"]));

				}
			}
		}

		message()->addMessage("Item could not be added to cart", array("type" => "error"));
		return false;
	}

	// Update quantity of item in cart
	# /janitor/admin/shop/updateCartItemQuantity/#cart_reference#/#cart_item_id#
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
 
						message()->addMessage("Item quantity updated");
						return $item;

					}

				}

			}

		}

		message()->addMessage("Quantity could not be updated", array("type" => "error"));
		return false;
	}

	// Delete item from cart
	# /janitor/admin/shop/deleteFromCart/#cart_reference#/#cart_item_id#
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

					message()->addMessage("Cart item deleted");
					return $cart;
				}
			}
		}

		message()->addMessage("Cart item could not deleted", array("type" => "error"));
		return false;
	}



	// Convert cart to order
	# /janitor/admin/shop/newOrderFromCart/#card_id#/#cart_reference#
	function newOrderFromCart($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 3) {

			$query = new Query();


			$cart_id = $action[1];
			$cart_reference = $action[2];
			$cart = $this->getCarts(array("cart_reference" => $cart_reference));

			if($cart && $cart["user_id"] && $cart["items"]) {

				$_POST["user_id"] = $cart["user_id"];
				$_POST["country"] = $cart["country"];
				$_POST["currency"] = $cart["currency"];
				$_POST["delivery_address_id"] = $cart["delivery_address_id"];
				$_POST["billing_address_id"] = $cart["billing_address_id"];

				$order = $this->addOrder(array("addOrder"));
				unset($_POST);

				foreach($cart["items"] as $cart_item) {
					$_POST["quantity"] = $cart_item["quantity"];
					$_POST["item_id"] = $cart_item["item_id"];

					$this->addToOrder(array("addOrder", $order["id"]));
					unset($_POST);
				}

				$this->deleteCart(array("deleteCart", $cart_id, $cart_reference));

				message()->addMessage("Cart converted to order");
				return $order;

			}
		}

		message()->addMessage("Cart could not be converted to order", array("type" => "error"));
		return false;

	}




	/**
	* get orders
	*
	* get all orders
	* get order by order_id or order_no
	* get orders for user_id
	* get orders containing specific item_id
	* get all orders
	*/
	function getOrders($_options=false) {

		include_once("classes/users/superuser.class.php");
		$UC = new SuperUser();

		// get specific order
		$order_id = false;
		$order_no = false;

		// get all orders for user_id
		$user_id = false;

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

					case "user_id"           : $user_id             = $_value; break;

					case "item_id"           : $item_id             = $_value; break;
					case "itemtype"          : $itemtype            = $_value; break;

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
				$sql = "SELECT * FROM ".$this->db_order_items." as items WHERE items.order_id = ".$order["id"];
//				print $sql;
				if($query->sql($sql)) {
					$order["items"] = $query->results();
				}

				// is order mapped to user
				if($order["user_id"]) {
					// get user info
					$order["user"] = $UC->getUsers(array("user_id" => $order["user_id"]));
					$order["user"]["email"] = $UC->getUsernames(array("user_id" => $order["user_id"], "type" => "email"));
					$order["user"]["mobile"] = $UC->getUsernames(array("user_id" => $order["user_id"], "type" => "mobile"));
				}

				$order["order_status_text"] = $this->order_statuses[$order["status"]];
				$order["shipping_status_text"] = $this->shipping_statuses[$order["shipping_status"]];
				$order["payment_status_text"] = $this->payment_statuses[$order["payment_status"]];
				return $order;
			}

		}

		// orders for user_id
		else if($user_id) {

			// get all orders with certain itemtype in it
			if($itemtype) {

				$sql = "SELECT orders.* FROM ".$this->db_orders." as orders, ".$this->db_order_items." as order_items, ".UT_ITEMS." as items WHERE orders.user_id=$user_id".($status !== false ? " AND status=$status" : "")." AND order_items.order_id = orders.id AND items.itemtype = '$itemtype' AND order_items.item_id = items.id ORDER BY orders.id DESC";

				// LEFT JOIN ALTERNATIVE
//				$sql = "SELECT orders.* FROM ".$this->db_orders." as orders, ".$this->db_order_items." as order_items LEFT JOIN ".UT_ITEMS." ON order_items.item_id = ".UT_ITEMS.".id WHERE orders.user_id=$user_id".($status !== false ? " AND status=$status" : "")." AND order_items.order_id = orders.id AND order_items.itemtype = '$itemtype' AND order_items.item_id = items.id ORDER BY orders.id DESC";

//				print $sql;
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
			// get all orders for user
			else {

				$sql = "SELECT * FROM ".$this->db_orders." WHERE user_id=$user_id".($status !== false ? " AND status=$status" : "")." ORDER BY id DESC";
//				print $sql;
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

		}

		// get all orders with certain itemtype in it
		else if($itemtype) {

			$sql = "SELECT orders.* FROM ".$this->db_orders." as orders, ".$this->db_order_items." as order_items, ".UT_ITEMS." as items WHERE ".($status !== false ? "status=$status AND " : "")."order_items.order_id = orders.id AND items.itemtype = '$itemtype' AND order_items.item_id = items.id ORDER BY orders.id DESC";
//			print $sql;
			if($query->sql($sql)) {
				$orders = $query->results();

				foreach($orders as $i => $order) {
					$orders[$i]["items"] = array();
					if($query->sql("SELECT * FROM ".$this->db_order_items." WHERE order_id = ".$order["id"])) {
						$orders[$i]["items"] = $query->results();

						// is order mapped to user
						if($order["user_id"]) {
							// get user info
							$orders[$i]["user"] = $UC->getUsers(array("user_id" => $order["user_id"]));
							$orders[$i]["user"]["email"] = $UC->getUsernames(array("user_id" => $order["user_id"], "type" => "email"));
							$orders[$i]["user"]["mobile"] = $UC->getUsernames(array("user_id" => $order["user_id"], "type" => "mobile"));
						}
					}
				}

				return $orders;
			}

		}

		// get all orders with item_id in it
		else if($item_id) {

			$sql = "SELECT orders.* FROM ".$this->db_orders." as orders, ".$this->db_order_items." as items WHERE orders.id = items.order_id AND items.item_id = $item_id GROUP BY order_id";
			if($query->sql($sql)) {
				$orders = $query->results();

				foreach($orders as $i => $order) {
					$orders[$i]["items"] = array();
					if($query->sql("SELECT * FROM ".$this->db_order_items." WHERE order_id = ".$order["id"])) {
						$orders[$i]["items"] = $query->results();

						// is order mapped to user
						if($order["user_id"]) {
							// get user info
							$orders[$i]["user"] = $UC->getUsers(array("user_id" => $order["user_id"]));
							$orders[$i]["user"]["email"] = $UC->getUsernames(array("user_id" => $order["user_id"], "type" => "email"));
							$orders[$i]["user"]["mobile"] = $UC->getUsernames(array("user_id" => $order["user_id"], "type" => "mobile"));
						}
					}
				}


				return $orders;
			}

		}

		// return all orders
		else {

			$sql = "SELECT * FROM ".$this->db_orders.($status !== false ? " WHERE status=$status" : "")." ORDER BY id DESC";
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


	function getUnpaidOrders($_options=false) {
		
		// get all unpaid orders for user_id
		$user_id = false;

		// get all unpaid orders containing item_id
		$item_id = false;
		$itemtype = false;


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "user_id"           : $user_id             = $_value; break;

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
			else {
				$sql = "SELECT * FROM ".$this->db_orders." WHERE user_id=$user_id AND payment_status != 2 AND status != 3 ORDER BY id DESC";
//				print $sql;
				$query->sql($sql);
				return $query->results();
			}

		}


		else if($itemtype) {

			$sql = "SELECT orders.* FROM ".$this->db_orders." as orders, ".$this->db_order_items." as order_items, ".UT_ITEMS." as items WHERE orders.payment_status != 2 AND orders.status != 3 AND order_items.order_id = orders.id AND items.itemtype = '$itemtype' AND order_items.item_id = items.id ORDER BY orders.id DESC";
//			print $sql;
			$query->sql($sql);
			return $query->results();

		}

		// get all unpaid orders with item_id in it
		else if($item_id) {

			$sql = "SELECT orders.* FROM ".$this->db_orders." as orders, ".$this->db_order_items." as items WHERE orders.payment_status != 2 AND orders.status != 3 AND orders.id = items.order_id AND items.item_id = $item_id GROUP BY order_id";
//			print $sql;
			$query->sql($sql);
			return $query->results();

		}

		// return all unpaid orders
		else if(!isset($_options["user_id"]) && !isset($_options["item_id"]) && !isset($_options["itemtype"])) {

			$sql = "SELECT * FROM ".$this->db_orders." WHERE payment_status != 2 AND status != 3 ORDER BY id DESC";
//			print $sql;
			$query->sql($sql);
			return $query->results();

		}

		return false;
		
	}


	// /#controller#/sendPaymentReminder/#order_id#
	function sendPaymentReminder($action) {

		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("order_id"))) {

			global $page;
			$query = new Query();
			$query->checkDbExistence(SITE_DB.".user_log_payment_reminders");

			// user_id passed?
			$order_id = $this->getProperty("order_id", "value");
			$order = $this->getOrders(["order_id" => $order_id]);

//			print_r($order);

			if($order && $order["payment_status"] < 2) {

				include_once("classes/users/user.core.class.php");
				$UC = new User();

				// use current user as sender for this reminder
				$current_user = $UC->getUser();
				$total_order_price = $this->getTotalOrderPrice($order["id"]);

				mailer()->send(array(
					"from_current_user" => true,
					"values" => array(
						"FROM" => $current_user["nickname"],
						"NICKNAME" => $order["user"]["nickname"],
						"ORDER_NO" => $order["order_no"],
						"ORDER_PRICE" => formatPrice($total_order_price),
					),
					"recipients" => $order["user"]["email"],
					"template" => "payment_reminder",
					"track_clicks" => false
				));

				message()->addMessage("Reminder sent to ".$order["user"]["email"]);

				// Add to user log
				$sql = "INSERT INTO ".SITE_DB.".user_log_payment_reminders SET order_id = ".$order["id"].", user_id = ".$order["user_id"];
		//		print $sql;
				$query->sql($sql);

				return true;
			}

		}



		return false;
	}

	function getPaymentReminders($_options = false) {

		// get all count of orders with status
		$order_id = false;


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "order_id"             : $order_id              = $_value; break;
				}
			}
		}

		if($order_id) {
			$query = new Query();
			$sql = "SELECT * FROM ".SITE_DB.".user_log_payment_reminders WHERE order_id = $order_id ORDER BY created_at DESC";
	//		print $sql;
			$query->sql($sql);
			return $query->results();

		}

		return false;

	}


	// a shorthand function to get order count for UI
	function getOrderCount($_options = false) {

		// get all count of orders with status
		$status = false;


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "status"             : $status              = $_value; break;
				}
			}
		}

		$query = new Query();

		if($status !== false) {
			$sql = "SELECT id, count(*) as order_count FROM ".$this->db_orders." WHERE status=$status";
			if($query->sql($sql)) {
				return $query->result(0, "order_count");
			}
		}
		else {
			$sql = "SELECT id, count(*) as order_count FROM ".$this->db_orders;
			if($query->sql($sql)) {
				return $query->result(0, "order_count");
			}
		}

		return 0;
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
				$delivery_address = false;
				$billing_address_id = $this->getProperty("billing_address_id", "value");
				$billing_address = false;

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
					$user = $UC->getUsers(["user_id" => $user_id]);
					if($user["firstname"] && $user["lastname"]) {
						$sql .= ", billing_name='".prepareForDB($user["firstname"])." ".prepareForDB($user["lastname"])."'";
					}
					else {
						$sql .= ", billing_name='".prepareForDB($user["nickname"])."'";
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

					$page->addLog("SuperShop->addOrder: user_id:".$user_id.", order_no:".$order_no);

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

	// Update order
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
	# /janitor/admin/shop/deleteOrder/#order_id#/#user_id#
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



	// cancel order
	// changes order status to cancelled
	// cancels any subscriptions or memberships included in order
	# /#controller#/cancelOrder/#order_id#/#user_id#
	function cancelOrder($action) {

		// does values validate
		if(count($action) == 3) {

			$query = new Query();
			$IC = new Items();

			include_once("classes/users/superuser.class.php");
			$UC = new SuperUser();

			$order_id = $action[1];
			$user_id = $action[2];

			// check overstatus
			$order = $this->getOrders(array("order_id" => $order_id));
			if($order && ($order["status"] == 0 || $order["status"] == 1)) {

				// create creditnote
				$creditnote_no = $this->getNewCreditnoteNumber(["order_id" => $order_id]);
//				print_r($creditnote_no);

				// map order to new credit note no
				$sql = "UPDATE ".$this->db_cancelled_orders." SET order_id = $order_id WHERE creditnote_no = '".$creditnote_no."'";
				if($query->sql($sql)) {

					// get all subscriptions related to order
					$sql = "SELECT * FROM ".$UC->db_subscriptions." WHERE order_id = ".$order_id;
					if($query->sql($sql)) {
						$subscriptions = $query->results();

						// deal with subscriptions individually
						foreach($subscriptions as $subscription) {

							// is subscription related to membership
							$sql = "SELECT * FROM ".$UC->db_members." WHERE subscription_id = ".$subscription["id"]." LIMIT 1";
							if($query->sql($sql)) {
								$membership = $query->result(0);

								// cancel membership - also deletes related subscription
								$UC->cancelMembership(array("cancelMembership", $membership["user_id"], $membership["id"]));
							}
							// regular subscription
							else {

								// delete subscription
								$UC->deleteSubscription(array("deleteSubscription", $subscription["user_id"], $subscription["id"]));
							}
						}
					}

					// update order status and create credit note
					$sql = "UPDATE ".$this->db_orders." SET status = 3 WHERE id = ".$order_id." AND user_id = ".$user_id;
					if($query->sql($sql)) {


						global $page;
						$page->addLog("SuperShop->cancelOrder: $order_id ($user_id)");

						message()->addMessage("Order cancelled");
						return true;

					}

				}
			}

			// clean up
			$this->deleteCreditnoteNumber($creditnote_no);
		}

		message()->addMessage("Order could not cancelled", array("type" => "error"));
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

//				$item_price = false;
//				$item_name = false;

				$item_price = $this->getProperty("item_price", "value");
				$item_name = $this->getProperty("item_name", "value");

				// if($this->validate("item_price") && $this->validate("item_name")) {
				// }



				$IC = new Items();
				$item = $IC->getItem(array("id" => $item_id, "extend" => array("subscription_method" => true)));

				// print "item:<br>\n";
				// print_r($item);


				// only add item if it exists
				if($item) {

					// custom order item
					if($item_name && $item_price) {

						// get best price for item
						$price = $this->getPrice($item_id, array("quantity" => $quantity, "currency" => $order["currency"], "country" => $order["country"]));
		//				print_r($price);

						$unit_price = $item_price;
						$unit_vat = $item_price * (1 - (1 / (1 + ($price["vatrate"]/100))));

						$total_price = $unit_price * $quantity;
						$total_vat = $unit_vat * $quantity;

						$sql = "INSERT INTO ".$this->db_order_items." SET order_id=$order_id, item_id=$item_id, name='".$item_name."', quantity=$quantity, unit_price=$unit_price, unit_vat=$unit_vat, total_price=$total_price, total_vat=$total_vat";
//						print $sql;
					}
					// check if item is already in order?
					else if($order["items"] && arrayKeyValue($order["items"], "item_id", $item_id) !== false) {
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

						// get best price for item
						$price = $this->getPrice($item_id, array("quantity" => $quantity, "currency" => $order["currency"], "country" => $order["country"]));
		//				print_r($price);

						$unit_price = $price["price"];
						$unit_vat = $price["vat"];
						$total_price = $unit_price * $quantity;
						$total_vat = $unit_vat * $quantity;

						$sql = "INSERT INTO ".$this->db_order_items." SET order_id=$order_id, item_id=$item_id, name='".prepareForDB($item["name"])."', quantity=$quantity, unit_price=$unit_price, unit_vat=$unit_vat, total_price=$total_price, total_vat=$total_vat";
//						print $sql;				
					}


					if($query->sql($sql)) {

						// update modified at time
						$sql = "UPDATE ".$this->db_orders." SET modified_at=CURRENT_TIMESTAMP WHERE id = ".$order_id;
//						print $sql;
						$query->sql($sql);


						include_once("classes/users/superuser.class.php");
						$UC = new SuperUser();


						$membership = false;

						// item is membership (membership can only be added with relation subscription)
						if(SITE_MEMBERS && $item["itemtype"] == "membership") {

							// check if user already has membership
							$membership = $UC->getMembers(array("user_id" => $order["user_id"]));

							// membership does not exist
							if(!$membership) {
								// set values for adding membership
								$_POST["user_id"] = $order["user_id"];
								// add new membership
								$membership = $UC->addMembership(array("addMembership"));
								unset($_POST);
							}

						}


						// subscription method available for item
						if(SITE_SUBSCRIPTIONS && $item["subscription_method"]) {

							// set values for updating/creating subscription
							$_POST["order_id"] = $order["id"];
							$_POST["item_id"] = $item_id;
							$_POST["user_id"] = $order["user_id"];

							// if membership variable is not false
							// it means that membership exists and current type is membership
							// avoid creating new membership subscription
							if($membership && $membership["item"]) {

								// get the current membership subscription
								$subscription = $UC->getSubscriptions(array("item_id" => $membership["item"]["id"], "user_id" => $order["user_id"]));
							}
							else {

								// check if subscription already exists
								$subscription = $UC->getSubscriptions(array("item_id" => $item_id, "user_id" => $order["user_id"]));
							}


							// if subscription is for itemtype=membership
							// add/updateSubscription will also update subscription_id on membership 

							// update existing subscription
							if($subscription) {
								$subscription = $UC->updateSubscription(array("updateSubscription", $order["user_id"], $subscription["id"]));
							}
							// add new subscription
							else {
								$subscription = $UC->addSubscription(array("addSubscription"));
							}

							// clean up POST array
							unset($_POST);

						}



						global $page;
						$page->addLog("SuperShop->addToOrder: order_id:".$order["id"].", item_id:".$item_id);

						$this->validateOrder($order_id);

						message()->addMessage("Item added to order");
						return $this->getOrders(array("order_id" => $order_id));

					}

				}

			}

		}

		message()->addMessage("Item could not be added to order", array("type" => "error"));
		return false;
	}





	// update shipping status for order or item
	# /janitor/admin/shop/updateShippingStatus/#order_id#/[#order_item_id#]
	// should check if total order is shipped and update shipping status
	function updateShippingStatus($action) {

		if(count($action) > 1) {

			$order_id = $action[1];
			$order_item_id = false;
			$order = $this->getOrders(array("order_id" => $order_id));

			// order_item_id sent
			if(count($action) == 3) {
				$order_item_id = $action[2];
			}

			// Get posted values to make them available for models
			$this->getPostedEntities();

			// does values validate
			// pending, waiting, complete - cannot change shipment on cancelled order
			if($order && ($order["status"] == 0 || $order["status"] == 1 || $order["status"] == 2)) {

				$query = new Query();

				$shipped = getPost("shipped");

				// who shipped the item
				$shipped_by = "NULL";
				if($shipped) {
					$shipped_by = session()->value("user_id");
				}

				// was a single order_item_id specified
				// then only update this one item
				if($order_item_id) {

					// get current shipping status for item
					$sql = "SELECT shipped_by, item_id FROM ".$this->db_order_items." WHERE id = ".$order_item_id." AND order_id = ".$order_id;
//					print $sql;
					if($query->sql($sql)) {
						$current_shipping = $query->result(0, "shipped_by");
						$item_id = $query->result(0, "item_id");

						// check that item exists in order
						$order_item_index = arrayKeyValue($order["items"], "item_id", $item_id);

//						print "current_shipping:" . $current_shipping ." for ".$item_id;

						// changed state to "shipped" and was not already in this state
						// then invoke model->ship if it is available (it will perform digital delivery)
						if($shipped && !$current_shipping && $order_item_index !== false) {

//							print "shipping state changed for $item_id";
							$order_item = $order["items"][$order_item_index];
							$IC = new Items();
							$item = $IC->getItem(array("id" => $item_id, "extend" => array("subscription_method" => true)));
//							print "item:";
//							print_r($item);
							if($item) {
								$model = $IC->typeObject($item["itemtype"]);
								
								// does model have shipped callback
								if($model && method_exists($model, "shipped")) {
									$model->shipped($order_item, $order);
								}
							}

						}


						$sql = "UPDATE ".$this->db_order_items." SET shipped_by=$shipped_by WHERE id = ".$order_item_id." AND order_id = ".$order_id;
	//					print $sql;
						$query->sql($sql);

					}
				}
				// ship whole order
				else {
//					print "ship whole order";


					foreach($order["items"] as $order_item) {

						// get current shipping status for item
						$sql = "SELECT shipped_by, item_id FROM ".$this->db_order_items." WHERE id = ".$order_item["id"]." AND order_id = ".$order_id;
//						print $sql;
						if($query->sql($sql)) {
							$current_shipping = $query->result(0, "shipped_by");
							$item_id = $query->result(0, "item_id");

							// changed state to shipped and was not already in this state
							// then invoke model->shipped if it is available (it will perform)
//							print $shipped . "," . $current_shipping;
							if($shipped && !$current_shipping) {
								$IC = new Items();
								$item = $IC->getItem(array("id" => $order_item["item_id"]));
								
								if($item) {
									$model = $IC->typeObject($item["itemtype"]);
									// does model have shipped callback
									if($model && method_exists($model, "shipped")) {
										$model->shipped($order_item, $order);
									}
								}
							}

							$sql = "UPDATE ".$this->db_order_items." SET shipped_by=$shipped_by WHERE id = ".$order_item["id"]." AND order_id = ".$order_id;
							$query->sql($sql);
						}
					}
				}

				message()->addMessage("Order shipment updated");
				return $this->validateOrder($order_id);

			}
		}

		message()->addMessage("Shipment could not be updated", array("type" => "error"));
		return false;
	}


	// Validate statuses or order
	function validateOrder($order_id) {
		
		// Update order shipping status
		$order = $this->getOrders(array("order_id" => $order_id));

		// only validate non-cancelled orders
		if($order["status"] != 3) {

			$shipped_items = 0;
			$shipping_status = 0;
			foreach($order["items"] as $order_item) {
	//			print_r($order_item);
				if($order_item["shipped_by"]) {
					$shipped_items++;
				}
			}

			if($shipped_items == count($order["items"])) {
				$shipping_status = 2;
			}
			else if($shipped_items) {
				$shipping_status = 1;
			}
		
	//		print $shipped_items ."==". count($order["items"]);
			$query = new Query();
			$sql = "UPDATE ".$this->db_orders." SET shipping_status = $shipping_status WHERE id = ".$order_id;
			$query->sql($sql);

			message()->addMessage($this->shipping_statuses[$shipping_status]);


			// check payment status
			$payments = $this->getPayments(array("order_id" => $order_id));
			$total_order_price = $this->getTotalOrderPrice($order_id);
			$payment_status = 0;
			$total_payments = 0;
			if($payments) {
				foreach($payments as $payment) {
					$total_payments += $payment["payment_amount"];
				}
			}
			if($total_payments >= $total_order_price["price"]) {
				$payment_status = 2;
			}
			else if($total_payments) {
				$payment_status = 1;
			}

			$query = new Query();
			$sql = "UPDATE ".$this->db_orders." SET payment_status = $payment_status WHERE id = ".$order_id;
			$query->sql($sql);

			message()->addMessage($this->payment_statuses[$payment_status]);

		
	//		print($payments);

			// Update order status based on payment and shipment status
			// if shipped OR (partially) paid
			if($shipping_status != 0 || $payment_status != 0) {
				// Waiting
				$status = 1;

				// if shipped and paid
				if($shipping_status == 2 && $payment_status == 2) {
					// Complete
					$status = 2;
				}

				$sql = "UPDATE ".$this->db_orders." SET status = $status WHERE id = ".$order_id;
				$query->sql($sql);

			}

		}



		return $this->getOrders(array("order_id" => $order_id));
	}




	// PAYMENTS

	function getPayments($_options=false) {

		$order_id = false;

		// get all orders for user_id
		$user_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "order_id"          : $order_id            = $_value; break;

					case "user_id"           : $user_id             = $_value; break;
				}
			}
		}

		$query = new Query();

		// get specific order
		if($order_id) {

			$sql = "SELECT * FROM ".$this->db_payments." WHERE order_id = ".$order_id;

			// print $sql."<br>";
			if($query->sql($sql)) {
				return $query->results();
			}

		}
		else if($user_id) {

			$sql = "SELECT * FROM ".$this->db_payments." as payments, ".$this->db_orders." as orders WHERE payments.order_id = orders.id AND orders.user_id = ".$user_id . " ORDER BY payments.created_at, payments.id DESC";

			// print $sql."<br>";
			if($query->sql($sql)) {
				return $query->results();
			}

		}
		else {
			$sql = "SELECT * FROM ".$this->db_payments . " ORDER BY created_at, id DESC";

			// print $sql."<br>";
			if($query->sql($sql)) {
				
				return $query->results();

			}
		
		}

		return false;
	}


	// register manual payment
	// also updates order state
	# /#controller#/registerPayment
	function registerPayment($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 1 && $this->validateList(array("payment_amount", "payment_method", "order_id", "transaction_id"))) {


			$order_id = $this->getProperty("order_id", "value");
			$transaction_id = $this->getProperty("transaction_id", "value");
			$payment_amount = $this->getProperty("payment_amount", "value");
			$payment_method = $this->getProperty("payment_method", "value");

			$order = $this->getOrders(array("order_id" => $order_id));

			if($order) {

				$query = new Query();

				// update modified at time
				$sql = "INSERT INTO ".$this->db_payments." SET order_id=$order_id, currency='".$order["currency"]."', payment_amount=$payment_amount, transaction_id='$transaction_id', payment_method=$payment_method";
				if($query->sql($sql)) {
					$payment_id = $query->lastInsertId();
					$this->validateOrder($order["id"]);

					global $page;
					$page->addLog("SuperShop->addPayment: order_id:$order_id, payment_method:$payment_method, payment_amount:$payment_amount");

					message()->addMessage("Payment added");
					return $payment_id;
				
				}
			}

		}
		message()->addMessage("Payment could not be added", array("type" => "error"));
		return false;
	}


	// check if we have gateway user info (indicates we can charge)
	function canBeCharged($_options = false) {

		// get all orders for user_id
		$user_id = false;
		$gateway = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "gateway"           : $gateway             = $_value; break;
					case "user_id"           : $user_id             = $_value; break;
				}
			}
		}


		$customer_id = payments()->getGatewayUserId($user_id);
		if($customer_id) {
			return true;
		}

		//
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

	// Charge remaining order payment from gateway
	function chargeRemainingOrderPayment($action) {
	
		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 1 && $this->validateList(array("payment_method", "order_id"))) {

			global $page;

			$order_id = $this->getProperty("order_id", "value");
			$payment_method_id = $this->getProperty("payment_method", "value");

			$payment_method = $page->paymentMethods($payment_method_id);
			$order = $this->getOrders(array("order_id" => $order_id));

			if($order) {

				$total_order_price = $this->getTotalOrderPrice($order_id);
				$payments = $this->getPayments(array("order_id" => $order_id));

//				print_r($payments);

				$total_payments = 0;
				if($payments) {
					foreach($payments as $payment) {
						$total_payments += $payment["payment_amount"];
					
					}
				}

				$payment_amount = $total_order_price["price"]-$total_payments;

				$custom_order = $order;
				$custom_order["total_price"]["price"] = $payment_amount;
				
				// if partially paid already, add custom description to charge
				if($total_payments) {
					$custom_order["custom_description"] = $order["order_no"] . ", " . $order["comment"] . " (partial)";
				}

				// // if partially paid already, add custom description to charge
				// if($total_payments) {
				// 	$order["custom_description"] = $order["order_no"] . ", " . $order["comment"] . " (partial)";
				// }

//				print "should charge $payment_amount from ".$payment_method["gateway"];

				if(payments()->chargeUser($custom_order)) {

					message()->addMessage("Payment charged sucessfully.");
					return true;

				}
				else {

					message()->addMessage("Payment could not be charged.", array("type" => "error"));
					return false;

				}

			}
			else {

				message()->addMessage("Unknown order.", array("type" => "error"));
				return false;

			}

		}

//
// 				if($payment_method["gateway"] == "stripe") {
//
// 					include_once("classes/adapters/stripe.class.php");
// 					$GC = new JanitorStripe();
//
// 					$customer_id = $GC->getCustomerId($order["user_id"]);
//
// 					if($customer_id) {
// //						print "should charge $payment_amount from ".$payment_method["gateway"];
//
// 						$custom_order = $order;
// 						$custom_order["total_price"]["price"] = $payment_amount;
//
// 						// if partially paid already, add custom description to charge
// 						if($total_payments) {
// 							$custom_order["custom_description"] = $order["order_no"] . ", " . $order["comment"] . " (partial)";
// 						}
//
// 						if($GC->chargeCustomer($custom_order, $customer_id)) {
//
// 							message()->addMessage("Payment charged sucessfully.");
// 							return true;
//
// 						}
//
// 					}
// 					else {
//
// 						message()->addMessage("User does not have Stripe account.", array("type" => "error"));
// 						return false;
//
// 					}
//
// 				}
//
// 			}
//
// 		}

		message()->addMessage("Payment could not be charged", array("type" => "error"));
		return false;

	}


}

?>