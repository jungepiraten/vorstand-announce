<?php

require_once(dirname(__FILE__) . "/classes/etherpad.class.php");
require_once(dirname(__FILE__) . "/classes/mediawiki.class.php");
require_once(dirname(__FILE__) . "/classes/organ.class.php");

class Vorstand {
	public static function getEtherPad() {
		$pad = new Etherpad("jupis.piratenpad.de");
		$pad->login("padmail", "padpw");
		return $pad;
	}

	public static function getMediaWiki() {
		$wiki = new Mediawiki("http://wiki.junge-piraten.de/w/api.php");
		$wiki->login("wikilogin", "wikipw");
		return $wiki;
	}
}

?>
