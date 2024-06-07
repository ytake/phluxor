<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Phluxor\ActorSystem;

interface SpawnFunctionInterface
{
    /**
     * @param ActorSystem $actorSystem
     * @param string $id
     * @param Props $props
     * @param Context\SpawnerInterface $parentContext
     * @return SpawnResult
     */
    public function __invoke(
        ActorSystem $actorSystem,
        string $id,
        Props $props,
        ActorSystem\Context\SpawnerInterface $parentContext
    ): SpawnResult;
}
