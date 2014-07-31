<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once("../../config/setup/init.php");

$action = $page->actions();

// include the output class for output method support
include_once("class/system/output.class.php");
$output = new Output();

$page->bodyClass("config");
$page->pageTitle("Janitor setup guide");


if($_SERVER["REQUEST_METHOD"] == "POST") {

	$project_path = getPost("project_path");

	$site_uid = getPost("site_uid");
	$site_name = getPost("site_name");
//	$site_url = getPost("site_url");
	$site_email = getPost("site_email");

	$_SESSION["project_path"] = $project_path;

	$_SESSION["site_uid"] = $site_uid;
	$_SESSION["site_name"] = $site_name;
//	$_SESSION["site_url"] = $site_url;
	$_SESSION["site_email"] = $site_email;


	if($site_uid && $site_name && $site_email && $project_path && file_exists($project_path)) {

		$_SESSION["CONFIG_INFO"] = true;

		$output->screen(true);
		exit();

	}
	else {
		message()->addMessage("Missing information or invalid path", array("type" => "error"));

		$_SESSION["CONFIG_INFO"] = false;
		$output->screen(false);
		exit();
	}

}
else {

	$page->header(array("type" => "setup"));
	$page->template("setup/config.php");
	$page->footer(array("type" => "setup"));
	exit();

}

?>