#!/usr/bin/php
<?php

if ($_SERVER["argc"] < 4) {
	echo "Usage: createbeschluss.php <date> <titel> <antragsteller> <stimmverhaeltnis>\n";
	echo "STDIN: Antragstext \"\\n--\\n\"\n";
	exit(1);
}

$timestamp = strptime($_SERVER["argv"][1], "%Y-%m-%d");
$timestamp = mktime($timestamp["tm_hour"], $timestamp["tm_min"], $timestamp["tm_sec"], $timestamp["tm_mon"] + 1, $timestamp["tm_mday"], $timestamp["tm_year"] + 1900);
$titel = $_SERVER["argv"][2];
$antragsteller = $_SERVER["argv"][3];
list($dafuer, $dagegen, $enthaltung) = explode("/", $_SERVER["argv"][4], 3);
list($antragtext, $zusatz) = explode("\n--\n", file_get_contents("php://stdin"), 2);

require_once(dirname(__FILE__) . "/../config.php");

print($vorstand->addBeschluss($timestamp, $titel, $antragtext, $dafuer, $dagegen, $enthaltung, $zusatz, $antragsteller));

?>
