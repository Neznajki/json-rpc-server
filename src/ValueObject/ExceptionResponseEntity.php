<?php


namespace JsonRpcServerBundle\ValueObject;


use JsonRpcServerBundle\Contract\ResponseEntityInterface;
use JsonRpcServerBundle\DataObject\RequestEntity;
use JsonRpcServerCommon\Contract\JsonRpcException;

class ExceptionResponseEntity implements ResponseEntityInterface
{
    /** @var JsonRpcException */
    protected $exception;
    /** @var RequestEntity */
    protected $requestEntity;

    /**
     * ExceptionResponseEntity constructor.
     * @param JsonRpcException $exception
     * @param RequestEntity|null $requestEntity
     */
    public function __construct(JsonRpcException $exception, RequestEntity $requestEntity = null)
    {
        $this->exception = $exception;
        $this->requestEntity = $requestEntity;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array
    {
        $result = [
            'jsonrpc' => $this->requestEntity->getJsonrpc() ?? '2.0',
            'error' => [
                'code' => $this->exception->getCode(),
                'message' => $this->exception->getMessage(),
            ],
        ];

        if ($this->requestEntity && $this->requestEntity->getId()) {
            $result['id'] = $this->requestEntity->getId();
        }

        return $result;
    }
}
