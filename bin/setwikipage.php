#!/usr/bin/php
<?php

require_once(dirname(__FILE__) . "/config.php");

Vorstand::getMediaWiki()->getPage($_SERVER["argv"][1])->setText(file_get_contents("php://stdin"), $_SERVER["argv"][2]);

?>
