<?php

$message .= "\n\n------ POST/GET INFO ------";

$message .= "\n\n".($_SERVER["HTTPS"] ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]."\n";
$message .= "From IP: ".getenv("HTTP_X_FORWARDED_FOR") ? getenv("HTTP_X_FORWARDED_FOR") : getenv("REMOTE_ADDR");

$message .= "\n\n_POST\n";
$message .= print_r($_POST, true);

$message .= "\n\n_GET\n";
$message .= print_r($_GET, true);

$message .= "\n\n_SERVER\n";
$message .= print_r($_SERVER, true);

?>