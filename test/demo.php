<?php
define('SERVER_PATH', __DIR__);
define('ROOT_PATH', dirname(SERVER_PATH));
define('LOG_PATH', SERVER_PATH . '/log');
require_once ROOT_PATH . '/vendor/autoload.php';
function getCurrentTime() {
    list ($msec, $sec) = explode(" ", microtime());
    return (float)$msec + (float)$sec;
}
echo "---- call_notice ----\n";
$start = getCurrentTime();
$client = \JsonRpc\RpcClient::instance(['tcp://localhost:5454']);
$client->setTimeout(0);
$v = $client->call('Server.UserExecute.UserAssetCheck',[
    'call' => 'Server.UserServer.checker',
]);

var_dump($v);
echo getCurrentTime() - $start;
echo "---- call_notice ----\n";