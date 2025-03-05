<?php
	


class FraudProtectionGateway {


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
		@include_once("config/connect_fraudprotection.php");

	}

	function fraudprotection_connection($_settings) {

		$this->_settings = $_settings;

	}

	function init_adapter() {

		if(!$this->adapter) {

			if($this->_settings) {

				if(file_exists(LOCAL_PATH."/classes/adapters/fraudprotection/".$this->_settings["type"].".class.php")) {

					@include_once("classes/adapters/fraudprotection/".$this->_settings["type"].".class.php");
					$adapter_class = "Janitor".ucfirst($this->_settings["type"]);
					$this->adapter = new $adapter_class();

				}

			}

		}

	}


	function getSiteKey($_options = false) {
		// debug(["getEvaluation", $_options]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {

			return $this->adapter->getSiteKey($_options);

		}

	}


	function getEvaluation($_options = false) {
		// debug(["getEvaluation", $_options]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {

			return $this->adapter->getEvaluation($_options);

		}

	}


}
