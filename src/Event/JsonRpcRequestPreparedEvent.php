<?php


namespace JsonRpcServerBundle\Event;


use JsonRpcServerBundle\DataObject\JsonRpcRequest;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * dispatches after request got parsed
 * Class JsonRpcRequestPreparedEvent
 * @package App\src\Event
 */
class JsonRpcRequestPreparedEvent extends Event
{
    /** @var JsonRpcRequest */
    protected $request;

    /**
     * JsonRpcRequestPreparedEvent constructor.
     * @param JsonRpcRequest $request
     */
    public function __construct(JsonRpcRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @return JsonRpcRequest
     */
    public function getRequest(): JsonRpcRequest
    {
        return $this->request;
    }
}
