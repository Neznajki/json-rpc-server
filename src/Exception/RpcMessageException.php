<?php


namespace JsonRpcServerBundle\Exception;


use Exception;
use JsonRpcServerContracts\Contract\JsonRpcException;

class RpcMessageException extends Exception implements JsonRpcException
{

}
