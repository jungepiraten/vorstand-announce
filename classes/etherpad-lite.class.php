<?php

require_once(dirname(__FILE__) . "/etherpad-lite-client.php");

class EtherpadLite {
	private $padServer;
	private $apiClient;

	public function __construct($apiKey, $padServer) {
		$this->padServer = $padServer;
		$this->apiClient = new EtherpadLiteClient($apiKey, "https://" . $padServer . "/api");
	}

	public function createPad($padId) {
		$this->apiClient->createPad($padId, "");
		return $this->getPad($padId);
	}

	public function getPad($padId) {
		return new LitePad($this->padServer, $this->apiClient, $padId);
	}
}

class LitePad {
	private $padServer;
	private $apiClient;
	private $padId;

	public function __construct($padServer, $apiClient, $padId) {
		$this->padServer = $padServer;
		$this->apiClient = $apiClient;
		$this->padId = $padId;
	}

	public function exists() {
		return ($this->getText() != null);
	}

	public function getURL() {
		return "https://" . $this->padServer . "/p/" . $this->padId;
	}

	public function getText() {
		try {
			return $this->apiClient->getText($this->padId)->text;
		} catch (Exception $e) {
			return null;
		}
	}

	public function setText($text) {
		$this->apiClient->setText($this->padId, $text);
	}
}

?>
