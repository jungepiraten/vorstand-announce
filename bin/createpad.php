#!/usr/bin/php
<?php

require_once(dirname(__FILE__) . "/../config.php");

$vorstand->getEtherPad()->createPad($_SERVER["argv"][1]);

?>
