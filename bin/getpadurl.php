#!/usr/bin/php
<?php

require_once(dirname(__FILE__) . "/../config.php");

print(Vorstand::getEtherPad()->getPad($_SERVER["argv"][1])->getURL());

?>
