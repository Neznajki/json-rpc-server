<?php


namespace JsonRpcServerBundle\Event;

use JsonRpcServerBundle\DataObject\RequestEntity;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class BeforeMethodExecuteEvent
 * @package App\src\Event
 */
class BeforeMethodExecuteEvent extends Event
{
    /** @var RequestEntity */
    protected $requestEntity;

    /**
     * BeforeMethodExecuteEvent constructor.
     * @param RequestEntity $requestEntity
     */
    public function __construct(RequestEntity $requestEntity)
    {
        $this->requestEntity = $requestEntity;
    }

    /**
     * @return RequestEntity
     */
    public function getRequestEntity(): RequestEntity
    {
        return $this->requestEntity;
    }
}
