<?php

$subject = "Activate your account on ".SITE_NAME."!";

preg_match("/PASSWORD\:([^\n]+)/", $message, $password_match);
preg_match("/EMAIL\:([^\n]+)/", $message, $email_match);
preg_match("/VERIFICATION\:([^\n]+)/", $message, $verification_match);

if($password_match && $email_match && $verification_match) {
	$password = $password_match[1];
	$email = $email_match[1];
	$verification = $verification_match[1];

	$message = "Thank you for being curious.\n\n";
	$message .= "Activate your account by clicking the link below:\n";
	$message .= SITE_URL."/signup/confirm/email/$email/$verification\n\n";

	$message .= "Your account details:.\n\n"; 

	$message .= "Url: ".SITE_URL."\n";
	$message .= "Username: $email\n";
	$message .= "Password: $password\n\n";

	$message .= "If you want to update your details or newsletter subscription, please visit ".SITE_URL."/janitor/admin/profile.\n\n";

}
else {
	$message = "Something went bonkers on our end!?.\n";
	$message .= "Please reply this email in order to solve the problem.\n\n";

}

$message .= "Greetings,\n\n";
$message .= SITE_NAME;

?>