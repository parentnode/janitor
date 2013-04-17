<?php
/**
* Message handling
*/
class Message {

	private $errorMessages = array();
	private $statusMessages = array();

	private $errors = false;
	private $messages = false;
	private $lastMessage = false;

	/**
	* Add status message
	*
	* @param string $string Status message
	*/
	function addStatusMessage($string) {
		$this->statusMessages[] = $string;
		$this->messages = true;
	}

	/**
	* Add error message
	*
	* @param string $string Error message
	*/
	function addErrorMessage($string) {
		$this->errorMessages[] = $string;
		$this->errors = true;
	}

	/**
	* Get stored messages (both errors and status)
	*
	* @param string $type Message delivery type
	* @return string Messages
	*/
	function getMessages($type = false) {
		$_ = '';
		$this->lastMessage = '';

		// if delivery type is JavaScript
		if($type == "js") {
			// always clear existing messages
			$_ .= '<script type="text/javascript">Util.clearMessageBoard();</script>';
			// get errors
			if($this->errors) {
				foreach($this->errorMessages as $value) {
					$_ .= "<script type=\"text/javascript\">Util.addMessageBoard('".htmlentities(stripslashes($value), ENT_QUOTES, "UTF-8")."', 'error');</script>";
					$this->lastMessage .= $value;
				}
			}
			// get messages
			if($this->messages) {
				foreach($this->statusMessages as $value) {
					$_ .= "<script type=\"text/javascript\">Util.addMessageBoard('".htmlentities(stripslashes($value), ENT_QUOTES, "UTF-8")."');</script>";
					$this->lastMessage .= $value;
				}
			}
		}
		// else delivery as plain HTML
		else {
			// get errors
			if($this->errors) {
				foreach($this->errorMessages as $value) {
					$_ .= '<p class="error">'.$value.'</p>';
					$this->lastMessage .= $value;
				}
			}
			// get messages
			if($this->messages) {
				foreach($this->statusMessages as $value) {
					$_ .= "<p>$value</p>";
					$this->lastMessage .= $value;
				}
			}
		}
		$this->resetMessages();
		return $_;
	}

	/**
	* Is there any undelivered messages?
	*
	* @return bool
	*/
	function hasMessages() {
		if($this->errors || $this->messages) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	* Get last delivered message
	*
	* @return string Last message
	*/
	function getLastMessage() {
		return $this->lastMessage ? $this->lastMessage : "";
	}

	/**
	* Reset (delete) all stored messages
	*/
	function resetMessages() {
		$this->errors = false;
		$this->messages = false;
		$this->errorMessages = array();
		$this->statusMessages = array();
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