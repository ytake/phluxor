<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

/**
 * DeadLetterEvent is published via event.Publish when a message is sent to a nonexistent PID
 */
readonly class DeadLetterEvent
{
    public function __construct(
        private Pid|null $pid,
        private mixed $message,
        private Pid|null $sender,
    ) {
    }

    public function getMessage(): mixed
    {
        return $this->message;
    }

    public function getSender(): Pid|null
    {
        return $this->sender;
    }

    public function isNoSender(): bool
    {
        return $this->sender === null;
    }

    public function getPid(): Pid|null
    {
        return $this->pid;
    }

    public function isNoPid(): bool
    {
        return $this->pid === null;
    }
}
