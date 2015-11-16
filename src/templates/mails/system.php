<?php
$message .= "\n\n------ ADDITIONAL SERVER INFO ------";

$message .= "\n\n".($_SERVER["HTTPS"] ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];

$message .= "\n\nFrom IP: ".getenv("HTTP_X_FORWARDED_FOR") ? getenv("HTTP_X_FORWARDED_FOR") : getenv("REMOTE_ADDR");
$message .= "\nReferer: ".$_SERVER["HTTP_REFERER"];
$message .= "\nUserAgent: ".$_SERVER["HTTP_USER_AGENT"];


$message .= "\n\nSegment:\n";
$message .= print_r(session()->value("segment"), true);

$message .= "\nMessages:\n";
$message .= print_r(message()->getMessages(), true);

$message .= "\n_SESSION:\n";
$message .= print_r($_SESSION, true);

$message .= "\n_POST\n";
$message .= print_r($_POST, true);

$message .= "\n_GET\n";
$message .= print_r($_GET, true);

$message .= "\n_FILES\n";
$message .= print_r($_FILES, true);

$message .= "\n_SERVER\n";
$message .= print_r($_SERVER, true);

?>