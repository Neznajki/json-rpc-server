<?php


namespace JsonRpcServerBundle\Event;


use JsonRpcServerBundle\Contract\ResponseEntityInterface;
use JsonRpcServerBundle\DataObject\RequestEntity;
use Symfony\Contracts\EventDispatcher\Event;

class SuccessMethodExecuteEvent extends Event
{
    /** @var RequestEntity */
    protected $requestEntity;
    /** @var ResponseEntityInterface */
    protected $responseEntity;

    /**
     * SuccessMethodExecuteEvent constructor.
     * @param RequestEntity $requestEntity
     * @param ResponseEntityInterface $responseEntity
     */
    public function __construct(RequestEntity $requestEntity, ResponseEntityInterface $responseEntity)
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
     * @return ResponseEntityInterface
     */
    public function getResponseEntity(): ResponseEntityInterface
    {
        return $this->responseEntity;
    }
}
