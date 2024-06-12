<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Strategy;

use DateInterval;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Pid;
use Phluxor\ActorSystem\SupervisorInterface;
use Phluxor\ActorSystem\SupervisorStrategyInterface;
use Phluxor\ActorSystem\Child;

final readonly class AllForOneStrategy implements SupervisorStrategyInterface
{
    /**
     * @param int $maxNrOfRetries
     * @param DateInterval $withinDuration
     * @param ActorSystem\Supervision\DeciderFunctionInterface $decider
     */
    public function __construct(
        private int $maxNrOfRetries,
        private DateInterval $withinDuration,
        private ActorSystem\Supervision\DeciderFunctionInterface $decider
    ) {
    }

    /**
     * @param ActorSystem $actorSystem
     * @param SupervisorInterface $supervisor
     * @param Pid $child
     * @param Child\RestartStatistics $restartStatistics
     * @param mixed $reason
     * @param mixed $message
     * @return void
     */
    public function handleFailure(
        ActorSystem $actorSystem,
        SupervisorInterface $supervisor,
        Pid $child,
        Child\RestartStatistics $restartStatistics,
        mixed $reason,
        mixed $message
    ): void {
        $decider = $this->decider;
        $directive = $decider($reason);
        switch ($directive) {
            case ActorSystem\Directive::Resume:
                // resume the child, no need to involve the crs
                $actorSystem->getEventStream()?->publish(
                    new SupervisorEvent($child, $reason, $directive)
                );
                $supervisor->resumeChildren($child);
                break;
            case ActorSystem\Directive::Restart:
                $children = $supervisor->children();
                // restart the all children and check if we should stop
                if ($this->shouldStop($restartStatistics)) {
                    $actorSystem->getEventStream()?->publish(
                        new SupervisorEvent($child, $reason, ActorSystem\Directive::Stop)
                    );
                    $supervisor->stopChildren(...$children);
                } else {
                    $actorSystem->getEventStream()?->publish(
                        new SupervisorEvent($child, $reason, ActorSystem\Directive::Restart)
                    );
                    $supervisor->restartChildren(...$children);
                }
                break;
            case ActorSystem\Directive::Stop:
                $children = $supervisor->children();
                // stop all the children
                $actorSystem->getEventStream()?->publish(
                    new SupervisorEvent($child, $reason, $directive)
                );
                $supervisor->stopChildren(...$children);
                break;
            case ActorSystem\Directive::Escalate:
                // send the failure to the parent
                $supervisor->escalateFailure($child, $reason);
                break;
        }
    }

    private function shouldStop(Child\RestartStatistics $rs): bool
    {
        if ($this->maxNrOfRetries === 0) {
            return true;
        }
        $rs->fail();

        if ($rs->numberOfFailures($this->withinDuration) > $this->maxNrOfRetries) {
            $rs->reset();
            return true;
        }

        return false;
    }
}
