<?php
define('SERVER_PATH', __DIR__);
define('ROOT_PATH', dirname(SERVER_PATH));
define('LOG_PATH', SERVER_PATH . '/log');
require_once ROOT_PATH . '/vendor/autoload.php';

Use JsonRpc\RpcClient;

$v = RpcClient::instance(['tcp://127.0.0.1:5252'])->call('Test.Test.demo',[
    'a' => '1',
    'b' => '2'
]);

var_dump($v);