<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Supervision;

use Phluxor\ActorSystem\Directive;

interface DeciderFunctionInterface
{
    /**
     * @param mixed $reason
     * @return Directive
     */
    public function __invoke(mixed $reason): Directive;
}
