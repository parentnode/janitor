<?php
$access_item["/"] = true;
// SUPER SHOP INTERFACE

$access_item["/addPayment"] = true;
$access_item["/payment/new"] = "/addPayment";
$access_item["/order/payment/new"] = "/addPayment";

// $access_item["/addCart"] = true;
// $access_item["/addOrder"] = true;
// $access_item["/addToOrder"] =

if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();

include_once("classes/shop/supershop.class.php");
$model = new SuperShop();


$page->bodyClass("shop");
$page->pageTitle("Shop management");


if(is_array($action) && count($action)) {

	// ORDER
	if(count($action) > 1 && preg_match("/^(order)$/", $action[0])) {

		// LIST/EDIT/NEW
		if(preg_match("/^(list|edit)$/", $action[1])) {

			$page->page(array(
				"type" => "janitor",
				"body_class" => "orders", 
				"page_title" => "Orders",
				"templates" => "janitor/shop/order/".$action[1].".php"
			));
			exit();
		}
		// // ITEM
		// else if(count($action) > 2 && preg_match("/^(item)$/", $action[1])) {
		//
		// 	// NEW
		// 	if(preg_match("/^(new)$/", $action[2])) {
		//
		// 		$page->page(array(
		// 			"type" => "janitor",
		// 			"body_class" => "orders",
		// 			"page_title" => "Orders",
		// 			"templates" => "janitor/shop/order/item/".$action[2].".php"
		// 		));
		// 		exit();
		// 	}
		// }

		// PAYMENT
		else if(count($action) > 2 && preg_match("/^(payment)$/", $action[1])) {

			// NEW
			if(preg_match("/^(new)$/", $action[2])) {

				$page->page(array(
					"type" => "janitor",
					"body_class" => "orders", 
					"page_title" => "Orders",
					"templates" => "janitor/shop/order/payment/".$action[2].".php"
				));
				exit();
			}
		}

	}
	// CART
	else if(count($action) > 1 && preg_match("/^(cart)$/", $action[0])) {

		// EDIT
		if(count($action) == 3 && preg_match("/^edit$/", $action[1])) {

			$page->page(array(
				"type" => "janitor",
				"body_class" => "carts", 
				"page_title" => "Carts",
				"templates" => "janitor/shop/cart/edit.php"
			));
			exit();

		}
		// LIST/NEW
		else if(preg_match("/^(list|new)$/", $action[1])) {

			$page->page(array(
				"type" => "janitor",
				"body_class" => "carts", 
				"page_title" => "Carts",
				"templates" => "janitor/shop/cart/".$action[1].".php"
			));
			exit();
		}
		// ITEM
		else if(count($action) > 2 && preg_match("/^(item)$/", $action[1])) {

			// LIST/EDIT
			if(preg_match("/^(new)$/", $action[2])) {

				$page->page(array(
					"type" => "janitor",
					"body_class" => "carts", 
					"page_title" => "Carts",
					"templates" => "janitor/shop/cart/item/".$action[2].".php"
				));
				exit();
			}
		}

	}
	// PAYMENTS
	else if(preg_match("/^(payment)$/", $action[0])) {

		// LIST/NEW
		if(preg_match("/^(list|new)$/", $action[1])) {

			$page->page(array(
				"type" => "janitor",
				"body_class" => "payments", 
				"page_title" => "Payments",
				"templates" => "janitor/shop/payment/".$action[1].".php"
			));
			exit();
		}

	}

	// Class interface
	else if($page->validateCsrfToken() && preg_match("/[a-zA-Z]+/", $action[0])) {

		// check if custom function exists on User class
		if($model && method_exists($model, $action[0])) {

			$output = new Output();
			$output->screen($model->{$action[0]}($action));
			exit();
		}
	}

}

$page->page(array(
	"templates" => "pages/404.php"
));

?>
