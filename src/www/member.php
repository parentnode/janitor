<?php
$access_item["/list"] = true;

$access_item["/new"] = true;
$access_item["/save"] = "/new";

// MEMBERS INTERFACE
$access_item["/"] = true;
$access_item["/updateMembership"] = "/";
$access_item["/switchMembership"] = "/";
$access_item["/upgradeMembership"] = "/";
$access_item["/addNewMembership"] = "/";
$access_item["/cancelMembership"] = "/";


if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");
$action = $page->actions();

if(count($action) == 1) {
	
	$model = new Member();
}
elseif(count($action) >= 2) {
	
	include_once("classes/users/supermember.class.php");
	$model = new SuperMember();
}


$page->bodyClass("members");
$page->pageTitle("Members");


if(is_array($action) && count($action)) {

	// LIST/EDIT
	if(preg_match("/^(list|edit)$/", $action[0])) {

		$page->page(array(
			"type" => "janitor",
			"body_class" => "members", 
			"page_title" => "Members",
			"templates" => "janitor/member/".$action[0].".php"
		));
		exit();
	}

	else if(preg_match("/^(view|upgrade|switch|cancel|add)$/", $action[0])) {
		
		$page->page(array(
			"type" => "janitor",
			"page_title" => "Membership",
			"templates" => "janitor/member/".$action[0].".php"
		));
		exit();
	}

	// Class interface
	else if(security()->validateCsrfToken() && preg_match("/[a-zA-Z]+/", $action[0])) {

		// check if custom function exists on Member class
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
