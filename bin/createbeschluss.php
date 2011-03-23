#!/usr/bin/php
<?php

if ($_SERVER["argc"] < 4) {
	echo "Usage: createbeschluss.php <date> <titel> <antragsteller> <stimmverhaeltnis>\n";
	echo "STDIN: Antragstext \"\\n--\\n\"\n";
	exit(1);
}

$date = $_SERVER["argv"][1];
$titel = $_SERVER["argv"][2];
$antragsteller = $_SERVER["argv"][3];
list($dafuer, $dagegen, $enthaltung) = explode("/", $_SERVER["argv"][4], 3);
list($antragtext, $zusatz) = explode("\n--\n", file_get_contents("php://stdin"), 2);

$titel = str_replace("|", "{{!}}", $titel);
$antragtext = str_replace("|", "{{!}}", $antragtext);
$zusatz = str_replace("|", "{{!}}", $zusatz);

require_once(dirname(__FILE__) . "/../config.php");

$wiki = Vorstand::getMediaWiki();

function getBeschlussNr($date) {
	$date = str_replace("-", "", $date);
	$wiki = new Mediawiki("http://wiki.junge-piraten.de/w/api.php");
	$pages = $wiki->searchPrefix("Vorstand/Beschluss/" . $date);

	$i = 0;
	foreach ($pages as $page) {
		preg_match('~Beschluss/' . $date . '(\d+) ~', $page, $match);
		$i = max($match[1], $i);
	}
	$i++;
	return $date . str_pad($i, 3, "0", STR_PAD_LEFT);
}

$beschlussnr = getBeschlussNr($date);

$pagetitel = "Vorstand/Beschluss/" . $beschlussnr . " " . $titel;

$page = $wiki->getPage($pagetitel);
$page->setText(<<<EOT
{{Beschluss Seite
|Organ = Bundesvorstand
|Beschluss-Datum = $date
|Beschluss-Nummer = $beschlussnr
|Beschluss-Titel = $titel
|Beschluss-Text = $antragtext
|Zusatzinfos = $zusatz
|Antragssteller = $antragsteller
|ja-Stimmen = $dafuer
|nein-Stimmen = $dagegen
|Enthaltung-Stimmen = $enthaltung
}}
EOT
);

print($pagetitel);

?>
