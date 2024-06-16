<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Strategy;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Ref;
use Phluxor\ActorSystem\SupervisorInterface;
use Phluxor\ActorSystem\SupervisorStrategyInterface;
use Phluxor\ActorSystem\Child\RestartStatistics;

final class RestartingStrategy implements SupervisorStrategyInterface
{
    /**
     * @param ActorSystem $actorSystem
     * @param SupervisorInterface $supervisor
     * @param Ref $child
     * @param RestartStatistics $restartStatistics
     * @param mixed $reason
     * @param mixed $message
     * @return void
     */
    public function handleFailure(
        ActorSystem $actorSystem,
        SupervisorInterface $supervisor,
        Ref $child,
        RestartStatistics $restartStatistics,
        mixed $reason,
        mixed $message
    ): void {
        // always restart the actor
        $actorSystem->getEventStream()?->publish(
            new SupervisorEvent($child, $reason, ActorSystem\Directive::Restart)
        );
        $supervisor->restartChildren($child);
    }
}
