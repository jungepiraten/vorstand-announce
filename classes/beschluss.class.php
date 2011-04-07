<?php

class Beschluss {
	protected $organ;
	protected $wikiPage;
	protected $beschlussnr;

	protected $timestamp;
	protected $titel;
	protected $text;
	protected $zusatz;
	protected $antragsteller;
	protected $dafuer;
	protected $dagegen;
	protected $enthaltung;
	
	public function __construct($organ, $wikiPage, $beschlussnr) {
		$this->organ = $organ;
		$this->wikiPage = $wikiPage;
		$this->beschlussnr = $beschlussnr;
	}

	public function loadIfNeeded() {
		if ($this->titel == null) {
			$this->load();
		}
	}

	public function getBeschlussNr() {
		return $this->beschlussnr;
	}

	public function getTitel() {
		$this->loadIfNeeded();
		return $this->titel;
	}

	public function setTitel($titel) {
		$this->titel = $titel;
	}

	public function getText() {
		$this->loadIfNeeded();
		return $this->text;
	}

	public function setText($text) {
		$this->text = $text;
	}

	public function getZusatz() {
		$this->loadIfNeeded();
		return $this->zusatz;
	}

	public function setZusatz($zusatz) {
		$this->zusatz = $zusatz;
	}

	public function getAntragsteller() {
		$this->loadIfNeeded();
		return $this->antragsteller;
	}

	public function setAntragsteller($antragsteller) {
		$this->antragsteller = $antragsteller;
	}

	public function getDafuer() {
		$this->loadAsNeeded();
		return $this->dafuer;
	}

	public function getDagegen() {
		$this->loadAsNeeded();
		return $this->dagegen;
	}

	public function getEnthaltung() {
		$this->loadAsNeeded();
		return $this->enthaltung;
	}

	public function setStimmen($dafuer, $dagegen, $enthaltung) {
		$this->dafuer = $dafuer;
		$this->dagegen = $dagegen;
		$this->enthaltung = $enthaltung;
	}

	public function getWikiPage() {
		return $this->wikiPage;
	}

	public function getTimestamp() {
		return $this->timestamp;
	}

	public function load() {
		$values = $this->wikiPage->getVorlagenVars();
		if ($values == null) {
			return;
		}
		
		$this->timestamp = strtotime($values["Beschluss-Datum"]);
		$this->beschlussnr = trim($values["Beschluss-Nummer"]);
		$this->titel = trim($values["Beschluss-Titel"]);
		$this->text = trim($values["Beschluss-Text"]);
		$this->zusatz = trim($values["Zusatzinfos"]);
		$this->antragsteller = trim($values["Antragssteller"]);
		$this->dafuer = trim($values["ja-Stimmen"]);
		$this->dagegen = trim($values["nein-Stimmen"]);
		$this->enthaltung = trim($values["Enthaltung-Stimmen"]);
	}
	
	public function save() {
		$date = date("Y-m-d", $this->timestamp);
		
		$this->wikiPage->setText(<<<EOT
{{Beschluss Seite
|Organ = {$this->organ->getLabel()}
|Beschluss-Datum = {$date}
|Beschluss-Nummer = {$this->getBeschlussNr()}
|Beschluss-Titel = {$this->getTitel()}
|Beschluss-Text = $antragtext
|Zusatzinfos = $zusatz
|Antragssteller = $antragsteller
|ja-Stimmen = $dafuer
|nein-Stimmen = $dagegen
|Enthaltung-Stimmen = $enthaltung
}}
EOT
		);
	}
}

?>
