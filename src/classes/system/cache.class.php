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
				$memc->set(SITE_URL."-".$key, json_encode($value));
			}
			// get value
			else {
				return json_decode($memc->get(SITE_URL."-".$key), true);
			}

		}

	}

	function reset($key) {

		if(class_exists("Memcached")) {

			$memc = new Memcached();
			$memc->addServer('localhost', 11211);

			$memc->delete(SITE_URL."-".$key);

		}
	}

	
	function unserializeSession($data) {

		$vars = preg_split('/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\|/', $data, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		for($i = 0; isset($vars[$i]); $i++) {
			$results[$vars[$i++]] = unserialize($vars[$i]);
		}

		foreach($results as $index => $result) {

			$results[$index] = $this->decodeSessionJSON($result);
			
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

$ccc = new Cache();

function cache() {
	global $ccc;
	return $ccc;
}
?>