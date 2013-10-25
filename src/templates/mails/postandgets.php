<?php

$message .= "_POST";
$message .= print_r($_POST, true);

$message .= "_GET";
$message .= print_r($_GET, true);

$message .= "_SERVER";
$message .= print_r($_SERVER, true);


$message .= "\n\n".$_SERVER["HTTP_HOST"]."\n".$_SERVER["REQUEST_URI"]."\n";
$message .= getenv("HTTP_X_FORWARDED_FOR") ? getenv("HTTP_X_FORWARDED_FOR") : getenv("REMOTE_ADDR");

?>