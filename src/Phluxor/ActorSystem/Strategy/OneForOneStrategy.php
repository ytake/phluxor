<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Strategy;

use Closure;
use DateInterval;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Pid;
use Phluxor\ActorSystem\SupervisorInterface;
use Phluxor\ActorSystem\SupervisorStrategyInterface;
use Phluxor\ActorSystem\Supervision\DeciderFunctionInterface;
use Phluxor\ActorSystem\Child\RestartStatistics;
use Phluxor\ActorSystem\Directive;

final readonly class OneForOneStrategy implements SupervisorStrategyInterface
{
    /**
     * @param int $maxNrOfRetries
     * @param DateInterval $withinDuration
     * @param DeciderFunctionInterface|Closure $decider
     */
    public function __construct(
        private int $maxNrOfRetries,
        private DateInterval $withinDuration,
        private DeciderFunctionInterface|Closure $decider
    ) {
    }

    /**
     * @param ActorSystem $actorSystem
     * @param SupervisorInterface $supervisor
     * @param Pid $child
     * @param RestartStatistics $restartStatistics
     * @param mixed $reason
     * @param mixed $message
     * @return void
     */
    public function handleFailure(
        ActorSystem $actorSystem,
        SupervisorInterface $supervisor,
        Pid $child,
        RestartStatistics $restartStatistics,
        mixed $reason,
        mixed $message
    ): void {
        $decider = $this->decider;
        $directive = $decider($reason);
        switch ($directive) {
            case Directive::Resume:
                $actorSystem->getEventStream()?->publish(
                    new SupervisorEvent($child, $reason, $directive)
                );
                $supervisor->resumeChildren($child);
                break;
            case Directive::Restart:
                if ($this->shouldStop($restartStatistics)) {
                    $actorSystem->getEventStream()?->publish(
                        new SupervisorEvent($child, $reason, Directive::Stop)
                    );
                    $supervisor->stopChildren($child);
                } else {
                    $actorSystem->getEventStream()?->publish(
                        new SupervisorEvent($child, $reason, Directive::Restart)
                    );
                    $supervisor->restartChildren($child);
                }
                break;
            case Directive::Stop:
                $actorSystem->getEventStream()?->publish(
                    new SupervisorEvent($child, $reason, Directive::Stop)
                );
                $supervisor->stopChildren($child);
                break;
            case Directive::Escalate:
                $supervisor->escalateFailure($reason, $message);
                break;
        }
    }

    /**
     * @param RestartStatistics $rs
     * @return bool
     */
    public function shouldStop(RestartStatistics $rs): bool
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
