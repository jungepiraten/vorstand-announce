#!/usr/bin/php
<?php

require_once(dirname(__FILE__) . "/../config.php");

print(Vorstand::getMediaWiki()->getPage($_SERVER["argv"][1])->getURL());

?>
