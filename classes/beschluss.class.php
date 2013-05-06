<?php

class Beschluss {
	protected $organ;
	protected $wikiPage;
	protected $beschlussnr;
	protected $titel;

	protected $timestamp;
	protected $text;
	protected $zusatz;
	protected $antragsteller;
	protected $dafuer;
	protected $dagegen;
	protected $enthaltung;
	protected $verantwortlicher;
	protected $erledigt;
	
	public function __construct($organ, $wikiPage, $beschlussnr) {
		$this->organ = $organ;
		$this->wikiPage = $wikiPage;
		$this->beschlussnr = $beschlussnr;
		$this->titel = substr($wikiPage->getPageName(), strpos($wikiPage->getPageName(), $beschlussnr) + strlen($beschlussnr) + 1);
	}

	public function loadIfNeeded() {
		if ($this->timestamp === null) {
			$this->load();
		}
	}

	public function exists() {
		$this->loadIfNeeded();
		return $this->timestamp !== null;
	}

	public function getBeschlussNr() {
		return $this->beschlussnr;
	}

	public function getTimestamp() {
		$this->loadIfNeeded();
		return $this->timestamp;
	}
	
	public function setTimestamp($timestamp) {
		$this->timestamp = $timestamp;
	}

	public function getTitel() {
		return $this->titel;
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
		$this->loadIfNeeded();
		$this->antragsteller = $antragsteller;
	}

	public function getDafuer() {
		$this->loadIfNeeded();
		return $this->dafuer;
	}

	public function getDagegen() {
		$this->loadIfNeeded();
		return $this->dagegen;
	}

	public function getEnthaltung() {
		$this->loadIfNeeded();
		return $this->enthaltung;
	}

	public function setStimmen($dafuer, $dagegen, $enthaltung) {
		$this->dafuer = $dafuer;
		$this->dagegen = $dagegen;
		$this->enthaltung = $enthaltung;
	}

	public function hasVerantwortlicher() {
		return $this->verantwortlicher != null;
	}

	public function getVerantwortlicher() {
		$this->loadIfNeeded();
		return $this->verantwortlicher;
	}

	public function setVerantwortlicher($verantwortlicher) {
		$this->loadIfNeeded();
		$this->verantwortlicher = $verantwortlicher;
	}

	public function isErledigt() {
		$this->loadIfNeeded();
		return $this->erledigt != null;
	}

	public function getErledigt() {
		$this->loadIfNeeded();
		return $this->erledigt;
	}

	public function setErledigt($erledigt) {
		$this->loadIfNeeded();
		$this->erledigt = $erledigt;
	}

	public function getWikiPage() {
		return $this->wikiPage;
	}

	public function load() {
		$values = $this->wikiPage->getVorlagenVars();
		if ($values == null) {
			return;
		}

		$this->timestamp = strtotime($values["Beschluss-Datum"]);
		$this->text = trim($values["Beschluss-Text"]);
		$this->zusatz = trim($values["Zusatzinfos"]);
		$this->antragsteller = trim($values["Antragssteller"]);
		$this->dafuer = trim($values["ja-Stimmen"]);
		$this->dagegen = trim($values["nein-Stimmen"]);
		$this->enthaltung = trim($values["Enthaltung-Stimmen"]);
		if (isset($values["Zustaendig"])) {
			$this->verantwortlicher = trim($value["Zustaendig"]);
		}
		if (isset($values["erledigt"])) {
			$this->erledigt = trim($value["erledigt"]);
		}
	}
	
	public function save() {
		$date = date("Y-m-d", $this->getTimestamp());
		
//		var_dump("{{Beschluss Seite
		$this->wikiPage->setText("{{Beschluss Seite
|Organ = {$this->organ->getLabel()}
|Beschluss-Datum = {$date}
|Beschluss-Text = {$this->getText()}
|Zusatzinfos = {$this->getZusatz()}
|Antragssteller = {$this->getAntragsteller()}
|ja-Stimmen = {$this->getDafuer()}
|nein-Stimmen = {$this->getDagegen()}
|Enthaltung-Stimmen = {$this->getEnthaltung()}
"       . ($this->hasVerantwortlicher() ? "|Zustaendig = {$this->getVerantwortlicher()}
" : "") . ($this->isErledigt() ? "|erledigt = {$this->getErledigt()}
" : "") . "}}");
	}
}

?>
