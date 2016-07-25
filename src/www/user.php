<?php
$access_item["/list"] = true;

$access_item["/new"] = true;
$access_item["/save"] = "/new";

$access_item["/edit"] = true;
$access_item["/update"] = "/edit";

$access_item["/updateUsernames"] = true;
$access_item["/updateEmail"] = "/updateUsernames";
$access_item["/updateMobile"] = "/updateUsernames";
$access_item["/setPassword"] = true;
$access_item["/renewToken"] = true;
$access_item["/disableToken"] = "/renewToken";

$access_item["/address"] = true;
$access_item["/address/new"] = "/address";
$access_item["/address/edit"] = "/address";
$access_item["/addAddress"] = "/address";
$access_item["/updateAddress"] = "/address";
$access_item["/deleteAddress"] = "/address";

$access_item["/newsletters"] = true;
$access_item["/newsletter/new"] = "/newsletters";
$access_item["/addNewsletter"] = "/newsletters";
$access_item["/deleteNewsletter"] = "/newsletters";


$access_item["/delete"] = true;
$access_item["/status"] = true;

$access_item["/access"] = true;
$access_item["/updateAccess"] = "/access";

$access_item["/group"] = true;
$access_item["/deleteUserGroup"] = "/group";
$access_item["/saveUserGroup"] = "/group";
$access_item["/updateUserGroup"] = "/group";

$access_item["/subscriber"] = true;

$access_item["/member"] = true;

// $access_item["/deleteSubscription"] = "/subscriber";
// $access_item["/disableSubscription"] = "/subscriber";
// $access_item["/subscriber/enable"] = "/subscriber";
//
// $access_item["/subscriber/edit"] = true;
// $access_item["/subscriber/update"] = true;

// $access_item["/saveUserGroup"] = "/group";
// $access_item["/updateUserGroup"] = "/group";



$access_item["/content"] = true;
$access_item["/orders"] = "/content";
$access_item["/subscriptions"] = "/content";
$access_item["/online"] = true;
$access_item["/flushUserSession"] = true;

if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();

include_once("classes/users/superuser.class.php");
$model = new SuperUser();


$page->bodyClass("user");
$page->pageTitle("User management");


if(is_array($action) && count($action)) {

	// LIST/EDIT/NEW
	if(preg_match("/^(list|edit|new)$/", $action[0])) {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/user/".$action[0].".php"
		));
		exit();
	}

	// ADDRESS
	else if(preg_match("/^(address)$/", $action[0]) && count($action) > 2) {

		// ADDRESS EDIT/NEW
		if(preg_match("/^(edit|new)$/", $action[1])) {

			$page->page(array(
				"type" => "janitor",
				"templates" => "janitor/user/address/".$action[1].".php"
			));
			exit();
		}
	}

	// GROUP
	else if(preg_match("/^(group)$/", $action[0]) && count($action) > 1) {

		// GROUP EDIT/
		if(preg_match("/^(list|edit|new)$/", $action[1])) {

			$page->page(array(
				"type" => "janitor",
				"templates" => "janitor/user/group/".$action[1].".php"
			));
			exit();
		}
	}

	// SUBSCRIBERS
	else if(preg_match("/^(subscriber)$/", $action[0]) && count($action) > 1) {

		// SUBSCRIBERS LIST/EDIT/NEW
		if(preg_match("/^(list|edit|new)$/", $action[1])) {

			$page->page(array(
				"type" => "janitor",
				"body_class" => "subscriber", 
				"page_title" => "Subscribers",
				"templates" => "janitor/user/subscriber/".$action[1].".php"
			));
			exit();
		}
	}

	// MEMBER
	else if(preg_match("/^(member)$/", $action[0]) && count($action) > 1) {

		// MEMBER LIST/EDIT/NEW
		if(preg_match("/^(list|edit|new)$/", $action[1])) {

			$page->page(array(
				"type" => "janitor",
				"body_class" => "member", 
				"page_title" => "Members",
				"templates" => "janitor/user/member/".$action[1].".php"
			));
			exit();
		}
	}

	// ORDER
	else if(preg_match("/^(order)$/", $action[0]) && count($action) > 1) {

		// ORDER LIST
		if(preg_match("/^(list)$/", $action[1])) {

			$page->page(array(
				"type" => "janitor",
				"body_class" => "order", 
				"page_title" => "Orders",
				"templates" => "janitor/user/order/".$action[1].".php"
			));
			exit();
		}
	}


	// ACCESS
	else if(preg_match("/^(access)$/", $action[0]) && count($action) > 1) {

		// ACCESS EDIT
		if($action[1] == "edit") {

			$page->page(array(
				"type" => "janitor",
				"body_class" => "usergroup", 
				"page_title" => "Access control management",
				"templates" => "janitor/user/group/access.php"
			));
			exit();

		}
	}

	// CONTENT, ORDERS OR SUBSCRIPTIONS OVERVIEW
	else if(preg_match("/^(content|orders|subscriptions)$/", $action[0]) && count($action) > 1) {
		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/user/".$action[0].".php"
		));
		exit();
	}

	// ONLINE OVERVIEW
	else if(preg_match("/^(online)$/", $action[0]) && count($action) == 1) {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/user/online.php"
		));
		exit();
	}

	// Class interface
	else if($page->validateCsrfToken() && preg_match("/[a-zA-Z]+/", $action[0])) {

		// check if custom function exists on User class
		if($model && method_exists($model, $action[0])) {

			$output = new Output();
			$output->screen($model->$action[0]($action));
			exit();
		}
	}

}

$page->page(array(
	"templates" => "pages/404.php"
));

?>
