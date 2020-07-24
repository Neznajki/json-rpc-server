<?php


namespace JsonRpcServerBundle\Subscriber;


use JsonRpcServerBundle\ValueObject\ExceptionResponseEntity;
use JsonRpcServerBundle\Exception\InternalErrorException;
use JsonRpcServerCommon\Contract\JsonRpcException;
use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

class ExceptionSubscriber implements EventSubscriberInterface
{
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

    /** @var LoggerInterface */
    protected $logger;
    /** @var string */
    protected $jsonRpcUrl;

    /**
     * ExceptionSubscriber constructor.
     * @param LoggerInterface $logger
     * @param string $jsonRpcUrl
     */
    public function __construct(LoggerInterface $logger, string $jsonRpcUrl)
    {
        $this->logger = $logger;
        $this->jsonRpcUrl = $jsonRpcUrl;
    }

    /**
     * @param ExceptionEvent $event
     */
    public function logException(ExceptionEvent $event)
    {
        if (! preg_match("@^{$this->jsonRpcUrl}@", $event->getRequest()->getRequestUri())) {
            return;
        }

        $exception = $this->getExceptionFromEvent($event);

        $context   = [(string)$exception];

        if ($exception instanceof JsonRpcException) {
            $this->getLogger()->info($exception->getMessage(), $context);
        } else {
            $this->getLogger()->critical($exception->getMessage(), $context);
        }
    }

    public function placeResponse(ExceptionEvent $event)
    {
        if (! preg_match("@^{$this->jsonRpcUrl}@", $event->getRequest()->getRequestUri())) {
            return;
        }

        $exception = $this->wrapException($this->getExceptionFromEvent($event));

        $event->setResponse(new JsonResponse(new ExceptionResponseEntity($exception)));
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param ExceptionEvent $event
     * @return Throwable
     */
    public function getExceptionFromEvent(ExceptionEvent $event): Throwable
    {
        if (method_exists($event, 'getException')) {
            return $event->getException();
        } elseif (method_exists($event, 'getThrowable')) {
            return $event->getThrowable();
        }

        throw new LogicException("could not detect exception");
    }

    /**
     * @param ExceptionEvent $event
     * @return InternalErrorException|JsonRpcException|Throwable
     */
    public function wrapException(Throwable $exception)
    {
        if (empty($exception)) {
            $exception = new InternalErrorException('exception could not be detected');
        } elseif (!$exception instanceof JsonRpcException) {
            $exception = new InternalErrorException('internal server error please report', $exception);
        }

        return $exception;
    }
}
