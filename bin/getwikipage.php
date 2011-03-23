#!/usr/bin/php
<?php

require_once(dirname(__FILE__) . "/config.php");

if ($_SERVER["argc"] > 1) {
	$pagetext = Vorstand::getMediaWiki()->getPage($_SERVER["argv"][1])->getText($_SERVER["argv"][2]);
} else {
	$pagetext = Vorstand::getMediaWiki()->getPage($_SERVER["argv"][1])->getText();
}
if ($pagetext === null) {
	exit(1);
}
print($pagetext);

?>
