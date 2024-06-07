<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Strategy;

use Phluxor\ActorSystem\Directive;
use Phluxor\ActorSystem\Pid;

final readonly class SupervisorEvent
{
    /**
     * @param Pid $child
     * @param mixed $reason
     * @param Directive $directive
     */
    public function __construct(
        private Pid $child,
        private mixed $reason,
        private Directive $directive,
    ) {
    }

    public function getChild(): Pid
    {
        return $this->child;
    }

    public function getReason(): mixed
    {
        return $this->reason;
    }

    public function getDirective(): Directive
    {
        return $this->directive;
    }
}
