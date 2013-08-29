<?php
/**
* Message handling
*/
class Message {

	private $errorMessages = array();
	private $statusMessages = array();

	private $messages = array();

	// TODO: implement message history
	private $message_history = false;

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


		$this->messages[$type][] = $message;
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

		if($type && isset($this->messages[$type])) {
			return $this->messages[$type];
		}

		return $this->messages;
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

		if($type && isset($this->messages[$type])) {
			return count($this->messages[$type]);
		}

		return count($this->messages);
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

		if($type && isset($this->messages[$type])) {
			$this->messages[$type] = null;
		}
		else if(!$type && $this->messages) {
			$this->messages = array();
		}

	}

}

/**
* Message handler
* Controls the existance of the message object and acts as an easy reference
*
* @return object Message object from session
* @uses Message
*/
function message() {
	if(!isset($_SESSION["message"])) {
		$_SESSION["message"] = new Message();
	}
	return $_SESSION["message"];
}

?>