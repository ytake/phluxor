<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Spawner;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context;
use Phluxor\ActorSystem\Props;
use Phluxor\ActorSystem\SpawnFunctionInterface;
use Phluxor\ActorSystem\SpawnResult;

readonly class SpawnFunction implements SpawnFunctionInterface
{
    public function __construct(
        private ActorSystem\RootContext $rootContext
    ) {

    }
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
        Context\SpawnerInterface $parentContext
    ): SpawnResult {
        return $props->spawn($actorSystem, $id, $this->rootContext);
    }
}
