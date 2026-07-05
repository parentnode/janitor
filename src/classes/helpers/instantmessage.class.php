<?php
	


class InstantMessageGateway {


	// Instantmessage settings
	private $_settings;
	private $adapter;

	/**
	*
	*/
	function __construct() {

		// no adapter selected yet
		$this->adapter = false;

		// email connection info
		@include_once("config/connect_instantmessage.php");

	}

	function instantmessage_connection($_settings) {

		$this->_settings = $_settings;

	}

	function init_adapter() {

		if(!$this->adapter) {

			if($this->_settings) {

				if(file_exists(LOCAL_PATH."/classes/adapters/instantmessage/".$this->_settings["type"].".class.php")) {

					@include_once("classes/adapters/instantmessage/".$this->_settings["type"].".class.php");
					$adapter_class = "Janitor".ucfirst($this->_settings["type"]);
					$this->adapter = new $adapter_class();

				}

			}

		}

	}

	/**
	 * SMSGateway::send
	 *
	 * @param array|false $_options
	 * 
	 * @return string|false 
	 */
	function send($_options = false) {


		$this->init_adapter();

		// Only attempt sending with valid adapter
		if($this->adapter) {

			$to = false;
			$from = $this->_settings["from"];
			$body = "";

			if($_options !== false) {
				foreach($_options as $_option => $_value) {
					switch($_option) {

						case "to"                     : $to                     = $_value; break;
						case "from"                   : $from                   = $_value; break;
						case "body"                   : $body                   = $_value; break;
					}
				}
			}



			
			// only attempt sending if recipients are specified
			if($body && $to) {

				return $this->adapter->send([

					"to" => $to,
					"from" => $from,
					"body" => $body,

				]);

			}

		}

		return false;
	}

}
