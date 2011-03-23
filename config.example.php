<?php

require_once(dirname(__FILE__) . "/classes/etherpad.class.php");
require_once(dirname(__FILE__) . "/classes/mediawiki.class.php");

class Vorstand {
	public static function getEtherPad() {
		$pad = new Etherpad("jupis.piratenpad.de");
		$pad->login("pad-mailadresse", "pad-passwort");
		return $pad;
	}

	public static function getMediaWiki() {
		$wiki = new Mediawiki("http://wiki.junge-piraten.de/w/api.php");
		$wiki->login("wiki-user", "wiki-passwort");
		return $wiki;
	}
}

?>
