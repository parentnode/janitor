<?php
/**
* This file contains Cache-functions
*/

class Cache {

	function value($key, $value = false) {

		if(class_exists("Memcached")) {

			$memc = new Memcached();
			$memc->addServer('localhost', 11211);


			// set value
			if($value) {
				$memc->set($key, json_encode($value));
			}
			// get value
			else {
				return json_decode($memc->get($key), true);
			}

		}

	}

	function reset($key) {

		if(class_exists("Memcached")) {

			$memc = new Memcached();
			$memc->addServer('localhost', 11211);

			$memc->delete($key);

		}
	}
}

$ccc = new Cache();

function cache() {
	global $ccc;
	return $ccc;
}
?>