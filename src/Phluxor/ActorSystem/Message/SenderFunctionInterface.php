<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Message;

use Phluxor\ActorSystem\Context\SenderInterface;
use Phluxor\ActorSystem\Pid;

interface SenderFunctionInterface
{
    /**
     * @param SenderInterface $context
     * @param Pid|null $target
     * @param MessageEnvelope $messageEnvelope
     * @return void
     */
    public function __invoke(
        SenderInterface $context,
        Pid|null $target,
        MessageEnvelope $messageEnvelope
    ): void;
}
