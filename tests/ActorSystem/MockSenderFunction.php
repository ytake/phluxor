<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use Closure;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Context\SenderInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Message\SenderFunctionInterface;
use Phluxor\ActorSystem\Pid;

readonly class MockSenderFunction implements SenderFunctionInterface
{
    /**
     * @param Closure(SenderInterface|ContextInterface, Pid, MessageEnvelope): void|SenderFunctionInterface $next
     */
    public function __construct(
        private Closure|SenderFunctionInterface $next
    ) {
    }

    public function __invoke(
        SenderInterface $context,
        ?Pid $target,
        MessageEnvelope $messageEnvelope
    ): void {
        $next = $this->next;
        $next($context, $target, $messageEnvelope);
    }
}
