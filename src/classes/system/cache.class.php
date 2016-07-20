<?php
/**
* This file contains Cache-functions
*/

class Cache {

	function __construct() {

		// Memcached not installed - create fake cache to make sure dependencies don't fail
		if(class_exists("Memcached")) {

			$this->memc = new Memcached();
			$this->memc->addServer('localhost', 11211);
		}
		// fallback to fake cache if Memcached is not installed
		else {

			$this->memc = new FakeCache();
		}

	}


	// Set/Get Cached value
	function value($key, $value = false) {

		// set value
		if($value) {
			$this->memc->set(SITE_URL."-".$key, json_encode($value));
		}
		// get value
		else {
			return json_decode($this->memc->get(SITE_URL."-".$key), true);
		}

	}


	// TODO: return true/false on success/error
	function reset($key) {

		if($this->memc->get(SITE_URL."-".$key) ) {
			$this->memc->delete(SITE_URL."-".$key);
		}

	}

	function unserializeSession($data) {

		$results = "";
		$vars = preg_split('/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\|/', $data, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		if(count($vars) > 1) {

			for($i = 0; isset($vars[$i]); $i++) {
				$results[$vars[$i++]] = unserialize($vars[$i]);
			}

			foreach($results as $index => $result) {
				$results[$index] = $this->decodeSessionJSON($result);
			}

		}
		return $results;
	}

	function decodeSessionJSON($dataset) {

		if(is_array($dataset)) {
			foreach($dataset as $index =>$data) {
				$dataset[$index] = $this->decodeSessionJSON($data);
			}
			return $dataset;
		}
		else if(!is_object($dataset)) {
			return json_decode($dataset, true);
		}
		else {
			return $dataset;
		}

	}
}

// Fake Caching for systems without Memcached installed
class FakeCache {

	function __construct() {
		$this->fake_cache = array();
	}
	function get($key) {
		return isset($this->fake_cache[$key]) ? $this->fake_cache[$key] : "";
	}
	function set($key, $value = false) {
		$this->fake_cache[$key] = $value;
	}
	function delete($key) {
		unset($this->fake_cache[$key]);
	}
}



$ccc = new Cache();

function cache() {
	global $ccc;
	return $ccc;
}
?>