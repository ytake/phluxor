<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Props;

use Closure;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Context\ReceiverInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Message\ReceiverFunctionInterface;

interface ReceiverMiddlewareInterface
{
    /**
     * @param Closure(ReceiverInterface|ContextInterface, MessageEnvelope): void|ReceiverFunctionInterface $next
     * @return ReceiverFunctionInterface
     */
    public function __invoke(
        Closure|ReceiverFunctionInterface $next
    ): ReceiverFunctionInterface;
}
