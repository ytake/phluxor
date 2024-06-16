<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Phluxor\ActorSystem;

interface SupervisorStrategyInterface
{
    public function handleFailure(
        ActorSystem $actorSystem,
        SupervisorInterface $supervisor,
        Ref $child,
        ActorSystem\Child\RestartStatistics $restartStatistics,
        mixed $reason,
        mixed $message
    ): void;
}
