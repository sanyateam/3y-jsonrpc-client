<?php
define('SERVER_PATH', __DIR__);
define('ROOT_PATH', dirname(SERVER_PATH));
define('LOG_PATH', SERVER_PATH . '/log');
require_once ROOT_PATH . '/vendor/autoload.php';

var_dump($a = [0,1,33]);
var_dump(\Protocols\JsonRpc2::isAssoc($a));
var_dump($a = [0 => '1',1 => '3']);
var_dump(\Protocols\JsonRpc2::isAssoc($a));
var_dump($a = ['0' => 0,'1' => 1]);
var_dump(\Protocols\JsonRpc2::isAssoc($a));
var_dump($a = ['1' => 0,'2' => 1]);
var_dump(\Protocols\JsonRpc2::isAssoc($a));