<?php
$access_item["/"] = true;
// $access_item = array();
// $access_item["/cart"] = true;
// $access_item["/cart/list"] = "cart";
// $access_item["/cart/view"] = "cart";
// $access_item["/deleteCart"] = "cart";
// 
// $access_item["/order"] = true;
// $access_item["/order/list"] = "order";
// $access_item["/order/view"] = "order";

if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");

// include the output class for output method support
include_once("class/system/output.class.php");

$action = $page->actions();

$model = new Shop();
$output = new Output();

// Add to cart handled


$page->bodyClass("cart");
$page->pageTitle("Carts");


if(is_array($action) && count($action)) {


	if(preg_match("/[a-zA-Z]+/", $action[0])) {

		// check if custom function exists on User class
		if($model && method_exists($model, $action[0])) {

			$output->screen($model->$action[0]($action));
			exit();
		}
	}

	// LIST CARTS
	// Requires exactly two parameters /enable/#item_id#
	if(count($action) == 2 && $action[0] == "cart" && $action[1] == "list") {

		$page->header(array("type" => "admin"));
		$page->template("admin/cart/list.php");
		$page->footer(array("type" => "admin"));
		exit();

	}
	// VIEW CART
	else if(count($action) == 3 && $action[0] == "cart" && $action[1] == "view") {

		$page->header(array("type" => "admin"));
		$page->template("admin/cart/view.php");
		$page->footer(array("type" => "admin"));
		exit();

	}
	// LIST ORDERS
	// Requires exactly two parameters /enable/#item_id#
	if(count($action) == 2 && $action[0] == "order" && $action[1] == "list") {

		$page->header(array("type" => "admin", "body_class" => "order", "page_title" => "Orders"));
		$page->template("admin/order/list.php");
		$page->footer(array("type" => "admin"));
		exit();

	}
	// VIEW ORDER
	else if(count($action) == 3 && $action[0] == "order" && $action[1] == "view") {

		$page->header(array("type" => "admin", "body_class" => "order", "page_title" => "Orders"));
		$page->template("admin/order/view.php");
		$page->footer(array("type" => "admin"));
		exit();

	}


}

$page->header();
$page->template("404.php");
$page->footer();

?>
