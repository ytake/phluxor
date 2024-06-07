<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Props;

use Phluxor\ActorSystem\SpawnFunctionInterface;

interface SpawnMiddlewareInterface
{
    /**
     * @param SpawnFunctionInterface $next
     * @return SpawnFunctionInterface
     */
    public function __invoke(
        SpawnFunctionInterface $next
    ): SpawnFunctionInterface;
}
