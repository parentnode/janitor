<?php
/**
* @package janitor.cart
*/



define("UT_CARTS",              SITE_DB.".carts");                         // Carts
define("UT_CART_ITEMS",         SITE_DB.".cart_items");                    // Cart items

/**
* Cart helper class
*
*/

class Cart {

	/**
	*
	*/
	function __construct() {

	}



	// get carts
	// - optional multiple carts, based on content match
	function getCarts($_options=false) {

		// get specific cart
		$cart_id = false;

		// get all carts containing $item_id
		$item_id = false;

		// get carts based on timestamps
		$before = false;
		$after = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "cart_id"  : $cart_id    = $_value; break;
					case "item_id"  : $item_id    = $_value; break;
					case "before"   : $before     = $_value; break;
					case "after"    : $after      = $_value; break;
				}
			}
		}

		$query = new Query();

		// get specific cart
		if($cart_id) {

			if($query->sql("SELECT * FROM ".UT_CARTS." as carts WHERE carts.id = ".$cart_id." LIMIT 1")) {
				$cart = $query->result(0);
				$cart["items"] = false;

				if($query->sql("SELECT * FROM ".UT_CART_ITEMS." as items WHERE items.cart_id = ".$cart_id)) {
					$cart["items"] = $query->results();
				}
				return $cart;
			}
		}

		// get all carts with item_id in it
		if($item_id) {

			if($query->sql("SELECT * FROM ".UT_CART_ITEMS." as items WHERE items.item_id = $item_id GROUP BY cart_id")) {
				$results = $query->results();
				$carts = false;
				foreach($results as $result) {
					$carts[] = $this->getCarts(array("cart_id" => $result["cart_id"]));
				}
				return $carts;
			}
		}

		// return cart all carts
		if(!$cart_id && !$item_id) {
			if($query->sql("SELECT * FROM ".UT_CARTS." as items")) {
				$carts = $query->results();

				foreach($carts as $i => $cart) {
					$carts[$i]["items"] = false;
					if($query->sql("SELECT * FROM ".UT_CART_ITEMS." as items WHERE items.cart_id = ".$cart["id"])) {
						$carts[$i]["items"] = $query->results();
					}
				}
				return $carts;
			}
		}

		return false;
	}


	// if no cart in session - create new cart

	// add product to cart - 4 parameters exactly
	// /cart-controller/addToCart/
	// posting: item_id, quantity, cart_id
	function addToCart() {

		$query = new Query();
		$IC = new Item();
		global $page;

		// if cart_id from form, prioritize it
		$cart_id = stringOr(getPost("cart_id"), Session::value("cart_id"));

		// no cart id - create new cart
		if(!$cart_id) {
			// TODO: add user id to cart creation when users are implemented
			$query->sql("INSERT INTO ".UT_CARTS." VALUES(DEFAULT, '".$page->country()."', '".$page->currency()."', DEFAULT, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
			$cart_id = $query->lastInsertId();
			Session::value("cart_id", $cart_id);
		}
		// update cart modified at
		else {
			$query->sql("UPDATE ".UT_CARTS." SET modified_at = CURRENT_TIMESTAMP WHERE id = ".$cart_id);
		}

		$item_id = getPost("item_id");
		// add item to cart, if item exists
		if($item_id && $IC->getItem($item_id)) {

			// item already exists in cart, update quantity
			if($query->sql("SELECT * FROM ".UT_CART_ITEMS." items WHERE items.cart_id = ".$cart_id." AND item_id = ".$item_id)) {
				$cart_item = $query->result(0);

				// INSERT current quantity+1
				$query->sql("UPDATE ".UT_CART_ITEMS." SET quantity = ".($cart_item["quantity"]+1)." WHERE id = ".$cart_item["id"]);
			}
			// just add item to cart
			else {

				$query->sql("INSERT INTO ".UT_CART_ITEMS." VALUES(DEFAULT, '".$item_id."', '".$cart_id."', 1)");
			}
		}
	}


	// update cart - 4 parameters minimum
	// /cart-controller/updateQuantity/
	function updateQuantity() {

		$query = new Query();
		$IC = new Item();
		global $page;

		// if cart_id from form, prioritize it
		$cart_id = stringOr(getPost("cart_id"), Session::value("cart_id"));

		$item_id = getPost("item_id");
		$quantity = getPost("quantity");

		// update quantity if item exists in cart
		if($query->sql("SELECT * FROM ".UT_CART_ITEMS." items WHERE items.cart_id = ".$cart_id." AND item_id = ".$item_id)) {
			$cart_item = $query->result(0);

			if($quantity) {
				// INSERT current quantity+1
				$query->sql("UPDATE ".UT_CART_ITEMS." SET quantity = ".$quantity." WHERE id = ".$cart_item["id"]);
			}
			else {
				// DELETE
				$query->sql("DELETE FROM ".UT_CART_ITEMS." WHERE id = ".$cart_item["id"]);
			}
		}
	}

	// delete cart - 3 parameters exactly
	// /cart-controller/deleteCart/#item_id#
	function deleteCart() {
		
	}

}

?>