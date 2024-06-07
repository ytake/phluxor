<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Message;

use Phluxor\ActorSystem\Context\ContextInterface;

interface ActorInterface
{
    /**
     * Receives a context.
     *
     * @param ContextInterface $context The context to receive.
     * @return void
     */
    public function receive(ContextInterface $context): void;
}
