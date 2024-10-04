<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Strategy;

use DateInterval;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Ref;
use Phluxor\ActorSystem\SupervisorInterface;
use Phluxor\ActorSystem\SupervisorStrategyInterface;
use Phluxor\ActorSystem\Child;
use Random\RandomException;
use Swoole\Timer;

use function random_int;

final readonly class ExponentialBackoffStrategy implements SupervisorStrategyInterface
{
    /**
     * @param DateInterval $backoffWindow
     * @param DateInterval $initialBackoff
     */
    public function __construct(
        private DateInterval $backoffWindow,
        private DateInterval $initialBackoff,
    ) {
    }

    /**
     * @param ActorSystem $actorSystem
     * @param SupervisorInterface $supervisor
     * @param Ref $child
     * @param Child\RestartStatistics $restartStatistics
     * @param mixed $reason
     * @param mixed $message
     * @return void
     * @throws RandomException
     */
    public function handleFailure(
        ActorSystem $actorSystem,
        SupervisorInterface $supervisor,
        Ref $child,
        Child\RestartStatistics $restartStatistics,
        mixed $reason,
        mixed $message
    ): void {
        $this->setFailureCount($restartStatistics);
        $backoff = $restartStatistics->failureCount() * $this->initialBackoff->s;
        $noise = random_int(0, 500);
        $dur = $backoff * 1000 + $noise;
        Timer::after($dur, function () use ($actorSystem, $supervisor, $child, $reason) {
            $actorSystem->getEventStream()?->publish(
                new SupervisorEvent(
                    $child,
                    $reason,
                    ActorSystem\Directive::Restart
                )
            );
            $supervisor->restartChildren($child);
        });
    }

    private function setFailureCount(Child\RestartStatistics $restartStatistics): void
    {
        if ($restartStatistics->numberOfFailures($this->backoffWindow) == 0) {
            $restartStatistics->reset();
        }
        $restartStatistics->fail();
    }
}
