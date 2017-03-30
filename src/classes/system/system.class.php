<?php
/**
* This file contains System maintanence functionality
*/
class System extends Model {

	function __construct() {

		// Usergroup
		$this->addToModel("newsletter", array(
			"type" => "string",
			"label" => "List title",
			"required" => true,
			"hint_message" => "Make it clear",
			"error_message" => "Invalid newsletter name"
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


	function addNewsletter($action) {
		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("newsletter"))) {

			$query = new Query();

			$newsletter = $this->getProperty("newsletter", "value");

			// already signed up (to avoid faulty double entries)
			$sql = "SELECT * FROM UT_NEWSLETTERS WHERE name = '$newsletter'";
			if(!$query->sql($sql)) {
				$sql = "INSERT INTO ".UT_NEWSLETTERS." SET name='$newsletter'";
				$query->sql($sql);

				cache()->reset("newsletters");
			}

			message()->addMessage("Newsletter added");
			return array("item_id" => $query->lastInsertId());
		}

		message()->addMessage("Could not add newsletter", array("type" => "error"));
		return false;
		
	}


}

?>