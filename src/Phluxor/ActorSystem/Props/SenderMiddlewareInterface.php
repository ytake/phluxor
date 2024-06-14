<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Props;

use Closure;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Context\SenderInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Message\SenderFunctionInterface;
use Phluxor\ActorSystem\Ref;

interface SenderMiddlewareInterface
{
    /**
     * @param Closure(SenderInterface|ContextInterface, Ref, MessageEnvelope): void|SenderFunctionInterface $next
     * @return SenderFunctionInterface
     */
    public function __invoke(Closure|SenderFunctionInterface $next): SenderFunctionInterface;
}
