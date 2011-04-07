<?php

class CURL {
	private $curl;

	public function __construct() {
		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_COOKIEFILE, '/dev/null');
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
	}

	protected function getResponseCode() {
		return curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
	}

	public function doGetRequest($url) {
		ob_start();
		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_HTTPGET, true);
		$text = curl_exec($this->curl);
		if (substr($this->getResponseCode(), 0, 1) != 2) {
			throw new Exception("HTTP-Exception: Got Code " + $this->getResponseCode());
		}
		return $text;
	}

	public function doPostRequest($url, $data) {
		ob_start();
		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_POST, true);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
		$text = curl_exec($this->curl);
		if (substr($this->getResponseCode(), 0, 1) != 2) {
			throw new Exception("HTTP-Exception: Got Code " + $this->getResponseCode());
		}
		return $text;
	}
}

?>
