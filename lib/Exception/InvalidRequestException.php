<?php
namespace JsonRpc\Exception;

use Throwable;

class InvalidRequestException extends RpcException {

    public function __construct($message = 'Invalid Request', $code = -32600, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}