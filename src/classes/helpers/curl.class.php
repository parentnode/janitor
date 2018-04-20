<?php
	
class CurlRequest {

	private $ch;

	public function init($params) {

		$this->ch = curl_init();

		@curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		@curl_setopt($this->ch, CURLOPT_VERBOSE, 1);
		@curl_setopt($this->ch, CURLOPT_HEADER, 1);
		@curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
		@curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
		@curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);

		@curl_setopt($this->ch, CURLOPT_COOKIEFILE, "");
 
		if(isset($params['header']) && $params['header']) {
			@curl_setopt($this->ch, CURLOPT_HTTPHEADER, $params['header']);
		}

		if($params['method'] == "HEAD") {
			@curl_setopt($this->ch, CURLOPT_NOBODY, 1);
		}

		if(isset($params['useragent'])) {
			@curl_setopt($this->ch, CURLOPT_USERAGENT, $params['useragent']);
		}

		if($params['method'] == "POST") {
			@curl_setopt($this->ch, CURLOPT_POST, true);
			@curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params['post_fields']);
		}

		if(isset($params['referer'])) {
			@curl_setopt($this->ch, CURLOPT_REFERER, $params['referer']);
		}

		if(isset($params['cookie'])) {
			@curl_setopt($this->ch, CURLOPT_COOKIE, $params['cookie']);
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
		

		if($result["http_code"] == 200 && $result['last_url'] == $url) {
			return $result;
		}
		else {
			return false;
		}

	}
}