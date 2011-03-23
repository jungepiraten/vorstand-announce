<?php

require_once(dirname(__FILE__) . "/curl.class.php");

class Mediawiki extends CURL {
	private $apiurl;

	public function __construct($apiurl, $username = null, $password = null) {
		parent::__construct();
		$this->apiurl = $apiurl;

		if ($username !== null) {
			$this->login($username, $password);
		}
	}

	public function doGetRequest($url) {
		return unserialize(parent::doGetRequest($this->apiurl . "?format=php&" . $url));
	}

	public function doPostRequest($url, $data) {
		return unserialize(parent::doPostRequest($this->apiurl . "?format=php&" . $url, $data));
	}

	public function login($username, $password, $domain = null) {
		$authstring = "action=login&lgname=" . urlencode($username) . "&lgpassword=" . urlencode($password);
		if ($domain != null) {
			$authstring .= "&lgdomain=" . urlencode($domain);
		}
		$data = $this->doPostRequest("",$authstring);
		if ($data["login"]["result"] == "NeedToken") {
			$data = $this->doPostRequest("", $authstring . "&lgtoken=" . urlencode($data["login"]["token"]));
			return $data["login"]["result"] == "Success";
		}
	}

	public function getPage($titel) {
		return new MediaWikiPage($this, $titel);
	}

	public function searchPrefix($prefix) {
		$pages = array();
		$apfrom = "";
		do {
			$data = $this->doGetRequest("action=query&list=allpages&apprefix=" . urlencode($prefix) . $apfrom);
			foreach ($data["query"]["allpages"] as $page) {
				$pages[] = $page["title"];
			}
		} while (isset($data["query-continue"]) && $apfrom = "&apfrom=" . urlencode($data["query-continue"]["allpages"]["apfrom"]));
		return $pages;
	}
}

class MediaWikiPage {
	private $mediawiki;
	private $titel;

	public function __construct($mediawiki, $titel) {
		$this->mediawiki = $mediawiki;
		$this->titel = $titel;
	}

	private function getActionToken($action) {
		$data = $this->mediawiki->doGetRequest("action=query&prop=info&titles=" . urlencode($this->titel) . "&intoken=" . urlencode($action));
		$page = array_shift($data["query"]["pages"]);
		return $page[$action . "token"];
	}

	public function getEditToken() {
		return $this->getActionToken("edit");
	}

	public function getProtectToken() {
		return $this->getActionToken("protect");
	}

	public function exists() {
		return $this->getText() != null;
	}

	public function getLinks() {
		$string = "action=query&prop=links&title=" . urlencode($this->titel);
		$plcontinue = "";
		$links = array();
		do {
			$data = $this->mediawiki->doGetRequest($string . $plcontinue);
			$page = array_shift($data["query"]["pages"]);
			foreach ($page["links"] as $link) {
				$links[] = $link["title"];
			}
		} while(isset($data["query-continue"]) && $plcontinue = "&plcontinue=" . urlencode($data["query-continue"]["links"]["plcontinue"]));
		return $links;
	}

	public function setText($text, $summary = "edited", $section = null) {
		$edittoken = $this->getEditToken();
		$string = "token=" . urlencode($edittoken) . "&title=" . urlencode($this->titel) . "&text=" . urlencode($text) . "&summary=" . urlencode($summary);
		if ($section != null) {
			$string .= "&section=" . urlencode($section);
		}
		$data = $this->mediawiki->doPostRequest("action=edit", $string);
	}

	public function getText($section = null) {
		$string = "action=query&prop=revisions&rvprop=content&titles=" . urlencode($this->titel);
		if ($section != null) {
			$string .= "&rvsection=" . urlencode($section);
		}
		$data = $this->mediawiki->doGetRequest($string);
		if (isset($data["error"]["code"])) {
			return null;
		}
		$page = array_shift($data["query"]["pages"]);
		if (isset($page["missing"])) {
			return null;
		}
		$content = array_shift(array_shift($page["revisions"]));
		return $content;
	}

	public function protect($protections = null) {
		if ($protections === null) {
			$protections = array("edit" => "sysop", "move" => "sysop");
		}
		$protects = array();
		foreach ($protections as $level => $group) {
			$protects[] = $level . "=" . $group;
		}
		
		$token = $this->getProtectToken();
		$string = "token=" . urlencode($token) . "&title=" . urlencode($this->titel) . "&protections=" . urlencode(implode("|", $protects));
		$data = $this->mediawiki->doPostRequest("action=protect", $string);
	}
}

?>
