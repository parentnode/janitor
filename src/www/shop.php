<?php
$access_item["/"] = true;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();

$model = new Shop();
$output = new Output();

// Add to cart handled


$page->bodyClass("cart");
$page->pageTitle("Carts");


if(is_array($action) && count($action)) {


	if(preg_match("/[a-zA-Z]+/", $action[0]) && $page->validateCsrfToken()) {

		// check if custom function exists on User class
		if($model && method_exists($model, $action[0])) {

			$output->screen($model->$action[0]($action));
			exit();
		}
	}

	// LIST CARTS
	// Requires exactly two parameters /enable/#item_id#
	if(count($action) == 2 && $action[0] == "cart" && $action[1] == "list") {

		$page->header(array("type" => "janitor"));
		$page->template("janitor/cart/list.php");
		$page->footer(array("type" => "janitor"));
		exit();

	}
	// VIEW CART
	else if(count($action) == 3 && $action[0] == "cart" && $action[1] == "view") {

		$page->header(array("type" => "janitor"));
		$page->template("janitor/cart/view.php");
		$page->footer(array("type" => "janitor"));
		exit();

	}
	// LIST ORDERS
	// Requires exactly two parameters /enable/#item_id#
	if(count($action) == 2 && $action[0] == "order" && $action[1] == "list") {

		$page->header(array("type" => "janitor", "body_class" => "order", "page_title" => "Orders"));
		$page->template("janitor/order/list.php");
		$page->footer(array("type" => "janitor"));
		exit();

	}
	// VIEW ORDER
	else if(count($action) == 3 && $action[0] == "order" && $action[1] == "view") {

		$page->header(array("type" => "janitor", "body_class" => "order", "page_title" => "Orders"));
		$page->template("janitor/order/view.php");
		$page->footer(array("type" => "janitor"));
		exit();

	}


}

$page->header();
$page->template("404.php");
$page->footer();

?>
