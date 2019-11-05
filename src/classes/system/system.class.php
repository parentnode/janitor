<?php
/**
* This file contains System maintenance functionality
*/
class System extends Model {

	function __construct() {

		// Usergroup
		$this->addToModel("maillist", array(
			"type" => "string",
			"label" => "List title",
			"required" => true,
			"hint_message" => "Make it clear",
			"error_message" => "Invalid maillist name"
		));
		
	}


	// flush entry from cache
	function flushFromCache($action) {

		$cache_key = getPost("cache-key");

		if(count($action) == 1 && $cache_key) {

			cache()->reset($cache_key);

			message()->addMessage("$cache_key flushed from cache");
			return true;
		
		}

		message()->addMessage("Key could not be flushed", array("type" => "error"));
		return false;

	}


	// TODO: add language, country, currency, vatrate, etc maintenance functions here


	function addMaillist($action) {
		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("maillist"))) {

			$query = new Query();

			$maillist = $this->getProperty("maillist", "value");

			$sql = "SELECT * FROM UT_MAILLISTS WHERE name = '$maillist'";
			if(!$query->sql($sql)) {
				$sql = "INSERT INTO ".UT_MAILLISTS." SET name='$maillist'";
				$query->sql($sql);

				cache()->reset("maillists");
			}

			message()->addMessage("Maillist added");
			return array("item_id" => $query->lastInsertId());
		}

		message()->addMessage("Could not add maillist", array("type" => "error"));
		return false;
		
	}


}

?>