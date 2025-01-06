<?php

declare(strict_types=1);

namespace Test\ActorSystem\Middleware;

use Closure;
use Phluxor\ActorSystem\Props\SpawnMiddlewareInterface;
use Phluxor\ActorSystem\SpawnFunctionInterface;
use Swoole\Lock;

class TestMiddleware implements SpawnMiddlewareInterface
{
    public function __construct(
        private int &$spawnCounter,
        private readonly Lock $lock
    ) {
    }

    public function __invoke(Closure|SpawnFunctionInterface $next): Closure|SpawnFunctionInterface
    {
        return new TestMiddlewareProcess($next, $this->spawnCounter, $this->lock);
    }
}
