<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Message;

use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Context\ReceiverInterface;

interface ReceiverFunctionInterface
{
    /**
     * @param ReceiverInterface|ContextInterface $context
     * @param MessageEnvelope $messageEnvelope
     * @return void
     */
    public function __invoke(
        ReceiverInterface|ContextInterface $context,
        MessageEnvelope $messageEnvelope
    ): void;
}
