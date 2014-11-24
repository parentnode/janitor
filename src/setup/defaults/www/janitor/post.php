<?php
$access_item["/"] = true;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();
$IC = new Items();
$itemtype = "post";
$model = $IC->typeObject($itemtype);


$page->bodyClass($itemtype);
$page->pageTitle("Posts");


if(is_array($action) && count($action)) {

	// LIST ITEM
	if(count($action) == 1 && $action[0] == "list") {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/".$itemtype."/list.php"
			)
		);
		exit();

	}
	// NEW ITEM
	else if(count($action) == 1 && $action[0] == "new") {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/".$itemtype."/new.php"
			)
		);
		exit();

	}
	// EDIT ITEM
	else if(count($action) == 2 && $action[0] == "edit") {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/".$itemtype."/edit.php"
			)
		);
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
	)
);

?>
