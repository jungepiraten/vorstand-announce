#!/usr/bin/php
<?php

require_once(dirname(__FILE__) . "/config.php");

Vorstand::getEtherPad()->createPad($_SERVER["argv"][1]);

?>
