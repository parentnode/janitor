<?php

	session_start();
	if(isset($_GET["segment"])) {
		$_SESSION["segment"] = $_GET["segment"];
	}
	if(!isset($_SESSION["segment"])) {

		$segment = @file_get_contents("http://devices-v3.dearapi.com/text?ua=".urlencode($_SERVER["HTTP_USER_AGENT"])."&site=".urlencode($_SERVER["HTTP_HOST"])."&file=".urlencode($_SERVER["SCRIPT_NAME"]));
//		$device_id = file_get_contents("http://devices.local/xml?ua=".urlencode($_SERVER["HTTP_USER_AGENT"])."&site=".urlencode($_SERVER["HTTP_HOST"])."&file=".urlencode($_SERVER["SCRIPT_NAME"]));
		if($segment) {
			$_SESSION["segment"] = $segment;
		}
		else {
			$_SESSION["segment"] = "desktop";
		}
	}


	// debug helper
	if(isset($_GET["dev"])) {
		$_SESSION["dev"] = $_GET["dev"];
	}
	if(!isset($_SESSION["dev"])) {
		$_SESSION["dev"] = false;
	}

?>
