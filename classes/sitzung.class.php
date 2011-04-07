<?php

class Sitzung {
	protected $organ;
	protected $wikiPage;
	protected $wikiProtokollPage;
	protected $padProtokoll;
	protected $timestamp;

	public function __construct($organ, $wikiPage, $wikiProtokollPage, $padProtokoll, $timestamp) {
		$this->organ = $organ;
		$this->wikiPage = $wikiPage;
		$this->wikiProtokollPage = $wikiProtokollPage;
		$this->padProtokoll = $padProtokoll;
		$this->timestamp = $timestamp;
	}

	public function getInfos() {
		return $this->wikiPage->getText(0);
	}

	public function getProtokollVorlage() {
		$pad = "== Anwesend ==" . "\n";
		$wiki = explode("\n", $this->wikiPage->getText(1));
		// Da ist die Uebersicht ("Tagesordnung") noch dabei
		array_shift($wiki);
		foreach ($wiki as $wikiline) {
			$i = 0;
			do {
				$char = $wikiline{$i++};
			} while ($char == "#");

			$level = $i;
			$label = trim(substr($wikiline, $i-1));

			if (trim($label) != "") {
				if ($char == "*" or ($i <= 1)) {
					$pad .= $label;
				} else {
					$pad .= str_repeat("=",$level) . " " . $label . " " . str_repeat("=",$level);                   
				}
				$pad .= "\n";
			}
		}
		$pad .= $this->wikiPage->getText(2);
		return $pad;
	}

	public function prepare() {
		$this->padProtokoll->create();
		// TODO Pad-Inhalt setzen
		// $this->getProtokollVorlage();
		// TODO Mail verschicken
	}

	public function save() {
		$protokoll = "{{Protokoll}}{{Offiziell}}" . $this->padProtokoll->getText();

		// Announce der naechsten Sitzungen und Update der Uebersichtsseite
		$lastsitzung = $this->timestamp;
		$nextsitzung = -1;
		preg_match_all('$\\[\\[(' . preg_quote($this->organ->getWikiPrefix()) . '/Sitzung[_ ](\\d{4}-\\d{2}-\\d{2}))\\|.*?\\]\\]$', $protokoll, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			$timestamp = strtotime($match[2]);
			if ($timestamp > $lastsitzung and ($nextsitzung < $lastsitzung or $timestamp < $nextsitzung) ) {
				$nextsitzung = $timestamp;
			}
			$page = Vorstand::getMediaWiki()->getPage($match[1]);
			if (!$page->exists()) {
				$page->setText("{{subst:Bundesvorstandssitzung|" . $match[2] . "}}", "Angekündigt laut [[" . $this->wikiProtokollPage->getPageName() . "]]");
			}
		}
		$this->organ->updateSitzungsAnnounce($lastsitzung, $nextsitzung);

		// TODO: Beschluesse automatisch erzeugen
		sendmail("phillip.thelen@junge-piraten.de", "VoSi-Protokoll", <<<EOT
Ahoi,

bei {$this->wikiProtokollPage->getPageName()} muessen noch folgende Tasks durchgefuehrt werden:

* Beschluesse kennzeichnen und formatieren
* Abschliessende Kontrolle

habe spass,
EOT
);

		$this->wikiProtokollPage->setText($protokoll, "Aus " . $this->padProtokoll->getURL());
		$this->wikiProtokollPage->protect();
		// TODO Mail verschicken
	}
}

?>
