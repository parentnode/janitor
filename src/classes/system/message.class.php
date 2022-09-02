<?php

/**
* Message handling via session
*/
class Message {


	/**
	* Add status message
	*
	* @param string $string Status message
	*/
	function addMessage($message, $options = false) {

		$type = "message";

		if($options !== false) {
			foreach($options as $option => $value) {
				switch($option) {
					case "type" : $type = $value; break;
				}
			}
		}

		session_start();
		$_SESSION["SM"][$type][] = $message;
		session_write_close();

	}

	/**
	* Get stored messages (both errors and status)
	*
	* @param string $type Message delivery type
	* @return string Messages
	*/
	function getMessages($options = false) {

		$type = false;

		if($options !== false) {
			foreach($options as $option => $value) {
				switch($option) {
					case "type" : $type = $value; break;
				}
			}
		}

		$messages = [];

		session_start();
		if(isset($_SESSION["SM"])) {

			if($type) {

				if(isset($_SESSION["SM"][$type])) {
					$messages = $_SESSION["SM"][$type];
				}
			}
			else {
				$messages = $_SESSION["SM"];
			}
			
		}
		session_write_close();

		return $messages;
	}

	/**
	* Is there any undelivered messages?
	*
	* @return bool
	*/
	function hasMessages($options = false) {

		$type = false;

		if($options !== false) {
			foreach($options as $option => $value) {
				switch($option) {
					case "type" : $type = $value; break;
				}
			}
		}

		$count = 0;

		session_start();
		if(isset($_SESSION["SM"])) {
			if($type) {
				if(isset($_SESSION["SM"][$type])) {
					$count = count($_SESSION["SM"][$type]);
				}
			}
			else {
				foreach($_SESSION["SM"] as $type) {
					$count += count($type);
				}
			}

		}
		session_write_close();

		return $count;
	}


	/**
	* Reset (delete) all stored messages
	*/
	function resetMessages($options = false) {

		$type = false;

		if($options !== false) {
			foreach($options as $option => $value) {
				switch($option) {
					case "type" : $type = $value; break;
				}
			}
		}

		session_start();
		if(isset($_SESSION["SM"])) {

			if($type) {
				if(isset($_SESSION["SM"][$type])) {
					unset($_SESSION["SM"][$type]);
				}
			}
			else {
				unset($_SESSION["SM"]);
			}

		}
		session_write_close();

	}

}


?>