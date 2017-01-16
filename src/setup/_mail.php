<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once("defaults/init.php");


$action = $page->actions();

$model = new Setup();


$page->bodyClass("mail");
$page->pageTitle("Janitor setup guide");


if($_SERVER["REQUEST_METHOD"] == "POST") {

	$output = new Output();
	$output->screen($model->updateMailSettings());
	exit();

}
else {

	$page->page(array(
		"type" => "setup",
		"templates" => "setup/mail.php"
		)
	);
	exit();

}

?>