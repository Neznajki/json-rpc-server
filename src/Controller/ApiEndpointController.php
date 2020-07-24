<?php


namespace JsonRpcServerBundle\Controller;


use JsonRpcServerBundle\DataObject\JsonRpcRequest;
use JsonRpcServerBundle\Event\FailMethodExecuteEvent;
use JsonRpcServerBundle\Event\JsonRpcRequestPreparedEvent;
use JsonRpcServerBundle\Event\SuccessMethodExecuteEvent;
use JsonRpcServerBundle\Service\MethodExecutorService;
use JsonRpcServerBundle\Subscriber\ExceptionSubscriber;
use JsonRpcServerBundle\ValueObject\ExceptionResponseEntity;
use JsonRpcServerBundle\Exception\InvalidRequestException;
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
    /** @var ExceptionSubscriber */
    protected $exceptionSubscriber;

    /**
     * ApiEndpointController constructor.
     * @param EventDispatcherInterface $eventDispatcher
     * @param MethodExecutorService $methodExecutorService
     * @param ExceptionSubscriber $exceptionSubscriber
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        MethodExecutorService $methodExecutorService,
        ExceptionSubscriber $exceptionSubscriber
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->methodExecutorService = $methodExecutorService;
        $this->exceptionSubscriber = $exceptionSubscriber;
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

        $requestEntities = $jsonRpcRequest->getValidRequestCollection();
        foreach ($requestEntities as $requestEntity) {
            try {
                $response = $this->getMethodExecutorService()->executeMethod($requestEntity);

                $jsonRpcRequest->getResponse()->addResponse($requestEntity, $response);
                $this->getEventDispatcher()->dispatch(new SuccessMethodExecuteEvent($requestEntity, $response));
            } catch (Throwable $exception) {
                $rpcException = $this->exceptionSubscriber->wrapException($exception);

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
