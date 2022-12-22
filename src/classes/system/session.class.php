<?php

/**
* This class contains session value storage functionality
*/
class Session {

	/**
	* Set/Get value - omit value to get - state value to set
	*
	* @param String $key Key – Optional – if key is 
	* @param String $value Value to save - Optional
	*/
	function value($key = false, $value = false) {

		if($key !== false && $value !== false) {

			// writeToFile("set value:" . $key ."=".$value);

			sessionStart();
			$_SESSION["SV"][$key] = json_encode($value);
			sessionEnd();

		}
		else {

			// writeToFile("get value:" . $key);

			$return_value = false;

			sessionStart();
			if($key) {
				if(isset($_SESSION["SV"]) && isset($_SESSION["SV"][$key])) {
					$return_value = json_decode($_SESSION["SV"][$key], true);
				}
			}
			else if(isset($_SESSION["SV"])) {
				$return_value = $_SESSION["SV"];
			}
			sessionEnd();

			return $return_value;

		}
	}

	/**
	* Reset value and all sub values - or plain reset all values if key is omitted
	*
	* @param String $key Key
	*/
	function reset($key = false) {
		// writeToFile("reset:" . $key);

		sessionStart();
		if($key) {
			unset($_SESSION["SV"][$key]);
		}
		// reset entire session
		else {
//			session_unset();
			unset($_SESSION);

			if(ini_get("session.use_cookies")) {
				$params = session_get_cookie_params();
				setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
			}

			session_destroy();

			// start a new session
			session_start();
			// regerate Session id
			session_regenerate_id(true);

		}
		sessionEnd();

	}

}


?>