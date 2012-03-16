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

	public function exists() {
		return $this->wikiPage->exists();
	}

	public function getWikiPage() {
		return $this->wikiPage;
	}

	public function getProtokollPage() {
		return $this->wikiProtokollPage;
	}

	public function getTimestamp() {
		return $this->timestamp;
	}

	public function getLastSitzung() {
		return $this->organ->getSitzungBefore($this->timestamp - 1);
	}

	public function getInfos() {
		return $this->wikiPage->getText(0);
	}

	public function getProtokollVorlage() {
		$pad = "";
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

			if ($label != "") {
				preg_match_all('#<!--\\s*VORSTANDSBOT:\\s*(.*?)\\s*-->#', $label, $matches, PREG_SET_ORDER);
				$filters = array();
				foreach ($matches as $match) {
					$filters[] = strtolower($match[1]);
					$label = trim(str_replace($match[0], "", $label));
				}

				if ($label != "") {
					if ($char == "*" or ($i <= 1)) {
						$pad .= $label;
					} else {
						$pad .= str_repeat("=",$level) . " " . $label . " " . str_repeat("=",$level);                   
					}
					$pad .= "\n";
				}
				
				foreach ($filters as $filter) {
					$pad .= $this->handleFilter($filter) . "\n";
				}
			}
		}
		$pad = preg_replace('#<!--(.*?)*-->#', '', $pad);
		return $pad;
	}

	public function handleFilter($filter) {
		$args = explode(" ", $filter);
		switch (array_shift($args)) {
		case "anwesend":
			return "== Anwesend ==";
		case "aufzeichnung":
			return "{{Aufzeichnung}}";
		case "umlaufbeschluesse":
			$text = "";
			// + 1d, damit die Beschlüsse der Vorstandssitzung nicht mitzaehlen
			foreach ($this->organ->getBeschluesse($this->getLastSitzung()->getTimestamp() + 24*60*60, $this->timestamp) as $beschluss) {
				$text .= "* [[" . $beschluss->getWikiPage()->getPageName() . "|" . $beschluss->getTitel() . "]]" . "\n";
			}
			return trim($text);
		case "laufendebeschluesse":
			$text = "";
			foreach ($this->organ->getLaufendeBeschluesse() as $beschluss) {
				$vars = $beschluss->getWikiPage()->getVorlagenVars();
				$text .= "* [[" . $beschluss->getWikiPage()->getPageName() . "|" . $beschluss->getTitel() . "]]" . "\n";
				if (isset($vars["Zustaendig"])) {
					$text .= "** Verantwortlich: " . $vars["Zustaendig"] . "\n";
				}
			}
			return trim($text);
		case "letztesitzung":
			$sitzung = $this->getLastSitzung();
			return "Das [[" . $sitzung->getProtokollPage()->getPageName() . "|Protokoll]] der Sitzung vom " . date("d.m.Y", $sitzung->getTimestamp()) . " wird mit -/-/- Stimmen angenommen/abgelehnt.";
		case "naechstesitzung":
			$text = "Die nächsten Termine sind:\n";
			foreach ($args as $arg) {
				$ntimestamp = $this->timestamp + $arg * 24*60*60;
				$text .= "* [[" . $this->organ->getWikiPrefix() . "/Sitzung " . date("Y-m-d", $ntimestamp) . "|Der " . date("d.m.Y", $ntimestamp) . "]]\n";
			}
			return $text;
		case "antraege":
			$antragLines = explode("\n", $this->wikiPage->getText(2));
			// Ueberschrift wegbuxen
			array_shift($antragLines);
			return trim(implode("\n", $antragLines));
		}
	}

	public function prepare() {
		$this->padProtokoll->create();
		$this->padProtokoll->setText($this->getProtokollVorlage());
		// TODO Mail verschicken
	}

	public function save() {
		$protokoll = $this->padProtokoll->getText();
		
		$protokoll = "{{Protokoll}}\n{{Offiziell}}\n" . $protokoll . "\n\n[[Kategorie:Protokoll der Vorstandssitzung des ".$this->organ->getLabel()."s|" . date("Y-m-d", $this->timestamp) . "]]";

		// Announce der naechsten Sitzungen und Update der Uebersichtsseite
		$lastsitzung = $this->timestamp;
		$nextsitzung = -1;
		preg_match_all('$\\[\\[(' . preg_quote($this->organ->getWikiPrefix()) . '/Sitzung[_ ](\\d{4}-\\d{2}-\\d{2}))\\|.*?\\]\\]$', $protokoll, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			$timestamp = strtotime($match[2]);
			if ($timestamp > $lastsitzung and ($nextsitzung < $lastsitzung or $timestamp < $nextsitzung) ) {
				$nextsitzung = $timestamp;
			}
			$page = $this->organ->getMediaWiki()->getPage($match[1]);
			if (!$page->exists()) {
				$page->setText("{{subst:Bundesvorstandssitzung|" . $match[2] . "}}", "Angekündigt laut [[" . $this->wikiProtokollPage->getPageName() . "]]");
			}
		}
		$this->organ->updateSitzungsAnnounce($lastsitzung, $nextsitzung);

		// Aenderungen an Beschluessen ins Wiki laden
		preg_match_all('$^\\* \\[\\[' . preg_quote($this->organ->getWikiPrefix()) . '/Beschluss/(\\d{11}).*\\|.*?\\]\\](\n\\*\\*(\\s*Verantwortlich(.*)|\\s*Erledigt(.*)|.*))*$mi', $protokoll, $beschluesse, PREG_SET_ORDER);
		foreach ($beschluesse as $beschlussStruct) {
			$beschluss = $this->organ->getBeschluss($beschlussStruct[1]);
			if (!empty($beschlussStruct[4])) {
				$beschluss->setVerantwortlicher(trim($beschlussStruct[4], ' :'));
			}
			if (!empty($beschlussStruct[5])) {
				$beschluss->setErledigt(trim($beschlussStruct[5] . " ([[" . $this->wikiProtokollPage->getPageName() . "|Vorstandssitzung vom " . date("d.m.Y", $this->getTimestamp()) . "]])", ' :'));
			}
			$beschluss->save();
		}

		// Gefaellte Beschluesse ins wiki laden
		preg_match_all('${{Antrag.*}}$Ums', $protokoll, $antraege, PREG_SET_ORDER);
		foreach ($antraege as $antrag) {
			$antragVars = getMediaWikiVorlagenVars($antrag[0]);
			if (preg_match('$Angenommen \\(([0-9])/([0-9])/([0-9])\\)$i', $antragVars["beschluss"], $stimmen)) {
				$begruendung = $antragVars["begruendung"];
				if ($begruendung == null) {
					$begruendung = $antragVars["begründung"];
				}
				$beschluss = $this->organ->addBeschluss($this->timestamp, $antragVars["antragstitel"], $antragVars["antragstext"], $stimmen[1], $stimmen[2], $stimmen[3], $begruendung, $antragVars["antragssteller"], $antragVars["zustaendig"]);
				$antragVorlage = str_replace($antragVars["beschluss"], $antragVars["beschluss"] . " [[" . $beschluss->getWikiPage()->getPageName() . "|Beschluss " . $beschluss->getBeschlussNr() . "]]", $antrag[0]);
				$protokoll = str_replace($antrag[0], $antragVorlage, $protokoll);
			}
		}

		$this->wikiProtokollPage->setText($protokoll, "Aus " . $this->padProtokoll->getURL());
		$this->wikiProtokollPage->protect();
	}
}

?>
