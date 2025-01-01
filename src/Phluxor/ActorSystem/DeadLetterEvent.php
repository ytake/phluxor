<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

/**
 * DeadLetterEvent is published via event.Publish when a message is sent to a nonexistent PID
 */
readonly class DeadLetterEvent
{
    /**
     * @param Ref|null $ref
     * @param mixed $message
     * @param Ref|null $sender
     */
    public function __construct(
        public Ref|null $ref,
        public mixed $message,
        public Ref|null $sender,
    ) {
    }

    public function isNoSender(): bool
    {
        return $this->sender === null;
    }

    public function isNoPid(): bool
    {
        return $this->ref === null;
    }
}
