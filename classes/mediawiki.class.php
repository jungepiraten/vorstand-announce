<?php

require_once(dirname(__FILE__) . "/curl.class.php");

class Mediawiki extends CURL {
	private $apiurl;

	public function __construct($apiurl, $username = null, $password = null) {
		parent::__construct();
		$this->apiurl = $apiurl;
		$this->setUserAgent("MediaWiki-PHP-Library by prauscher");

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

	public function getRandomPage() {
		$data = $this->doGetRequest("action=query&list=random&rnlimit=1");
		$page = array_shift($data["query"]["random"]);
		return new MediaWikiPage($this, $page["title"]);
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

	public function getPagesByCategory($name) {
		$pages = array();
		$apfrom = "";
		do {
			$data = $this->doGetRequest("action=query&list=categorymembers&cmtitle=Category:" . urlencode($name) . $apfrom);
			foreach ($data["query"]["categorymembers"] as $page) {
				$pages[] = $page["title"];
			}
		} while (isset($data["query-continue"]) && $apfrom = "&cmcontinue=" . urlencode($data["query-continue"]["categorymembers"]["cmcontinue"]));
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

	private function getPageProps($string) {
		$data = $this->mediawiki->doGetRequest("action=query&prop=info&titles=" . urlencode($this->titel) . "&" . $string);
		$page = array_shift($data["query"]["pages"]);
		return $page;
	}

	public function getPageName() {
		return $this->titel;
	}

	public function getURL() {
		$page = $this->getPageProps("inprop=url");
		return $page["fullurl"];
	}

	private function getActionToken($action) {
		$page = $this->getPageProps("intoken=" . urlencode($action));
		return $page[$action . "token"];
	}

	public function getEditToken() {
		return $this->getActionToken("edit");
	}

	public function getMoveToken() {
		return $this->getActionToken("move");
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
		$string = "token=" . urlencode($edittoken) . "&bot=1&title=" . urlencode($this->titel) . "&text=" . urlencode($text) . "&summary=" . urlencode($summary);
		if ($section !== null) {
			if (substr($section,0,4) == "new:") {
				$string .= "&section=new&sectiontitle=" . urlencode(substr($section,4));
			} else {
				$string .= "&section=" . urlencode($section);
			}
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
		$content = array_shift($page["revisions"]);
		return $content["*"];
	}

	public function getVorlagenVars($section = null) {
		$page = $this->getText($section);
		if ($page == null) {
			return null;
		}

		return getMediaWikiVorlagenVars($page);
	}

	public function move($newpage, $reason = null) {
		$string = "token=" . urlencode($this->getMoveToken()) . "&from=" . urlencode($this->titel) . "&to=" . urlencode($newpage);
		if ($reason != null) {
			$string .= "&reason=" . urlencode($reason);
		}
		$data = $this->mediawiki->doPostRequest("action=move", $string);
		$this->titel = $newpage;
	}

	public function protect($protections = null, $expire = null, $reason = null) {
		if ($protections === null) {
			$protections = array("edit" => "sysop", "move" => "sysop");
		}
		$protects = array();
		foreach ($protections as $level => $group) {
			$protects[] = $level . "=" . $group;
		}
		
		$token = $this->getProtectToken();
		$string = "token=" . urlencode($token) . "&title=" . urlencode($this->titel) . "&protections=" . urlencode(implode("|", $protects));
		if ($expire != null) {
			$string .= "expiry=" . date("r", $expire);
		}
		if ($reason != null) {
			$string .= "reason=" . urlencode($reason);
		}
		$data = $this->mediawiki->doPostRequest("action=protect", $string);
	}
}

function getMediaWikiVorlagenVars($page) {
	$vorlagenpos = 0;
	$linkpos = 0;
	$varname = null;
	$values = array();
	$buffer = "";
	for ($i = 0; $i < strlen($page); $i++) {
		if (substr($page,$i,2) == "[[") {
			$linkpos++;
		}
		if (substr($page,$i,2) == "{{") {
			$vorlagenpos++;
		}
		if ($vorlagenpos == 1 and $linkpos == 0) {
			if (substr($page,$i,1) == "|" or substr($page,$i,2) == "}}") {
				if ($varname != null and $buffer != "") {
					$values[trim($varname)] = trim($buffer);
					$varname = null;
				}
				$buffer = "";
				continue;
			}
			if (substr($page,$i,1) == "=" and $buffer != "" and $varname == null) {
				$varname = $buffer;
				$buffer = "";
				continue;
			}
		}
		if (substr($page,$i,2) == "]]") {
			$linkpos--;
		}
		if (substr($page,$i,2) == "}}") {
			$vorlagenpos--;
		}
		$buffer .= substr($page, $i, 1);
	}
	return $values;
}

?>
