<?php

namespace JsonRpc;

use JsonRpc\Exception\InternalErrorException;
use JsonRpc\Exception\InvalidRequestException;
use JsonRpc\Exception\MethodAlreadyException;
use JsonRpc\Exception\MethodNotFoundException;
use JsonRpc\Exception\RpcException;
use JsonRpc\Exception\ServerErrorException;
use JsonRpc\Format\ErrorFmt;
use JsonRpc\Format\JsonFmt;
use Protocols\JsonRpc2;

class RpcClient {

    /**
     * @var int 发送数据和接收数据的超时时间  单位S
     */
    const TIME_OUT = 5;

    /**
     * @var array 服务端地址
     */
    protected static $_addressArray = [];

    /**
     * @var string 异步调用实例
     */
    protected static $_asyncInstances = [];
    /**
     * @var RpcClient 同步调用实例
     */
    protected static $_instances = null;
    /**
     * @var resource 服务端的socket连接
     */
    protected $_connection = null;
    protected $_prepares   = false;
    protected $_buffer     = null;
    protected $_timeout    = self::TIME_OUT;

    /**
     * RpcClient constructor.
     * @param array $address
     */
    protected function __construct(array $address) {
        if($address){
            self::$_addressArray = $address;
        }
        $this->register();
    }

    /**
     * 注册异常响应
     */
    public function register() {
        spl_autoload_register([$this, '_autoload']);
    }

    /**
     * @param array $address
     * @return RpcClient
     */
    public static function instance(array $address = []) {

        if(!isset(self::$_instances) or self::$_instances instanceof RpcClient) {
            self::$_instances = new self($address);
        }
        return self::$_instances;
    }

    /**
     * 设置是否本地预处理服务器返回值
     * @param bool $prepares
     * @return $this
     */
    public function prepares(bool $prepares){
        $this->_prepares = $prepares;
        return $this;
    }

    /**
     * 获取缓冲区数据
     * @return string
     */
    public function getBuffer(){
        return $this->_buffer;
    }

    /**
     * 设置缓冲区数据
     * @param $buffer
     */
    public function setBuffer($buffer){
        $this->_buffer = $buffer;
    }

    /**
     * 获取超时时间
     * @return string
     */
    public function getTimeout(){
        return $this->_timeout;
    }

    /**
     * 设置超时时间
     * @param int $time
     */
    public function setTimeout(int $time){
        $this->_timeout = $time;
    }

    /**
     * @param $method
     * @param $arguments
     * @param string $id
     * @return bool|JsonFmt
     *
     * JsonEmt.object 表示有异常
     * true           表示成功
     *
     * @throws MethodAlreadyException
     */
    public function asyncSend($method, $arguments, $id = '') {
        $key = $id ? $method.$id : $method;
        if(
            isset(self::$_asyncInstances[$key]) and
            self::$_asyncInstances[$key]
        ) {
            throw new MethodAlreadyException("{$method}->{$id}");
        }
        self::$_asyncInstances[$key] = true;

        return self::instance(self::$_addressArray)->_sendData($method, $arguments, $id);
    }

    /**
     * @param string $method
     * @param string $id
     * @return array|bool|null
     *
     * array 表示成功
     * null 表示服务器非json-rpc2.0标准
     * bool 表示服务内部错误
     *
     * @throws MethodNotFoundException
     */
    public function asyncRecv(string $method, $id = '') {
        $key = $id ? $method.$id : $method;
        if(
            !isset(self::$_asyncInstances[$key]) or
            self::$_asyncInstances[$key] !== true
        ) {
            throw new MethodNotFoundException("{$method}->{$id}");

        }
        self::$_asyncInstances[$key] = null;

        return self::instance(self::$_addressArray)->_recvData();
    }

    /**
     * 同步
     * @param string $method
     * @param array $arguments
     * @param string $id
     * @return array
     */
    public function call(string $method, array $arguments, $id = '') {
        if(!$res = $this->_sendData($method, $arguments, $id)){
            return $this->_res($res->outputArray(),null);
        }
        $res = $this->_recvData();
        if($res !== false and $res !== null){
            return $this->_res($res, true);
        }
        return $this->_res([], $res);
    }

    /**
     * @param array $data 数据
     * @param bool $tag 标记
     * @return array
     */
    protected function _res($data, $tag){
        return [
            $tag,
            $data
        ];
    }

    /**
     * @param $class
     * @throws \Exception
     */
    protected function _autoload($class) {
        throw new \Exception("class {$class} not found",'-1');
    }

    /**
     * 发送数据给服务端
     * @param $method
     * @param $arguments
     * @param $id
     * @return bool|JsonFmt
     *
     * JsonEmt.object 表示有异常
     * true           表示成功
     */
    protected function _sendData(string $method, array $arguments, $id = '') {
        $fmt         = JsonFmt::factory();
        $fmt->method = $method;
        $fmt->params = $arguments;
        $fmt->id     = $id ? $id : null;
        $error       = ErrorFmt::factory();
        try {
            $json = JsonRpc2::encode($fmt->outputArray($fmt::FILTER_STRICT));
            # 发送数据
            if(fwrite($this->_openConnection(), $json) !== strlen($json)) {
                throw new InvalidRequestException();
            }
        }catch(RpcException $rpcException){
            $error->code    = $rpcException->getCode();
            $error->message = $rpcException->getMessage();
            $fmt->error     = $error->outputArray();
            return $fmt;
        }catch(\Exception $exception){
            $serverException = new ServerErrorException();
            $error->code    = $serverException->getCode();
            $error->message = $serverException->getMessage();
            $error->data    = [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode()
            ];
            $fmt->error     = $error->outputArray();
            return $fmt;
        }
        return true;
    }

    /**
     * 从服务端接收数据
     * @return array|null|bool
     *
     * array 表示成功
     * null 表示服务器非json-rpc2.0标准
     * bool 表示服务内部错误
     */
    protected function _recvData() {
        $this->setBuffer(null);
        $this->setBuffer(fgets($this->_connection));
        $this->_closeConnection();
        try {
            return JsonRpc2::decode($this->getBuffer(), $this->_prepares);
        }catch(RpcException $rpcException){
            return null;
        }catch(\Exception $exception){
            return false;
        }
    }

    /**
     * 打开连接
     * @return false|resource
     * @throws InternalErrorException
     */
    protected function _openConnection() {
        $this->_connection = stream_socket_client(
            self::$_addressArray[array_rand(self::$_addressArray)],
            $err_no,
            $err_msg
        );
        if(!$this->_connection) {
            throw new InternalErrorException();
        }
        stream_set_blocking($this->_connection, true);
        stream_set_timeout($this->_connection, $this->getTimeout());
        return $this->_connection;
    }

    /**
     * 关闭连接
     */
    protected function _closeConnection() {
        fclose($this->_connection);
        $this->_connection = null;
    }
}
