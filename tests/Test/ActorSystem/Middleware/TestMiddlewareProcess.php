<?php

declare(strict_types=1);

namespace Test\ActorSystem\Middleware;

use Closure;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context;
use Phluxor\ActorSystem\Props;
use Phluxor\ActorSystem\SpawnFunctionInterface;
use Phluxor\ActorSystem\SpawnResult;
use Swoole\Lock;

class TestMiddlewareProcess implements SpawnFunctionInterface
{
    public function __construct(
        private readonly Closure|SpawnFunctionInterface $next,
        private int &$spawnCounter,
        private Lock $lock
    ) {
    }

    public function __invoke(
        ActorSystem $actorSystem,
        string $id,
        Props $props,
        Context\SpawnerInterface $parentContext
    ): SpawnResult {
        $next = $this->next;
        $this->lock->lock();
        $this->spawnCounter++;
        $this->lock->unlock();
        return $next($actorSystem, $id, $props, $parentContext);
    }
}
