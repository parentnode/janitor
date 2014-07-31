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

$page->bodyClass("paths");
$page->pageTitle("Janitor setup guide");


if($_SERVER["REQUEST_METHOD"] == "POST") {

	$framework_path = getPost("framework_path");
	$local_path = getPost("local_path");
	
//	print file_exists($local_path) . ", " . file_exists($framework_path);
	if($framework_path && file_exists($framework_path)) {
		$_SESSION["FRAMEWORK_PATH"] = $framework_path;
	}
	else {
		message()->addMessage("Invalid framework path", array("type" => "error"));
		$_SESSION["FRAMEWORK_PATH"] = false;
	}

	if($local_path && file_exists($local_path)) {
		$_SESSION["LOCAL_PATH"] = $local_path;
	}
	else {
		message()->addMessage("Invalid local path", array("type" => "error"));
		$_SESSION["LOCAL_PATH"] = false;
	}

//	print $_SESSION["LOCAL_PATH"]." , ".$_SESSION["FRAMEWORK_PATH"];

	// state of path verification
	if($_SESSION["LOCAL_PATH"] && $_SESSION["FRAMEWORK_PATH"]) {

//		print "updated";
		$_SESSION["PATH_INFO"] = true;
		$output->screen(true);
	}
	else {

		$_SESSION["PATH_INFO"] = false;
		$output->screen(false);
	}

}
else {

	$page->header(array("type" => "setup"));
	$page->template("setup/paths.php");
	$page->footer(array("type" => "setup"));
	exit();

}

?>