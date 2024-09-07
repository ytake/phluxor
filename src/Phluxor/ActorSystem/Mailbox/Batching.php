<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Mailbox;

use Phluxor\Buffer\Queue as RingBufferQueue;

class Batching implements MailboxProducerInterface
{
    /** @var MailboxMiddlewareInterface[] */
    private array $mailboxMiddleware = [];

    public function __construct(
        private readonly int $batchSize = 100,
        private readonly int $queueSize = 10,
        MailboxMiddlewareInterface ...$mailboxMiddleware
    ) {
        $this->mailboxMiddleware = $mailboxMiddleware;
    }

    public function __invoke(): MailboxInterface
    {
        return new BatchingMailbox(
            new UnboundedMailboxQueue(new RingBufferQueue($this->queueSize)),
            new UnboundedMailboxQueue(new RingBufferQueue($this->queueSize)),
            $this->batchSize,
            $this->mailboxMiddleware
        );
    }
}
