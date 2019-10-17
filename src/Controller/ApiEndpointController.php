<?php


namespace JsonRpcServerBundle\Controller;


use JsonRpcServerBundle\DataObject\JsonRpcRequest;
use JsonRpcServerBundle\Event\FailMethodExecuteEvent;
use JsonRpcServerBundle\Event\JsonRpcRequestPreparedEvent;
use JsonRpcServerBundle\Event\SuccessMethodExecuteEvent;
use JsonRpcServerBundle\Service\MethodExecutorService;
use JsonRpcServerBundle\ValueObject\ExceptionResponseEntity;
use JsonRpcServerBundle\Exception\InternalErrorException;
use JsonRpcServerBundle\Exception\InvalidRequestException;
use JsonRpcServerContracts\Contract\JsonRpcException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

class ApiEndpointController extends AbstractController
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    /** @var MethodExecutorService */
    protected $methodExecutorService;

    /**
     * ApiEndpointController constructor.
     * @param EventDispatcherInterface $eventDispatcher
     * @param MethodExecutorService $methodExecutorService
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, MethodExecutorService $methodExecutorService)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->methodExecutorService = $methodExecutorService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidRequestException
     */
    public function apiAction(Request $request): JsonResponse
    {
        $jsonRpcRequest = new JsonRpcRequest($request);
        $this->getEventDispatcher()->dispatch(new JsonRpcRequestPreparedEvent($jsonRpcRequest));

        foreach ($jsonRpcRequest->getValidRequestCollection() as $requestEntity) {
            try {
                $response = $this->getMethodExecutorService()->executeMethod($requestEntity);

                $jsonRpcRequest->getResponse()->addResponse($requestEntity, $response);
                $this->getEventDispatcher()->dispatch(new SuccessMethodExecuteEvent($requestEntity, $response));
            } catch (Throwable $exception) {
                $rpcException = $exception;
                if (! $rpcException instanceof JsonRpcException) {
                    $rpcException = new InternalErrorException('internal server error please report', $exception);
                }

                $responseEntity = new ExceptionResponseEntity($rpcException, $requestEntity);
                $jsonRpcRequest->getResponse()->addResponse(
                    $requestEntity,
                    $responseEntity
                );

                $this->getEventDispatcher()->dispatch(new FailMethodExecuteEvent($requestEntity, $responseEntity));
            }
        }

        $jsonRpcRequest->sortResponseInRequestOrder();
        return new JsonResponse($jsonRpcRequest->getResponse());
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * @return MethodExecutorService
     */
    public function getMethodExecutorService(): MethodExecutorService
    {
        return $this->methodExecutorService;
    }
}
