<?php

$message = preg_replace("/\; REFERRER\:/", ";\nREFERRER:", $message);
$message = preg_replace("/\; USERAGENT\:/", ";\nUSERAGENT:", $message);
$message = preg_replace("/\; IDENTIFIED\:/", ";\nIDENTIFIED:", $message);

?>