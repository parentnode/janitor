<?php

$message .= "\n\n".$_SERVER["HTTP_HOST"]."\n".$_SERVER["REQUEST_URI"]."\n";
$message .= getenv("HTTP_X_FORWARDED_FOR") ? getenv("HTTP_X_FORWARDED_FOR") : getenv("REMOTE_ADDR");

$message .= "\n\nMessages:\n";
$message .= print_r(message()->getMessages(), true);

$message .= "\n\n_POST\n";
$message .= print_r($_POST, true);

$message .= "\n\n_GET\n";
$message .= print_r($_GET, true);

$message .= "\n\n_SERVER\n";
$message .= print_r($_SERVER, true);

?>