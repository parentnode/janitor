<?php
//$access_item = false;
$access_item["/"] = true;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");

$action = $page->actions();
$HTML = new HTML();

$page->pageTitle("the Janitor @ kaestel.dk")
?>
<? $page->header(array("type" => "admin")) ?>

<div class="scene front">
	<h1><?= SITE_NAME ?> Admin</h1>


</div>

<? $page->footer(array("type" => "admin")) ?>