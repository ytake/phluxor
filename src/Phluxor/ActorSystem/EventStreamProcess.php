<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Phluxor\ActorSystem;

readonly class EventStreamProcess implements ProcessInterface
{
    /**
     * @param ActorSystem $actorSystem
     */
    public function __construct(
        private ActorSystem $actorSystem
    ) {
    }

    public function sendUserMessage(?Pid $pid, mixed $message): void
    {
        $msg = ActorSystem\Message\MessageEnvelope::unwrapEnvelope($message);
        $this->actorSystem->getEventStream()?->publish($msg['message']);
    }

    public function sendSystemMessage(Pid $pid, mixed $message): void
    {
        // none
    }

    public function stop(Pid $pid): void
    {
        // none
    }
}
