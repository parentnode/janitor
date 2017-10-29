<?php
/**
* This file contains settings for mailer connection
*
*
* @package Config
*/
define("ADMIN_EMAIL", "###ADMIN_EMAIL###");

$this->mail_connection(
	array(
		"type" => "###TYPE###",
		"host" => "###HOST###", 
		"port" => "###PORT###", 
		"username" => "###USERNAME###", 
		"password" => "###PASSWORD###", 
		"secure" => "tls", 
		"smtpauth" => true
	)
);

?>
