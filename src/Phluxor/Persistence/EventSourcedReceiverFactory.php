<?php

namespace Phluxor\Persistence;

use Closure;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Context\ReceiverInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Message\ReceiverFunctionInterface;
use Phluxor\ActorSystem\Message\Started;

readonly class EventSourcedReceiverFactory implements ReceiverFunctionInterface
{
    /**
     * @param ProviderInterface $provider
     * @param Closure(ReceiverInterface|ContextInterface, MessageEnvelope): void|ReceiverFunctionInterface $next
     */
    public function __construct(
        private ProviderInterface $provider,
        private Closure|ReceiverFunctionInterface $next
    ) {
    }

    /**
     * @param ContextInterface|ReceiverInterface $context
     * @param MessageEnvelope $messageEnvelope
     * @return void
     */
    public function __invoke(
        ContextInterface|ReceiverInterface $context,
        MessageEnvelope $messageEnvelope
    ): void {
        $msg = $messageEnvelope->getMessage();
        $next = $this->next;
        $next($context, $messageEnvelope);
        if ($msg instanceof Started) {
            $actor = $context->actor();
            if ($actor instanceof PersistentInterface) {
                $actor->init($this->provider, $context);
            }
        }
    }
}
