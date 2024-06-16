<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Middleware;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context\SenderInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Message\SenderFunctionInterface;
use Phluxor\ActorSystem\Ref;

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
     * @param Ref|null $target
     * @param MessageEnvelope $messageEnvelope
     * @return void
     */
    public function __invoke(
        SenderInterface $context,
        ?Ref $target,
        MessageEnvelope $messageEnvelope
    ): void {
        if ($target instanceof Ref) {
            $target->sendUserMessage($this->actorSystem, $messageEnvelope);
        }
    }
}
