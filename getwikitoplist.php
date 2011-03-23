#!/usr/bin/php
<?php

$date = $_SERVER["argv"][1];

function formatWiki2Pad($wiki) {
	$wiki = explode("\n", $wiki);
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
	return $pad;
}

require_once(dirname(__FILE__) . "/config.php");

print("== Anwesenheit ==" . "\n");
$tagesordnung = Vorstand::getMediaWiki()->getPage("Vorstand/Sitzung " . $date)->getText(1);
print(formatWiki2Pad($tagesordnung));

$antraege = Vorstand::getMediaWiki()->getPage("Vorstand/Sitzung " . $date)->getText(2);
print($antraege);
print("\n");

?>
