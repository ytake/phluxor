<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Mailbox;

interface MailboxMiddlewareInterface
{
    public function mailboxStared(): void;

    public function messagePosted(mixed $message): void;

    public function messageReceived(mixed $message): void;

    public function mailboxEmpty(): void;
}
