<?php


namespace JsonRpcServerBundle\Service;


use JsonRpcServerBundle\Contract\MethodHandlerInterface;
use JsonRpcServerBundle\Exception\InternalErrorException;
use JsonRpcServerBundle\Exception\MethodNotFoundException;

class MethodCollectionService
{
    /**
     * @var MethodHandlerInterface[]
     */
    protected $collection = [];

    /**
     * @param MethodHandlerInterface $methodHandler
     * @throws InternalErrorException
     */
    public function addMethod(MethodHandlerInterface $methodHandler)
    {
        $method = $methodHandler->getMethod();
        if (array_key_exists($method, $this->collection)) {
            throw new InternalErrorException("method {$method} could not be defined twice");
        }

        $this->collection[$method] = $methodHandler;
    }

    /**
     * @param string $method
     * @return MethodHandlerInterface
     * @throws MethodNotFoundException
     */
    public function getMethod(string $method): MethodHandlerInterface
    {
        if (empty($this->collection[$method])) {
            throw new MethodNotFoundException("method {$method} not found");
        }

        return $this->collection[$method];
    }
}
