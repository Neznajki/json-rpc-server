<?php


namespace JsonRpcServerBundle\Exception;


use Exception;
use JsonRpcServerCommon\Contract\JsonRpcException;

class RpcMessageException extends Exception implements JsonRpcException
{

}
