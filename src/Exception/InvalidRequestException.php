<?php


namespace JsonRpcServerBundle\Exception;


use Exception;
use JsonRpcServerCommon\Contract\JsonRpcException;
use Throwable;

class InvalidRequestException extends Exception implements JsonRpcException
{
    const ERROR_CODE = -32600;

    public function __construct($message = "", Throwable $previous = null)
    {
        parent::__construct($message, self::ERROR_CODE, $previous);
    }
}
