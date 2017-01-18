<?php

// Extend Memcached session handler for PHP7 support
// TODO: can be deleted when Memcached session handler returns proper value as expected by PHP7
class MemcachedSession extends SessionHandler {
	public function read($session_id) {
		return (string)parent::read($session_id);
	}
}
$sess = new MemcachedSession();
session_set_save_handler($sess, true);


/**
* Start session
*/


if(!isset($_SESSION)) {
	session_start();
}

/**
* This class contains session value exchange functionality
*/
class Session {

	/**
	* Set/Get value - omit value to get - state value to set
	*
	* @param String $key Key
	* @param String $value Value to save - Optional
	*/
	function value($key, $value = false) {
		if($value !== false) {

			// writeToFile("set value:" . $key ."=".$value);
			$_SESSION["SV"][$key] = json_encode($value);
		}
		else {
			// writeToFile("get value:" . $key);

			if(!isset($_SESSION["SV"]) || !isset($_SESSION["SV"][$key])) {
				return false;
			}
			return json_decode($_SESSION["SV"][$key], true);

		}
	}

	/**
	* Reset value and all sub values - or plain reset all values if key is omitted
	*
	* @param String $key Key
	*/
	function reset($key = false) {
		// writeToFile("reset:" . $key);

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
	}
}

$sss = new Session();

function session() {
	global $sss;
	return $sss;
}

?>