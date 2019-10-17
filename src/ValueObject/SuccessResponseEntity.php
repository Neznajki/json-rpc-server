<?php


namespace JsonRpcServerBundle\ValueObject;


use JsonRpcServerBundle\Contract\ResponseEntityInterface;
use JsonRpcServerBundle\DataObject\RequestEntity;
use JsonRpcServerBundle\Exception\InternalErrorException;

class SuccessResponseEntity implements ResponseEntityInterface
{

    /** @var RequestEntity */
    protected $requestEntity;
    /** @var  */
    protected $responseData;

    /**
     * SuccessResponseEntity constructor.
     * @param RequestEntity $requestEntity
     * @param $responseData
     * @throws InternalErrorException
     */
    public function __construct(RequestEntity $requestEntity, $responseData)
    {
        $this->requestEntity = $requestEntity;

        if (json_encode($responseData) === null) {
            $jsonError       = json_last_error();
            $jsonErrorString = json_last_error_msg();
            throw new InternalErrorException("response can not be converted to json : {$jsonErrorString} ({$jsonError})");
        }

        $this->responseData = $responseData;
    }

    /**
     * @return RequestEntity
     */
    public function getRequestEntity(): RequestEntity
    {
        return $this->requestEntity;
    }

    /**
     * @param RequestEntity $requestEntity
     */
    public function setRequestEntity(RequestEntity $requestEntity): void
    {
        $this->requestEntity = $requestEntity;
    }

    /**
     * @return mixed
     */
    public function getResponseData()
    {
        return $this->responseData;
    }

    /**
     * @param mixed $responseData
     */
    public function setResponseData($responseData): void
    {
        $this->responseData = $responseData;
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
            'jsonrpc' => $this->getRequestEntity()->getJsonrpc(),
            "result" => $this->responseData,
        ];

        if ($this->getRequestEntity()->getId()) {
            $result['id'] = $this->getRequestEntity()->getId();
        }

        return $result;
    }
}
