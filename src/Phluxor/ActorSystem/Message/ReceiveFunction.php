<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Message;

use Closure;
use Phluxor\ActorSystem\Context\ContextInterface;

readonly class ReceiveFunction implements ActorInterface
{
    /**
     * @param Closure(ContextInterface): void $callable
     */
    public function __construct(
        private Closure $callable
    ) {
    }

    /**
     * @param ContextInterface $context
     * @return void
     */
    public function receive(ContextInterface $context): void
    {
        $c = $this->callable;
        $c($context);
    }
}
