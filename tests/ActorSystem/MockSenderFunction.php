<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use Closure;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Context\SenderInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Message\SenderFunctionInterface;
use Phluxor\ActorSystem\Ref;

readonly class MockSenderFunction implements SenderFunctionInterface
{
    /**
     * @param Closure(SenderInterface|ContextInterface, Ref, MessageEnvelope): void|SenderFunctionInterface $next
     */
    public function __construct(
        private Closure|SenderFunctionInterface $next
    ) {
    }

    public function __invoke(
        SenderInterface $context,
        ?Ref $target,
        MessageEnvelope $messageEnvelope
    ): void {
        $next = $this->next;
        $next($context, $target, $messageEnvelope);
    }
}
