<?php
namespace JsonRpc\Exception;

use Throwable;

class MethodNotReadyException extends ServerErrorException {

    /**
     * InternalErrorException constructor.
     * @param string $message
     * @param int $code -32000 to -32099
     * @param Throwable|null $previous
     */
    public function __construct($message , $code = -32002, Throwable $previous = null) {
        parent::__construct("Server error [{$message}]", $code, $previous);
    }
}