#!/usr/bin/php
<?php

$date = $_SERVER["argv"][1];
$protokoll = file_get_contents("php://stdin");

require_once(dirname(__FILE__) . "/config.php");

/**
 * Finde zukuenftige VoSis
 **/
preg_match_all('$\\[\\[(Vorstand/Sitzung[_ ](\\d{4}-\\d{2}-\\d{2}))\\|.*?\\]\\]$', $protokoll, $matches, PREG_SET_ORDER);
foreach ($matches as $match) {
	$page = Vorstand::getMediaWiki()->getPage($match[1]);
	if (!$page->exists()) {
		$page->setText("{{subst:Bundesvorstandssitzung|" . $match[2] . "}}", "Angekündigt laut [[Vorstand/Sitzung " . $date . "/Protokoll]]");
	}
}

print($protokoll);

?>
