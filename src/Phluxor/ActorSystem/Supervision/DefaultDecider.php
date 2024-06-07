<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Supervision;

use Phluxor\ActorSystem\Directive;

class DefaultDecider implements DeciderFunctionInterface
{
    /**
     * @param mixed $reason
     * @return Directive
     */
    public function __invoke(mixed $reason): Directive
    {
        // restart the actor
        return Directive::Restart;
    }
}
