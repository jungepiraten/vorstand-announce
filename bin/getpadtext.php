#!/usr/bin/php
<?php

require_once(dirname(__FILE__) . "/../config.php");

print($vorstand->getEtherPad()->getPad($_SERVER["argv"][1])->getText());

?>
