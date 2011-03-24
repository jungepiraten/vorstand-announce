<?php

class Sitzung {
	private $wikiPage;
	private $wikiProtokollPage;
	private $padProtokoll;
	private $timestamp;

	public function __construct($wikiPage, $wikiProtokollPage, $padProtokoll, $timestamp) {
		$this->wikiPage = $wikiPage;
		$this->wikiProtokollPage = $wikiProtokollPage;
		$this->padProtokoll = $padProtokoll;
		$this->date = $timestamp;
	}

	public function getInfos() {
		return $this->wikiPage->getText(0);
	}

	public function getProtokollVorlage() {
		$pad = "== Anwesend ==" . "\n";
		$wiki = explode("\n", $this->wikiPage->getText(1));
		foreach ($wiki as $wikiline) {
			$i = 0;
			do {
				$char = $wikiline{$i++};
			} while ($char == "#");

			$level = $i;
			$label = trim(substr($wikiline, $i-1));

			if (trim($label) != "") {
				if ($label{0} == "*" or ($i <= 1)) {
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
		// TODO Mail verschicken
	}

	public function save() {
		$this->wikiProtokollPage->setText($this->padProtokoll->getText(), "Aus " . $this->padProtokoll->getURL());
		$this->wikiProtokollPage->protect();
		// TODO Mail verschicken
	}
}

?>
