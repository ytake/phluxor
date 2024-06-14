<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Phluxor\ActorSystem\Mailbox\MailboxInterface;
use Phluxor\ActorSystem\ProtoBuf\Stop;
use Swoole\Atomic\Long;

readonly class ActorProcess implements ProcessInterface
{
    public function __construct(
        private MailboxInterface $mailbox,
        private Long $dead = new Long(0)
    ) {
    }

    public function sendUserMessage(?Ref $pid, mixed $message): void
    {
        $this->mailbox->postUserMessage($message);
    }

    public function sendSystemMessage(Ref $pid, mixed $message): void
    {
        $this->mailbox->postSystemMessage($message);
    }

    public function stop(Ref $pid): void
    {
        $this->dead->set(1);
        $this->sendSystemMessage($pid, new Stop());
    }

    public function dead(): Long
    {
        return $this->dead;
    }
}
