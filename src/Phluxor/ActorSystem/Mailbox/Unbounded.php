<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Mailbox;

use Phluxor\Buffer\Queue as RingBufferQueue;
use Phluxor\Mspc\Queue as MpscQueue;

class Unbounded implements MailboxProducerInterface
{
    /** @var MailboxMiddlewareInterface[]  */
    private array $mailboxMiddleware = [];

    public function __construct(
        MailboxMiddlewareInterface ...$mailboxMiddleware
    ) {
        $this->mailboxMiddleware = $mailboxMiddleware;
    }

    public function __invoke(): MailboxInterface
    {
        return new DefaultMailbox(
            new UnboundedMailboxQueue(new RingBufferQueue(10)),
            new MpscQueue(),
            $this->mailboxMiddleware
        );
    }
}
