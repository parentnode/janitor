<?php
/**
* This file contains Cache-functions
*/

class Cache {

	public $cache_type;

	function __construct() {

		// If Redis or Memcached not installed - create pseudo cache to make sure dependencies don't fail


		if(class_exists("Redis")) {

			$this->cache_type = "redis";

			$this->cache = new Redis();
			$this->cache->connect('127.0.0.1', 6379);
		}
		else if(class_exists("Memcached")) {

			$this->cache_type = "memcached";

			$this->cache = new Memcached();
			$this->cache->addServer('localhost', 11211);
		}
		// fallback to pseudo cache if Redis or Memcached is not installed
		else {

			$this->cache_type = "pseudo";

			$this->cache = new PseudoCache();
		}

	}


	// Set/Get Cached value
	function value($key, $value = false, $expire = false) {

		// set value
		if($value) {
			if($expire) {
				if($this->cache_type === "memcached") {
					$this->cache->set(SITE_URL."-".$key, json_encode($value), false, $expire);
				}
				else {
					$this->cache->set(SITE_URL."-".$key, json_encode($value), $expire);
				}
			}
			else {
				$this->cache->set(SITE_URL."-".$key, json_encode($value));
			}
		}
		// get value
		else {
			return json_decode($this->cache->get(SITE_URL."-".$key), true);
		}

	}


	// return true/false on success/error
	function reset($key) {

		if($this->cache->get(SITE_URL."-".$key)) {
			if($this->cache_type === "redis") {
				return $this->cache->del(SITE_URL."-".$key);
			}
			else {
				return $this->cache->delete(SITE_URL."-".$key);
			}
		}
		else if($this->cache->get($key)) {
			if($this->cache_type === "redis") {
				return $this->cache->del($key);
			}
			else {
				return $this->cache->delete($key);
			}
		}

	}

	// igbinary unserializer with additional string and JSON decoding
	// TODO: Fix Unicode issues in nicknames
	function unserializeSession($data) {

		$results = "";

		// For some reason this returns all strings as quoted, like "martin" instead of martin
		$dataset = igbinary_unserialize($data);
		if(isset($dataset["SV"])) {

			foreach($dataset["SV"] as $key => $value) {
				if(is_string($value)) {
//					print $key . "=>" . $value."<br>\n";

					// detect JSON objects
					if(preg_match("/^\{[^$]+\}$/", $value)) {
						$dataset["SV"][$key] = json_decode($value, true);
					}
					// remove extra quotes from regular strings.
					else {
						$dataset["SV"][$key] = stripslashes(preg_replace("/^\"|\"$/", "", mb_convert_encoding($value, "UTF-8")));
					}

				}


			}

		}
		return $dataset;
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


	function getAllDomainPairs() {

		$entries = array();

		// Redis
		if($this->cache_type == "redis") {
		
			$keys = $this->cache->keys("*");
		}
		// Memcached
		else if($this->cache_type == "memcached") {
			
			$keys = $this->cache->getAllKeys();
		}
		// Pseudo cache
		else {

			$keys = $this->cache->keys("*");
		}

		foreach($keys as $key) {
			// debug(["key", $key]);

			// only list cache entries matching current site
			if(preg_match("/^".preg_quote(SITE_URL, "/")."\-/", $key)) {

				$entry = $this->cache->get($key);
				// debug(["entry:", $entry]);

				$data = $this->decodeSessionJSON($entry);
				// debug(["data:", $data]);

				$entries[preg_replace("/^".preg_quote(SITE_URL, "/")."\-/", "", $key)] = $data;

			}

		}

		return $entries;

	}


	function getAllDomainSessions() {

		$users = array();

		// Redis
		if($this->cache_type == "redis") {
		
			$keys = $this->cache->keys("PHPREDIS_SESSION*");
		}
		// Memcached
		else if($this->cache_type == "memcached") {
			
			$keys = $this->cache->getAllKeys();
			foreach($keys as $i => $key) {
				if(!preg_match("/sess\.(?!lock)/i", $key)) {
					unset($keys[$i]);
				}
			}
		}
		// Pseudo cache (no access to user session)
		else {

			return $users;
		}

		foreach($keys as $key) {
//			print $key."<br>\n";
			$user = $this->cache->get($key);
			// debug(["user:", $user]);

			if($user) {

				$data = $this->unserializeSession($user);
				// debug(["data:", $data]);

				// collect sessions users
				// skip current user
				if($data && isset($data["SV"]) && isset($data["SV"]["csrf"]) && $data["SV"]["csrf"] != session()->value("csrf")) {

					$values = $data["SV"];

					if($values["site"] == SITE_URL) {
						$users[] = array(
							"user_id" => $values["user_id"],
							"user_group_id" => $values["user_group_id"],
							"nickname" => isset($values["user_nickname"]) ? $values["user_nickname"] : "Anonymous",
							"ip" => $values["ip"]."cc",
							"useragent" => $values["useragent"],
							"last_login_at" => $values["last_login_at"],
							"session_key" => $key
						);
					}

				}

			}

		}

		return $users;

	}

}


// Pseudo Caching for systems without Redis/Memcached installed
// – expire times are not meaningful as this cache only lives through the execution of the individual request
class PseudoCache {

	private $pseudo_cache;

	function __construct() {
		$this->pseudo_cache = array();
	}
	function get($key) {
		return isset($this->pseudo_cache[$key]) ? $this->pseudo_cache[$key] : "";
	}
	function set($key, $value = false, $expire = false) {
		$this->pseudo_cache[$key] = $value;
	}
	function delete($key) {
		if(isset($this->pseudo_cache[$key])) {
			unset($this->pseudo_cache[$key]);
		}
		return true;
	}
	function keys() {
		return array_keys($this->pseudo_cache);
	}

}



$ccc = new Cache();

function cache() {
	global $ccc;
	return $ccc;
}
?>