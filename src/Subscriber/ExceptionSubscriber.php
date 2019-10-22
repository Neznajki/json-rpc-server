<?php


namespace JsonRpcServerBundle\Subscriber;


use JsonRpcServerBundle\ValueObject\ExceptionResponseEntity;
use JsonRpcServerBundle\Exception\InternalErrorException;
use JsonRpcServerCommon\Contract\JsonRpcException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    /** @var LoggerInterface */
    protected $logger;

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
            KernelEvents::EXCEPTION => [
                ['logException', 10],
                ['placeResponse', 0],
            ],
        ];
    }

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function logException(ExceptionEvent $event)
    {
        $exception = $event->getException();
        $context   = [(string)$exception];

        if ($exception instanceof JsonRpcException) {
            $this->getLogger()->info($exception->getMessage(), $context);
        } else {
            $this->getLogger()->critical($exception->getMessage(), $context);
        }
    }

    public function placeResponse(ExceptionEvent $event)
    {
        $exception = $event->getException();

        if (! $exception instanceof JsonRpcException) {
            $exception = new InternalErrorException('internal server error please report', $exception);
        }
        $event->setResponse(new JsonResponse(new ExceptionResponseEntity($exception)));
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
