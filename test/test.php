<?php
define('SERVER_PATH', __DIR__);
define('ROOT_PATH', dirname(SERVER_PATH));
define('LOG_PATH', SERVER_PATH . '/log');
require_once ROOT_PATH . '/vendor/autoload.php';

Use JsonRpc\RpcClient;

function getCurrentTime() {
    list ($msec, $sec) = explode(" ", microtime());
    return (float)$msec + (float)$sec;
}

//echo "---- send ----\n";
//$client = RpcClient::instance(['tcp://localhost:5252']);
//var_dump(RpcClient::getInstance());
//
//RpcClient::instance(['tcp://localhost:5252'])->send('{"jsonrpc":"2.0","id":"039f946b-1e7c-258e-9755-f445ae7a4fd5",');
//var_dump(RpcClient::getInstance());
//RpcClient::instance(['tcp://localhost:5252'])->send('"method":"Test.Test.demo","params":{"call":"Test.Test.demo2"}}'."\n");
//var_dump(RpcClient::getInstance());
//RpcClient::instance(['tcp://localhost:5252'])->send('{"jsonrpc":"2.0","id":"039f946b-1e7c-258e-9755-f445ae7a4fd1",');
//var_dump(RpcClient::getInstance());
//RpcClient::instance(['tcp://localhost:5252'])->send('"method":"Test.Test.demo","params":{"call":"Test.Test.demo1"}}'."\n");
//var_dump(RpcClient::getInstance());
//$v = RpcClient::instance(['tcp://localhost:5252'])->get();
//
//var_dump($v);
//echo "---- send ----\n";

//echo "---- call ----\n";
//$v = RpcClient::instance(['tcp://localhost:5252'])->call(
//    'Reject.Test.demo',
//    ['call' => 'Reject.Test.demo'],
//    RpcClient::uuid()
//);
//
//var_dump($v);
//
//$v = RpcClient::instance(['tcp://localhost:5252'])->call(
//    'Test.Test.demo',
//    ['call' => 'Test.Test.demo'],
//    RpcClient::uuid()
//);
//
//var_dump($v);
//
//$v = RpcClient::instance(['tcp://localhost:5252'])->call(
//    'MethodNotFound.Test.demo',
//    ['call' => 'MethodNotFound.Test.demo'],
//    RpcClient::uuid()
//);
//
//var_dump($v);
//echo "---- call ----\n";

//echo "---- call_notice ----\n";
//$v = RpcClient::instance(['tcp://localhost:5252'])->call('Server.UserServer.checker',[
//    'call' => 'Server.UserServer.checker',
//]);
//echo "---- call_notice ----\n";

echo "---- async ----\n";
$start = getCurrentTime();
try {
    $rpc = RpcClient::instance(['tcp://localhost:5252']);
    [$key1, $res] = $rpc->asyncSend(
        'Server.UserServer.checker',
        ['async' => 'send1'],
        RpcClient::uuid()
    );

    [$key2, $res] = $rpc->asyncSend(
        'Server.UserServer.checker1',
        ['async' => 'send2'],
        RpcClient::uuid()
    );

    [$tag, $data] = $rpc->asyncRecv($key1);
    var_dump($tag);
    var_dump($data);

    [$tag, $data] = $rpc->asyncRecv($key2);
    var_dump($tag);
    var_dump($data);

    $rpc->close();

}catch(\JsonRpc\Exception\MethodAlreadyException $methodAlreadyException){
    var_dump($methodAlreadyException);

}catch(\JsonRpc\Exception\MethodNotReadyException $methodNotReadyException){
    var_dump($methodNotReadyException);
}
echo getCurrentTime() - $start . PHP_EOL;
echo "---- async ----\n";

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
//    $rpc->close();
//
//
//}catch(\JsonRpc\Exception\MethodAlreadyException $methodAlreadyException){
//    var_dump($methodAlreadyException);
//}catch(\JsonRpc\Exception\MethodNotReadyException $methodNotReadyException){
//    var_dump($methodNotReadyException);
//
//}
//echo "---- async_notice ----\n";



