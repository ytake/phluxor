<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Context;

use Phluxor\ActorSystem\Message\MessageEnvelope;

interface ReceiverPartInterface
{
    /**
     * @param MessageEnvelope|null $envelope
     * @return void
     */
    public function receive(MessageEnvelope|null $envelope): void;
}
