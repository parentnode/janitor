<?php
$access_item["/list"] = true;
$access_item["/new"] = true;
$access_item["/addTag"] = "/new";
$access_item["/edit"] = true;
$access_item["/updateTag"] = "/edit";
$access_item["/deleteTag"] = true;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();
$model = new Tag();


$page->bodyClass("tags");
$page->pageTitle("Tags management");


if(is_array($action) && count($action)) {

	// LIST/EDIT ITEM
	if(preg_match("/^(new|list|edit)$/", $action[0])) {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/tag/".$action[0].".php"
		));
		exit();
	}

	// addTag returns to list, keep messages
	else if(security()->validateCsrfToken() && preg_match("/addTag/", $action[0])) {

		// check if custom function exists on User class
		if($model && method_exists($model, "API_".$action[0])) {

			$output = new Output();
			$output->screen($model->{"API_".$action[0]}($action), ["reset_messages" => false]);
			exit();
		}
	}
	// Class interface
	else if(security()->validateCsrfToken() && preg_match("/[a-zA-Z]+/", $action[0])) {

		// check if custom function exists on User class
		if($model && method_exists($model, "API_".$action[0])) {

			$output = new Output();
			$output->screen($model->{"API_".$action[0]}($action));
			exit();
		}
	}

}

$page->page(array(
	"templates" => "pages/404.php"
));

?>
