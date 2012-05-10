<?php

require_once(dirname(__FILE__) . "/curl.class.php");

class Etherpad extends CURL {
	private $padServer;
	private $curl;

	public function __construct($padServer) {
		parent::__construct();
		$this->padServer = $padServer;
	}

	public function getPadServer() {
		return $this->padServer;
	}

	public function doGetRequest($url) {
		return parent::doGetRequest("http://" . $this->padServer . $url);
	}

	public function doPostRequest($url, $data) {
		return parent::doPostRequest("http://" . $this->padServer . $url, $data);
	}

	public function login($email, $pass) {
		$this->doGetRequest("/ep/account/sign-in");
		$this->doPostRequest("/ep/account/sign-in", "email=" . urlencode($email) . "&password=" . urlencode($pass));
	}

	public function createPad($padId) {
		$this->doGetRequest("/" . urlencode($padId));
		$this->doPostRequest("/ep/pad/create", "padId=" . urlencode($padId));
		return $this->getPad($padId);
	}

	public function getPad($padId) {
		// TODO ggf. Pad anlegen
		return new Pad($this, $padId);
	}
}

class Pad {
	private $etherpad;
	private $padId;

	public function __construct($etherpad, $padId) {
		$this->etherpad = $etherpad;
		$this->padId = $padId;
	}

	public function exists() {
		if ($this->getText() == null) {
			return false;
		} else {
			return true;
		}
	}

	public function getURL() {
		return "http://" . $this->etherpad->getPadServer() . "/" . $this->padId;
	}

	public function getText() {
		try {
			return $this->etherpad->doGetRequest("/ep/pad/export/" . urlencode($this->padId) . "/latest?format=txt");
		} catch (Exception $e) {
			return null;
		}
	}

	public function create() {
		$this->etherpad->createPad($this->padId);
	}

	public function setText($text) {
		// Unsupported
	}
}

?>
