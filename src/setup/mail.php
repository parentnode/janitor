<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once("defaults/init.php");


$action = $page->actions();
$output = new Output();


$page->bodyClass("mail");
$page->pageTitle("Janitor setup guide");


if($_SERVER["REQUEST_METHOD"] == "POST") {

	$mail_host = getPost("mail_host");
	$mail_port = getPost("mail_port");
	$mail_username = getPost("mail_username");
	$mail_password = getPost("mail_password");

	$_SESSION["mail_host"] = $mail_host;
	$_SESSION["mail_port"] = $mail_port;
	$_SESSION["mail_username"] = $mail_username;
	$_SESSION["mail_password"] = $mail_password;


	if($mail_host && $mail_port && $mail_username && $mail_password) {

		$_SESSION["MAIL_INFO"] = true;

		$output->screen(true);
		exit();

	}
	else {
		message()->addMessage("Missing information", array("type" => "error"));

		$_SESSION["MAIL_INFO"] = false;
		$output->screen(false);
		exit();
	}

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