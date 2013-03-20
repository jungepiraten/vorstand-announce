<?php

require_once(dirname(__FILE__) . "/sitzung.class.php");
require_once(dirname(__FILE__) . "/beschluss.class.php");
require_once(dirname(__FILE__) . "/projekt.class.php");

class Organ {
	protected $label;
	protected $wiki;
	protected $wikiPrefix;
	protected $pad;
	protected $padPrefix;

	public function __construct($label, $wiki, $wikiPrefix, $pad, $padPrefix) {
		$this->label = $label;
		$this->wiki = $wiki;
		$this->wikiPrefix = $wikiPrefix;
		$this->pad = $pad;
		$this->padPrefix = $padPrefix;
	}

	public function getLabel() {
		return $this->label;
	}

	public function getMediaWiki() {
		return $this->wiki;
	}

	public function getWikiPrefix() {
		return $this->wikiPrefix;
	}

	public function getEtherPad() {
		return $this->pad;
	}

	public function getPadPrefix() {
		return $this->padPrefix;
	}

	public function getSitzung($timestamp) {
		$wikiprefix = $this->wikiPrefix . "/Sitzung " . date("Y-m-d", $timestamp);
		return new Sitzung($this, $this->wiki->getPage($wikiprefix), $this->wiki->getPage($wikiprefix . "/Protokoll"), $this->pad->getPad($this->padPrefix . "-" . date("Y-m-d", $timestamp)), $timestamp);
	}

	public function getSitzungBefore($timestamp) {
		// Sicherheitszaehler, falls es noch gar keine Sitzung gab
		$i = 0;
		do {
			$sitzung = $this->getSitzung($timestamp);
			$timestamp -= 24*60*60;
		} while (!$sitzung->exists() and $i++ < 60);
		if ($sitzung->exists()) {
			return $sitzung;
		}
		return null;
	}

	public function getBeschluss($beschlussnr) {
		$seiten = $this->wiki->searchPrefix($this->wikiPrefix . "/Beschluss/" . $beschlussnr);
		foreach ($seiten as $wikiprefix) {
			$beschluss = new Beschluss($this, $this->wiki->getPage($wikiprefix), $beschlussnr);
			if ($beschluss->exists()) {
				return $beschluss;
			}
		}
		return null;
	}

	public function getBeschluesse($lowtimestamp, $hightimestamp) {
		$beschluesse = array();
		for ($timestamp = $lowtimestamp; $timestamp <= $hightimestamp; $timestamp += 24*60*60) {
			$pages = $this->wiki->searchPrefix($this->wikiPrefix . "/Beschluss/" . date("Ymd", $timestamp));
			foreach ($pages as $page) {
				preg_match('#Beschluss/(\\d*)#', $page, $match);
				$beschluss = $this->getBeschluss($match[1]);
				if ($beschluss->exists()) {
					$beschluesse[] = $beschluss;
				}
			}
		}
		return $beschluesse;
	}

	public function getLaufendeBeschluesse() {
		$beschluesse = array();
		$pages = $this->wiki->getPagesByCategory("Nicht erledigter Beschluss " . $this->getLabel());
		sort($pages);
		foreach ($pages as $page) {
			preg_match('#Beschluss/(\\d*)#', $page, $match);
			$beschluss = $this->getBeschluss($match[1]);
			if ($beschluss->exists()) {
				$beschluesse[] = $beschluss;
			}
		}
		return $beschluesse;
	}

	private function getNextBeschlussNr($timestamp) {
		$pages = $this->wiki->searchPrefix($this->wikiPrefix . "/Beschluss/" . date("Ymd", $timestamp));
		$i = 0;
		foreach ($pages as $page) {
			preg_match('~Beschluss/' . date("Ymd", $timestamp) . '(\d+) ~', $page, $match);
			$i = max($match[1], $i);
		}
		$i++;
		return date("Ymd", $timestamp) . str_pad($i, 3, "0", STR_PAD_LEFT);
	}

	public function addBeschluss($timestamp, $titel, $antragtext, $dafuer, $dagegen, $enthaltung, $zusatz, $antragsteller, $zustaendig) {
		$beschlussnr = $this->getNextBeschlussNr($timestamp);
		$titel = str_replace("|", "{{!}}", $titel);
		$antragtext = str_replace("|", "{{!}}", $antragtext);
		$zusatz = str_replace("|", "{{!}}", $zusatz);

		$date = date("Y-m-d", $timestamp);
		
		$pagetitel = $this->wikiPrefix . "/Beschluss/" . $beschlussnr . " " . $titel;
		$page = $this->wiki->getPage($pagetitel);
		$beschluss = new Beschluss($this, $page, $beschlussnr);
		$beschluss->setTimestamp($timestamp);
		$beschluss->setText($antragtext);
		$beschluss->setStimmen($dafuer, $dagegen, $enthaltung);
		$beschluss->setZusatz($zusatz);
		$beschluss->setAntragsteller($antragsteller);
		$beschluss->setVerantwortlicher($zustaendig);
		$beschluss->save();
		return $beschluss;
	}

	public function getProjekt($year, $titel) {
		return new Projekt($this, $this->wiki->getPage($this->wikiPrefix . "/Projekt/" . $year . " " . $titel), $year, $titel);
	}

	public function getLaufendeProjekte() {
		$projekte = array();
		$pages = $this->wiki->getPagesByCategory("Nicht abgeschlossenes Projekt " . $this->getLabel());
		foreach ($pages as $page) {
			preg_match('#Projekt/(\\d{4}) (.*)$#', $page, $match);
			$projekt = $this->getProjekt($match[1], $match[2]);
			if ($projekt->exists()) {
				$projekte[] = $projekt;
			}
		}
		return $projekte;
	}

	public function updateSitzungsAnnounce($lastsitzung, $nextsitzung) {
		return;
	}
}

?>
