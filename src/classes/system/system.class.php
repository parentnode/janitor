<?php
/**
* This file contains System maintanence functionality
*/
class System {

	function __construct() {
		
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

}

?>