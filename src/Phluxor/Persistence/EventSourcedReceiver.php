<?php

declare(strict_types=1);

namespace Phluxor\Persistence;

use Closure;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Context\ReceiverInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Message\ReceiverFunctionInterface;
use Phluxor\ActorSystem\Props\ReceiverMiddlewareInterface;

readonly class EventSourcedReceiver implements ReceiverMiddlewareInterface
{
    /**
     * @param ProviderInterface $provider
     */
    public function __construct(
        private ProviderInterface $provider,
    ) {
    }

    /**
     * @param Closure(ReceiverInterface|ContextInterface, MessageEnvelope): void|ReceiverFunctionInterface $next
     * @return ReceiverFunctionInterface
     */
    public function __invoke(Closure|ReceiverFunctionInterface $next): ReceiverFunctionInterface
    {
        return new EventSourcedReceiverFactory($this->provider, $next);
    }
}
