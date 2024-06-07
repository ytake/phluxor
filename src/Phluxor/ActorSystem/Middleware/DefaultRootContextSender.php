<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Middleware;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context\SenderInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Message\SenderFunctionInterface;
use Phluxor\ActorSystem\Pid;

readonly class DefaultRootContextSender implements SenderFunctionInterface
{
    /**
     * @param ActorSystem $actorSystem
     */
    public function __construct(
        private ActorSystem $actorSystem
    ) {
    }

    /**
     * @param SenderInterface $context
     * @param Pid|null $target
     * @param MessageEnvelope $messageEnvelope
     * @return void
     */
    public function __invoke(
        SenderInterface $context,
        ?Pid $target,
        MessageEnvelope $messageEnvelope
    ): void {
        if ($target instanceof Pid) {
            $target->sendUserMessage($this->actorSystem, $messageEnvelope);
        }
    }
}
