<?php


namespace JsonRpcServerBundle\Exception;


use Exception;
use JsonRpcServerCommon\Contract\JsonRpcException;
use Throwable;

class ServerErrorException extends Exception implements JsonRpcException
{

    /**
     * ServerErrorException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @throws InternalErrorException
     */
    public function __construct($message = "", int $code = -32000, Throwable $previous = null)
    {
        if ($code < -32099 || $code > -32000) {
            throw new InternalErrorException(
                sprintf(
                    '%d code should be between -32000 and -32099 you can use %s for other codes',
                    $code,
                    RpcMessageException::class
                )
            );
        }

        parent::__construct($message, $code, $previous);
    }
}
