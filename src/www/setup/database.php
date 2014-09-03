<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once("../../config/setup/init.php");


$action = $page->actions();
$output = new Output();


$page->bodyClass("database");
$page->pageTitle("Janitor setup guide");


if($_SERVER["REQUEST_METHOD"] == "POST") {

	$db_host = getPost("db_host");
	$db_root_user = getPost("db_root_user");
	$db_root_pass = getPost("db_root_pass");

	$db_janitor_db = getPost("db_janitor_db");
	$db_janitor_user = getPost("db_janitor_user");
	$db_janitor_pass = getPost("db_janitor_pass");

	$_SESSION["db_host"] = $db_host;
	$_SESSION["db_root_user"] = $db_root_user;
	$_SESSION["db_root_pass"] = $db_root_pass;
	$_SESSION["db_janitor_db"] = $db_janitor_db;
	$_SESSION["db_janitor_user"] = $db_janitor_user;
	$_SESSION["db_janitor_pass"] = $db_janitor_pass;

	if($db_host && $db_root_user && $db_root_pass && $db_janitor_db && $db_janitor_user && $db_janitor_pass) {


		$mysqli = new mysqli($db_host, $db_root_user, $db_root_pass);
		if($mysqli->connect_errno) {
			message()->addMessage("Could not connect to database", array("type" => "error"));
		}
		else {

			// correct the database connection setting
			$mysqli->query("SET NAMES utf8");
			$mysqli->query("SET CHARACTER SET utf8");
			$mysqli->set_charset("utf8");

			global $mysqli_global;
			$mysqli_global = $mysqli;

			$query = new Query();

			if(!$query->sql("SHOW DATABASES LIKE '$db_janitor_db';")) {

				$_SESSION["DATABASE_INFO"] = true;

				$output->screen(true);
				exit();
			}
			else {
				message()->addMessage("Database $db_janitor_db already exists", array("type" => "error"));
			}

		}

		
	}
	else {
		message()->addMessage("Missing information", array("type" => "error"));
	}

	$_SESSION["DATABASE_INFO"] = false;
	$output->screen(false);
}
else {

	$page->page(array(
		"type" => "setup",
		"templates" => "setup/database.php"
		)
	);
	exit();

}

?>