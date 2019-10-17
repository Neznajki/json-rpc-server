<?php


namespace JsonRpcServerBundle\DataObject;


use JsonRpcServerBundle\Contract\ResponseEntityInterface;
use JsonSerializable;
use RuntimeException;
use SplObjectStorage;

class JsonRpcResponse implements JsonSerializable
{
    /** @var SplObjectStorage|ResponseEntityInterface[] */
    protected $responseCollection;
    /** @var bool */
    protected $responseAsArray;

    /**
     * JsonRpcResponse constructor.
     * @param bool $responseAsArray
     */
    public function __construct(bool $responseAsArray = false)
    {
        $this->responseCollection = new SplObjectStorage();
        $this->responseAsArray = $responseAsArray;
    }

    /**
     * @param RequestEntity $requestEntity
     * @param ResponseEntityInterface $responseEntity
     */
    public function addResponse(RequestEntity $requestEntity, ResponseEntityInterface $responseEntity)
    {
        $this->responseCollection->attach($requestEntity, $responseEntity);
    }

    /**
     * @param array $requestData
     */
    public function sortResponseInRequestOrder(array $requestData)
    {
        $sortedCollection = new SplObjectStorage();

        foreach ($requestData as $request) {
            $sortedCollection->attach($request, $this->responseCollection->offsetGet($request));
        }

        $this->responseCollection = $sortedCollection;
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
        if (count($this->responseCollection) === 0) {
            throw new RuntimeException('nothing to serialize');
        }

        if ($this->responseAsArray === false) {
            return $this->responseCollection->getInfo()->jsonSerialize();
        }

        $result = [];

        foreach ($this->responseCollection as $requestEntity) {
            $result[] = $this->responseCollection->getInfo();
        }

        return $result;
    }
}
