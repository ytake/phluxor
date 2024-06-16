<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Strategy;

use Phluxor\ActorSystem\Directive;
use Phluxor\ActorSystem\Ref;

final readonly class SupervisorEvent
{
    /**
     * @param Ref $child
     * @param mixed $reason
     * @param Directive $directive
     */
    public function __construct(
        private Ref $child,
        private mixed $reason,
        private Directive $directive,
    ) {
    }

    public function getChild(): Ref
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
