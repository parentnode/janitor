<?php
$access_item["/list"] = true;

$access_item["/new"] = true;
$access_item["/save"] = "/new";

$access_item["/edit"] = true;
$access_item["/update"] = "/edit";

$access_item["/delete"] = true;
$access_item["/status"] = true;
$access_item["/cancel"] = true;


$access_item["/updateUsernames"] = true;
$access_item["/updateEmail"] = "/updateUsernames";
$access_item["/updateMobile"] = "/updateUsernames";
$access_item["/setPassword"] = true;


$access_item["/apitoken"] = true;
$access_item["/renewToken"] = "/apitoken";
$access_item["/disableToken"] = "/apitoken";

// USER ADDRESS INTERFACE
$access_item["/address"] = true;
$access_item["/addAddress"] = "/address";
$access_item["/updateAddress"] = "/address";
$access_item["/deleteAddress"] = "/address";

// USER MAILLIST INTERFACE
$access_item["/maillists"] = true;
$access_item["/addMaillist"] = "/maillists";
$access_item["/deleteMaillist"] = "/maillists";


// USER SUBSCRIPTION INTERFACE
$access_item["/subscription"] = true;
$access_item["/addSubscription"] = "/subscription";
$access_item["/updateSubscription"] = "/subscription";
$access_item["/deleteSubscription"] = "/subscription";

$access_item["/renewSubscriptions"] = true;


// USER CONTENT AND READSTATES INTERFACE
$access_item["/content"] = true;
$access_item["/orders"] = "/content";
$access_item["/readstates"] = "/content";
$access_item["/membership"] = "/content";


// MEMBERS INTERFACE
$access_item["/members"] = true;
$access_item["/updateMembership"] = "/members";
$access_item["/switchMembership"] = "/members";
$access_item["/upgradeMembership"] = "/members";
$access_item["/addNewhMembership"] = "/members";
$access_item["/cancelMembership"] = "/members";


// ACCESS INTERFACE
$access_item["/access"] = true;
$access_item["/updateAccess"] = "/access";

// USERGROUP INTERFACE
$access_item["/group"] = true;
$access_item["/deleteUserGroup"] = "/group";
$access_item["/saveUserGroup"] = "/group";
$access_item["/updateUserGroup"] = "/group";


// ONLINE INTERFACE
$access_item["/online"] = true;
$access_item["/unverified-usernames"] = true;
$access_item["/sendVerificationLink"] = true;
$access_item["/sendVerificationLinks"] = true;
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
				"page_title" => "User",
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

	// SUBSCRIPTIONS
	else if(preg_match("/^(subscription)$/", $action[0]) && count($action) > 1) {

		// SUBSCRIPTIONS LIST/EDIT/NEW
		if(preg_match("/^(new|list|edit)$/", $action[1])) {

			$page->page(array(
				"type" => "janitor",
				"page_title" => "Subscriptions",
				"templates" => "janitor/user/subscription/".$action[1].".php"
			));
			exit();
		}
	}

	// MEMBERSHIP
	else if(preg_match("/^(membership)$/", $action[0]) && count($action) > 1) {

		// MEMBER LIST/EDIT
		if(preg_match("/^(view|upgrade|switch|cancel|add)$/", $action[1])) {

			$page->page(array(
				"type" => "janitor",
				"page_title" => "Membership",
				"templates" => "janitor/user/membership/".$action[1].".php"
			));
			exit();
		}
	}

	// CONTENT/ORDERS/MAILLIST OVERVIEW
	else if(preg_match("/^(content|orders|maillists)$/", $action[0]) && count($action) > 1) {
		$page->page(array(
			"type" => "janitor",
			"page_title" => "User",
			"templates" => "janitor/user/".$action[0].".php"
		));
		exit();
	}

	// MEMBERS
	else if(preg_match("/^(members)$/", $action[0]) && count($action) > 1) {

		// MEMBER LIST/EDIT
		if(preg_match("/^(list|edit)$/", $action[1])) {

			$page->page(array(
				"type" => "janitor",
				"body_class" => "members", 
				"page_title" => "Members",
				"templates" => "janitor/user/members/".$action[1].".php"
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

	// ONLINE OVERVIEW
	else if(preg_match("/^(online)$/", $action[0]) && count($action) == 1) {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/user/online.php"
		));
		exit();
	}

	// NOT VERIFIED USER OVERVIEW
	else if(preg_match("/^(unverified-usernames)$/", $action[0]) && count($action) == 1) {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/user/unverified-usernames.php"
		));
		exit();
	}

	// Class interface
	else if(preg_match("/^renewSubscriptions$/", $action[0])) {

		$output = new Output();
		$output->screen($model->renewSubscriptions($action));
		exit();

	}

	
	else if($page->validateCsrfToken() && preg_match("/^(cancel)$/", $action[0])) {
		
		$result = $model->cancel($action);
		$output = new Output();
		// Cannot cancel account due to unpaid orders
		if(isset($result["error"]) && $result["error"] == "unpaid_orders") {
			$output->screen($result, ["type" => "error"]);
			exit();
		}
		else {
			$output->screen($result);
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
