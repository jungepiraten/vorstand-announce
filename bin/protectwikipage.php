#!/usr/bin/php
<?php

require_once(dirname(__FILE__) . "/config.php");

Vorstand::getMediaWiki()->getPage($_SERVER["argv"][1])->protect();

?>
