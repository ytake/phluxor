<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Mailbox;

use Phluxor\ActorSystem\QueueInterface;
use Phluxor\ActorSystem\QueueResult;
use Phluxor\Buffer\Queue as RingBufferQueue;

readonly class UnboundedMailboxQueue implements QueueInterface
{
    /**
     * @param RingBufferQueue $userMailbox
     */
    public function __construct(
        private RingBufferQueue $userMailbox
    ) {
    }

    public function push(mixed $val): void
    {
        $this->userMailbox->push($val);
    }

    public function pop(): QueueResult
    {
        return $this->userMailbox->pop();
    }

    public function isEmpty(): bool
    {
        return $this->userMailbox->isEmpty();
    }
}
