<?php

class Projekt {
	protected $organ;
	protected $wikiPage;
	protected $jahr;
	protected $titel;

	protected $beschreibung;
	protected $verantwortlich;
	protected $erledigt;
	
	public function __construct($organ, $wikiPage, $jahr, $titel) {
		$this->organ = $organ;
		$this->wikiPage = $wikiPage;
		$this->jahr = $jahr;
		$this->titel = $titel;
	}

	public function loadIfNeeded() {
		if ($this->beschreibung === null) {
			$this->load();
		}
	}

	public function exists() {
		$this->loadIfNeeded();
		return $this->beschreibung !== null;
	}

	public function getJahr() {
		return $this->jahr;
	}

	public function getTitel() {
		return $this->titel;
	}

	public function getBeschreibung() {
		$this->loadIfNeeded();
		return $this->beschreibung;
	}

	public function setBeschreibung($beschreibung) {
		$this->beschreibung = $beschreibung;
	}

	public function hasVerantwortlicher() {
		return $this->verantwortlich != null;
	}

	public function getVerantwortlicher() {
		$this->loadIfNeeded();
		return $this->verantwortlich;
	}

	public function setVerantwortlicher($verantwortlich) {
		$this->loadIfNeeded();
		$this->verantwortlich = $verantwortlich;
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

		$this->beschreibung = trim($values["Beschreibung"]);
		if (isset($values["Verantwortlich"])) {
			$this->verwantwortlich = trim($value["Verantwortlich"]);
		}
		if (isset($values["erledigt"])) {
			$this->erledigt = trim($value["erledigt"]);
		}
	}
	
	public function save() {
//		var_dump("{{Projekt
		$this->wikiPage->setText("{{Projekt
|Organ = {$this->organ->getLabel()}
|Beschreibung = {$this->getBeschreibung()}
"       . ($this->hasVerantwortlicher() ? "|Verantwortlich = {$this->getVerantwortlicher()}
" : "") . ($this->isErledigt() ? "|erledigt = {$this->getErledigt()}
" : "") . "}}", "edited", 0);
	}

	public function addSection($section, $text) {
		$this->wikiPage->setText($text, "new section", "new:" . $section);
	}
}

?>
