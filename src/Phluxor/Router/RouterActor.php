<?php

declare(strict_types=1);

namespace Phluxor\Router;

use Phluxor\ActorSystem;

class RouterActor
{
    private function __construct()
    {
    }

    private static function spawn(
        ActorSystem $actorSystem,
        string $id,
        ConfigInterface $config,
        ActorSystem\Props $props,
        ActorSystem\Context\SpawnerInterface $parent
    ): ActorSystem\SpawnResult {
        return new ActorSystem\SpawnResult();
    }
}