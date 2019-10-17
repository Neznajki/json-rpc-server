<?php


namespace JsonRpcServerBundle\Event;


use JsonRpcServerBundle\DataObject\RequestEntity;
use JsonRpcServerBundle\ValueObject\ExceptionResponseEntity;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * NOTICE will be dispatched only in case execution is called (if invalid request received there won't be an event)
 * Class FailMethodExecuteEvent
 * @package App\src\Event
 */
class FailMethodExecuteEvent extends Event
{
    /** @var RequestEntity */
    protected $requestEntity;
    /** @var ExceptionResponseEntity */
    protected $responseEntity;

    /**
     * FailMethodExecuteEvent constructor.
     * @param RequestEntity $requestEntity
     * @param ExceptionResponseEntity $responseEntity
     */
    public function __construct(RequestEntity $requestEntity, ExceptionResponseEntity $responseEntity)
    {
        $this->requestEntity = $requestEntity;
        $this->responseEntity = $responseEntity;
    }

    /**
     * @return RequestEntity
     */
    public function getRequestEntity(): RequestEntity
    {
        return $this->requestEntity;
    }

    /**
     * @return ExceptionResponseEntity
     */
    public function getResponseEntity(): ExceptionResponseEntity
    {
        return $this->responseEntity;
    }
}
