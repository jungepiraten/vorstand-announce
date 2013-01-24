#!/usr/bin/php
<?php

require_once(dirname(__FILE__) . "/../config.php");

$vorstand->getMediaWiki()->getPage($_SERVER["argv"][1])->move($_SERVER["argv"][2]);

?>
