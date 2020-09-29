<?php
define('SERVER_PATH', __DIR__);
define('ROOT_PATH', dirname(SERVER_PATH));
define('LOG_PATH', SERVER_PATH . '/log');
require_once ROOT_PATH . '/vendor/autoload.php';

Use JsonRpc\RpcClient;

echo "---- call ----\n";
$v = RpcClient::instance(['tcp://127.0.0.1:5252'])->call(
    'Test.Test.demo',
    ['call' => 'Test.Test.demo'],
    RpcClient::uuid()
);

var_dump($v);
echo "---- call ----\n";

echo "---- call_notice ----\n";
$v = RpcClient::instance(['tcp://127.0.0.1:5252'])->call('Test.Test.demo',[
    'call' => 'Test.Test.demo',
]);

var_dump($v);
echo "---- call_notice ----\n";


//
//echo "---- async ----\n";
//try {
//    $rpc = RpcClient::instance(['tcp://127.0.0.1:5252']);
//    [$key, $res] = $rpc->asyncSend(
//        'Test.Test.demo',
//        ['async' => 'send'],
//        RpcClient::uuid()
//    );
//
//
//
//    if($res){
//        [$tag, $data] = $rpc->asyncRecv($key);
//        var_dump($tag);
//        var_dump($data);
//    }
//
//
//}catch(\JsonRpc\Exception\MethodAlreadyException $methodAlreadyException){
//    var_dump($methodAlreadyException);
//
//}catch(\JsonRpc\Exception\MethodNotReadyException $methodNotReadyException){
//    var_dump($methodNotReadyException);
//}
//echo "---- async ----\n";
//
//echo "---- async_notice ----\n";
//try {
//    [$key, $res] = $rpc->asyncNoticeSend(
//        'Test.Test.demo',
//        ['async' => 'notice_send'],
//        false
//    );
//    if($res){
//        [$tag, $data] = $rpc->asyncRecv($key);
//        var_dump($tag);
//        var_dump($data);
//    }
//
//
//}catch(\JsonRpc\Exception\MethodAlreadyException $methodAlreadyException){
//    var_dump($methodAlreadyException);
//}catch(\JsonRpc\Exception\MethodNotReadyException $methodNotReadyException){
//    var_dump($methodNotReadyException);
//
//}
//echo "---- async_notice ----\n";



