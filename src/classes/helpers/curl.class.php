<?php
	
class CurlRequest {

	private $ch;

	public function init($_options = false) {

		$header = false;
		$method = "GET";
		$useragent = false;
		$referer = false;
		$cookie = false;
		$cookiejar = false;

		$inputs = false;

		// overwrite model/defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "header"           : $header            = $_value; break;

					case "method"           : $method            = $_value; break;
					case "useragent"        : $useragent         = $_value; break;

					case "referer"          : $referer           = $_value; break;
					case "cookie"           : $cookie            = $_value; break;
					case "cookiejar"        : $cookiejar         = $_value; break;

					case "inputs"           : $inputs            = $_value; break;

					// Backwards compatibility
					case "post_fields"      : $inputs            = $_value; break;

				}
			}
		}

		

		$this->ch = curl_init();

		@curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		@curl_setopt($this->ch, CURLOPT_VERBOSE, 1);
		@curl_setopt($this->ch, CURLOPT_HEADER, 1);
		@curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
		@curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
		@curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);

		@curl_setopt($this->ch, CURLOPT_COOKIEFILE, "");
 
		if($header) {
			@curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
		}

		if(strtoupper($method) == "HEAD") {
			@curl_setopt($this->ch, CURLOPT_NOBODY, 1);
		}

		if($useragent) {
			@curl_setopt($this->ch, CURLOPT_USERAGENT, $useragent);
		}

		if(strtoupper($method) == "POST") {
			@curl_setopt($this->ch, CURLOPT_POST, true);
			@curl_setopt($this->ch, CURLOPT_POSTFIELDS, $inputs);
		}

		if($referer) {
			@curl_setopt($this->ch, CURLOPT_REFERER, $referer);
		}

		if($cookie) {
			@curl_setopt($this->ch, CURLOPT_COOKIE, $cookie);
		}
		
		if($cookiejar) {
			@curl_setopt($this->ch, CURLOPT_COOKIEJAR, $cookiejar);
		}
	}

	public function exec($url, $_options = false) {

		$debug = false;

		// overwrite model/defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "debug"           : $debug            = $_value; break;

				}
			}
		}


		@curl_setopt($this->ch, CURLOPT_URL, $url);

		$response = curl_exec($this->ch);
		$error = curl_error($this->ch);

		if($debug) {
			print_r($response);
		}

		$result = array(
			'header' => '',
			'body' => '',
			'curl_error' => '',
			'http_code' => '',
			'last_url' => ''
		);

		if($error) {
			$result['curl_error'] = $error;
			return $result;
		}

		$header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
		$result['header'] = substr($response, 0, $header_size);
		$result['body'] = substr($response, $header_size);
		$result['http_code'] = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
		$result['last_url'] = curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);
		$result['cookies'] = curl_getinfo($this->ch, CURLINFO_COOKIELIST);

		// Disabled because it turned out to be too restrictive for certain common use cases
		//
		// if($result["http_code"] == 200 && $result['last_url'] == $url) {
		// 	return $result;
		// }
		// else {
		// 	return false;
		// }

		return $result;

	}

	// Combined init and execution, for full request
	public function request($url, $_options = []) {

		$this->init($_options);
		return $this->exec($url, $_options);

	}
	
}