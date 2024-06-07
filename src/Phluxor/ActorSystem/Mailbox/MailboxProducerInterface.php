<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Mailbox;

interface MailboxProducerInterface
{
    /**
     * @return MailboxInterface
     */
    public function __invoke(): MailboxInterface;
}
