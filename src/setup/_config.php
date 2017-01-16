<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once("defaults/init.php");

$action = $page->actions();

$model = new Setup();


$page->bodyClass("config");
$page->pageTitle("Janitor setup guide");


if($_SERVER["REQUEST_METHOD"] == "POST") {

	$output = new Output();
	$output->screen($model->updateConfigSettings());
	exit();

}
else {

	$page->page(array(
		"type" => "setup",
		"templates" => "setup/config.php"
		)
	);
	exit();

}

?>