<?php

class CURL {
	private $curl;

	public function __construct() {
		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_COOKIEJAR, "/tmp/vosi-cookies");
		curl_setopt($this->curl, CURLOPT_COOKIEFILE, "/tmp/vosi-cookies");
		curl_setopt($this->curl, CURLOPT_AUTOREFERER, TRUE);
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
	}

	public function setUserAgent($uastring) {
		curl_setopt($this->curl, CURLOPT_USERAGENT, $uastring);
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
