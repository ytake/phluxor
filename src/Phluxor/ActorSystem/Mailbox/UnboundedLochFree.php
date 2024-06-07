<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Mailbox;

use Phluxor\Mspc\Queue as MspcQueue;

class UnboundedLochFree
{
    private array $mailboxMiddleware = [];

    public function __construct(
        private readonly MspcQueue $queue = new MspcQueue(),
        private readonly MspcQueue $systemQueue = new MspcQueue(),
        MailboxMiddlewareInterface ...$mailboxMiddleware
    ) {
        $this->mailboxMiddleware = $mailboxMiddleware;
    }

    public function __invoke(): MailboxInterface
    {
        return new DefaultMailbox(
            $this->queue,
            $this->systemQueue,
            $this->mailboxMiddleware
        );
    }
}
