#!/usr/bin/php
<?php

require_once(dirname(__FILE__) . "/../config.php");

$vorstand->getEtherPad()->getPad($_SERVER["argv"][1])->setText(file_get_contents("php://stdin"));

?>
