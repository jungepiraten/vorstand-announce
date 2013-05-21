<?php

require_once(dirname(__FILE__) . "/classes/etherpad.class.php");
require_once(dirname(__FILE__) . "/classes/mediawiki.class.php");
require_once(dirname(__FILE__) . "/classes/organ.class.php");

class Bundesvorstand extends Organ {
	public function __construct($wiki, $pad) {
		parent::__construct("Bundesvorstand", $wiki, "Vorstand", $pad, "vorstandssitzung");
	}
	
	public function updateSitzungsAnnounce($lastsitzung, $nextsitzung) {
		$lastday = date("d", $lastsitzung);
		$lastmonth = date("m", $lastsitzung);
		$lastyear = date("Y", $lastsitzung);
		$nextday = date("d", $nextsitzung);
		$nextmonth = date("m", $nextsitzung);
		$nextyear = date("Y", $nextsitzung);
		$protocol = $this->wikiPrefix . "/Sitzung " . date("Y-m-d", $lastsitzung) . "/Protokoll";
		$this->wiki->getPage("Vorlage:Hauptseite/Aktuelles/Sitzungsvariablen")->setText("{{#vardefine:lastday|$lastday}}{{#vardefine:lastmonth|$lastmonth}}{{#vardefine:lastyear|$lastyear}}{{#vardefine:nextday|$nextday}}{{#vardefine:nextmonth|$nextmonth}}{{#vardefine:nextyear|$nextyear}}{{#vardefine:lastprotocol|$protocol}}");
	}
}

$wiki = new Mediawiki("https://wiki.junge-piraten.de/w/api.php");
$wiki->login("Vorstandsbot", "WIKIPASSWORT", "Junge Piraten");

$pad = new EtherpadLite("APIKEY", "pad.junge-piraten.de");

$vorstand = new Bundesvorstand($wiki, $pad);

?>
