#!/usr/bin/php
<?php

$timestamp = strptime($_SERVER["argv"][1], "%Y-%m-%d");
$timestamp = mktime($timestamp["tm_hour"], $timestamp["tm_min"], $timestamp["tm_sec"], $timestamp["tm_mon"] + 1, $timestamp["tm_mday"], $timestamp["tm_year"] + 1900);

require_once(dirname(__FILE__) . "/../config.php");

$vorstand->getSitzung($timestamp)->save();

?>
