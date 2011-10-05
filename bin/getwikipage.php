#!/usr/bin/php
<?php

require_once(dirname(__FILE__) . "/../config.php");

if ($_SERVER["argc"] > 1) {
	$pagetext = $vorstand->getMediaWiki()->getPage($_SERVER["argv"][1])->getText($_SERVER["argv"][2]);
} else {
	$pagetext = $vorstand->getMediaWiki()->getPage($_SERVER["argv"][1])->getText();
}
if ($pagetext === null) {
	exit(1);
}
print($pagetext);

?>
