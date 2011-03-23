<?php

class CURL {
	private $curl;

	public function __construct() {
		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_COOKIEFILE, '/dev/null');
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
	}

	public function doGetRequest($url) {
		ob_start();
		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_HTTPGET, true);
		curl_exec($this->curl);
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	public function doPostRequest($url, $data) {
		ob_start();
		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_POST, true);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
		curl_exec($this->curl);
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
}

?>
