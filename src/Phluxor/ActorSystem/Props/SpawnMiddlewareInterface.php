<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Props;

use Closure;
use Phluxor\ActorSystem\SpawnFunctionInterface;

interface SpawnMiddlewareInterface
{
    /**
     * @param Closure|SpawnFunctionInterface $next
     * @return Closure|SpawnFunctionInterface
     */
    public function __invoke(
        Closure|SpawnFunctionInterface $next
    ): Closure|SpawnFunctionInterface;
}
