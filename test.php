<?php
require_once __DIR__.'/vendor/autoload.php';
$class = \JsonRpc\Format\JsonFmt::factory();
$class->method = 1;

$class->params = 1;
$class->error = 1;
$class->result = 1;

var_dump($class->outputArrayByKey($class::FILTER_STRICT,$class::TYPE_REQUEST));
