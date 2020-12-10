<?php

namespace Protocols;

use JsonRpc\Exception\InvalidRequestException;
use JsonRpc\Exception\ParseErrorException;
use JsonRpc\Exception\RpcException;
use JsonRpc\Format\JsonFmt;

/**
 * JsonRpc-2.0 协议
 *
 * Class JsonRpc2
 * @package Protocols
 * @link https://www.jsonrpc.org/
 * @license https://www.jsonrpc.org/specification
 */
class JsonRpc2Client {

    /**
     * 打包
     * @param array $buffer
     * @return string
     * @throws InvalidRequestException
     * @throws RpcException
     */
    public static function encode($buffer) {
        if(!is_array($buffer)){
            # 抛出ParseError异常
            throw new ParseErrorException();
        }
        if(!$buffer){
            # 抛出InvalidRequest异常
            throw new InvalidRequestException();
        }
        $fmt = JsonFmt::factory();
        if(!self::isAssoc($buffer)){
            foreach($buffer as $value){
                self::_throw($fmt, $value, $fmt::TYPE_REQUEST);
            }
        }
        self::_throw($fmt, $buffer, $fmt::TYPE_REQUEST);
        return json_encode($buffer) . "\n";
    }

    /**
     * 解包
     * @param string $buffer 原始数据值
     * @param bool $check
     * @return array
     * @throws InvalidRequestException
     * @throws ParseErrorException
     * @throws RpcException
     */
    public static function decode($buffer, $check = false) {
        $data = self::isJson(trim($buffer),true);
        if($check){
            # 不是json
            if(!$data){
                # 抛出ParseError异常
                throw new ParseErrorException();
            }
            # 空数组
            if(!$data){
                # 抛出InvalidRequest异常
                throw new InvalidRequestException();
            }
            $fmt = JsonFmt::factory();
            # 关联数组
            if(!self::isAssoc($data)){
                foreach($data as $value){
                    self::_throw($fmt, $value, $fmt::TYPE_RESPONSE);
                }
            }
            self::_throw($fmt, $data, $fmt::TYPE_RESPONSE);
        }
        return $data;
    }

    /**
     * @param JsonFmt $fmt
     * @param array $data
     * @param string $scene
     * @throws RpcException
     */
    protected static function _throw(JsonFmt $fmt, $data, $scene){
        $fmt->clean();
        $fmt->setScene($scene);
        $fmt->create($data,true);
        # 如果有错误
        if($fmt->hasError()){
            # 抛出异常
            $exception = $fmt->getError();
            $exception = "JsonRpc\Exception\\{$exception}";
            throw new $exception;
        }
        # 如果有特殊错误
        if($fmt->hasSpecialError()){
            # 抛出异常
            $exception = $fmt->getSpecialError();
            $exception = "JsonRpc\Exception\\{$exception}";
            throw new $exception;
        }
    }

    /**
     * 是否是Json
     * @param $string
     * @param bool $get
     * @return bool|mixed
     */
    public static function isJson($string, bool $get = false){
        @json_decode($string);
        if(json_last_error() != JSON_ERROR_NONE){
            return false;
        }
        if($get){
            return json_decode($string,true);
        }
        return true;
    }

    /**
     * 是否是关联数组
     * @param array $array
     * @return bool
     */
    public static function isAssoc(array $array){
        return boolval(array_keys($array) !== range(0, count($array) - 1));
    }
    
}
