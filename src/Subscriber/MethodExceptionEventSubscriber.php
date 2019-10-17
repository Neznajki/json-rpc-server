<?php


namespace JsonRpcServerBundle\Subscriber;


use JsonRpcServerBundle\Event\FailMethodExecuteEvent;
use JsonRpcServerBundle\Exception\RpcMessageException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MethodExceptionEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
            FailMethodExecuteEvent::class => [
                ['log', 10],
            ],
        ];
    }

    /** @var LoggerInterface */
    protected $logger;

    /**
     * MethodExceptionEventSubscriber constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param FailMethodExecuteEvent $event
     */
    public function log(FailMethodExecuteEvent $event)
    {
        $exceptionResponseEntity = $event->getResponseEntity();

        if (! $exceptionResponseEntity instanceof RpcMessageException) {
            $this->getLogger()->error(
                sprintf('method exception caught : %s', json_encode($exceptionResponseEntity->jsonSerialize()))
            );
        } else {
            $this->getLogger()->info(
                sprintf('method message exception caught : %s', json_encode($exceptionResponseEntity->jsonSerialize()))
            );
        }
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
