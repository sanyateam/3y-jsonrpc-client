# wm-jsonrpc-client

***
A JsonRpc-client for WorkerMan

- 基于TCP协议通讯
- JsonRpc-2.0业务协议
- 适用于PHP-FPM/PHP-CLI的jsonRpc 应-答 模式客户端
- ext-sockets、ext-json
***

## 1.阻塞调用

- 业务会等待服务器业务应答结果

#### 1. 请求 call(method, arguments, id)
- method (string) [必要]: 调用方式
- arguments (array) [必要]: 参数
- id (string) [非必要]: ID  **该项不传入时，为通知类型请求**

````
# Notice
$res = RpcClient::instance(['tcp://localhost:5252'])->call(
    'Server.UserServer.getUser',
    ['call' => 'Server.UserServer.getUser']
);

# request
$res = RpcClient::instance(['tcp://localhost:5252'])->call(
    'Server.UserServer.getUser',
    ['call' => 'Server.UserServer.getUser'],
    RpcClient::uuid()
);
````
## 2.拆分请求

- 业务无须阻塞等待等待服务器业务应答结果
- 业务拆分为发送、接收
- 在PHP-FPM下仅可在一个上下文中使用发送-接收异步执行业务
- 在workerman/swoole下，只要进程不退出，都可以使用发送-接收执行业务

#### 1. 发送请求 asyncSend(method, arguments, id)
- method (string) [必要]: 调用方式
- param (array) [必要]: 参数
- id (string) [必要]: ID

**注：**
- 在接收前重复的同个ID执行，会抛出一个MethodAlreadyException异常

````
# request
try {
    $rpc = RpcClient::instance(['tcp://localhost:5252']);
    [$key1, $res] = $rpc->asyncSend(
        'Test.Test.demo',
        ['async' => 'send1'],
        RpcClient::uuid()
    );

    # 如果是重复的ID，则会抛出一个MethodAlreadyException异常
    [$key2, $res] = $rpc->asyncSend(
        'Test.Test.demo',
        ['async' => 'send2'],
        RpcClient::uuid()
    );

    [$key3, $res] = $rpc->asyncSend(
        'Test.Test.demo',
        ['async' => 'send3'],
        RpcClient::uuid()
    );
}catch(\JsonRpc\Exception\MethodAlreadyException $methodAlreadyException){

}
````
#### 2. 发送通知 asyncNoticeSend(method, arguments, sole = true)
- method (string) [必要]: 调用方式
- param (array) [必要]: 参数
- sole (bool) [非必要]: 是否开启调用方式唯一执行

**注：**
- 如果开启调用方式唯一执行，在执行接收前重复同个method会抛出一个MethodAlreadyException异常
````
# request
try {
    [$key, $res] = $rpc->asyncNoticeSend(
        'Test.Test.demo',
        ['async' => 'notice_send'],
        true
    );

    # 如果开启调用方式唯一执行，以下会抛出一个MethodAlreadyException异常
    [$key, $res] = $rpc->asyncNoticeSend(
        'Test.Test.demo',
        ['async' => 'notice_send'],
        true
    );
    
}catch(\JsonRpc\Exception\MethodAlreadyException $methodAlreadyException){

}
````

#### 3. 接收 asyncRecv(key)
- key (string) [必要]: 发送方式返回的key值

**注：**
- 如果调用的key值不存在，会抛出一个MethodNotReadyException异常
- 成功调用后，会将key值失效

````
# request
try {
    # 如果调用的key值不存在，则会抛出一个MethodNotReadyException异常
    [$tag, $data] = $rpc->asyncRecv($key);
    
}catch(\JsonRpc\Exception\MethodNotReadyException $methodAlreadyException){

}
````
***
## 补充

