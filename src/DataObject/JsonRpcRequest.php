<?php


namespace JsonRpcServerBundle\DataObject;


use JsonRpcServerBundle\Exception\InvalidParamsException;
use JsonRpcServerBundle\ValueObject\ExceptionResponseEntity;
use JsonRpcServerBundle\Exception\InternalErrorException;
use JsonRpcServerBundle\Exception\InvalidRequestException;
use JsonRpcServerContracts\Contract\JsonRpcException;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

class JsonRpcRequest
{
    /** @var RequestEntity[] */
    protected $collection = [];
    /** @var JsonRpcResponse */
    protected $response;

    /**
     * JsonRpcRequest constructor.
     * @param Request $request
     * @throws InvalidRequestException
     */
    public function __construct(Request $request)
    {
        $data           = json_decode($request->getContent());
        $this->response = new JsonRpcResponse(is_array($data));

        if ($data === null) {
            $jsonError       = json_last_error();
            $jsonErrorString = json_last_error_msg();
            throw new InvalidRequestException("body should be json encoded string : {$jsonErrorString} (code {$jsonError})");
        }

        if (is_array($data)) {
            foreach ($data as $requestData) {
                $this->addSingleRequestEntity($requestData);
            }
        } else {
            $this->addSingleRequestEntity($data);
        }
    }

    /**
     * @return RequestEntity[]
     */
    public function getValidRequestCollection(): array
    {
        $result = [];

        foreach ($this->collection as $requestEntity) {
            if ($requestEntity->isValid()) {
                $result[] = $requestEntity;
            }
        }

        return $result;
    }

    /**
     * @return JsonRpcResponse
     */
    public function getResponse(): JsonRpcResponse
    {
        return $this->response;
    }

    /**
     * @param $requestData
     */
    public function addSingleRequestEntity($requestData): void
    {
        $requestEntity = null;
        try {
            if (! is_array($requestData) && ! is_object($requestData)) {
                $requestEntity = new RequestEntity([]);
                throw new InvalidParamsException('incoming data should be object');
            }
            $requestEntity      = new RequestEntity($requestData);
            $requestEntity->validate();
        } catch (Throwable $exception) {
            if (! $exception instanceof JsonRpcException) {
                $exception = new InternalErrorException('internal server error during parsing request entity please report', $exception);
            }

            $this->getResponse()->addResponse($requestEntity, new ExceptionResponseEntity($exception, $requestEntity));
        } finally {
            $this->collection[] = $requestEntity;
        }
    }

    /**
     *
     */
    public function sortResponseInRequestOrder()
    {
        $this->response->sortResponseInRequestOrder($this->collection);
    }
}
