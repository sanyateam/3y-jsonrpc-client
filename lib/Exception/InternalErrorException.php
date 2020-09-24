<?php
namespace JsonRpc\Exception;

use Throwable;

class InternalErrorException extends RpcException {

    public function __construct($message = 'Internal error', $code = -32603, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}