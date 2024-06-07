<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Closure;
use Swoole\Timer;
use Swoole\Atomic\Long;

readonly class Throttle
{
    private Long $currentEvents;

    /**
     * @param int $maxEventsInPeriod
     * @param int $periodSeconds seconds
     * @param Closure(int): void $throttledCallback
     */
    public function __construct(
        private int $maxEventsInPeriod,
        private int $periodSeconds,
        private Closure $throttledCallback
    ) {
        $this->currentEvents = new Long(0);
    }

    public function shouldThrottle(): Valve
    {
        $tries = $this->currentEvents->add();
        if ($tries === 1) {
            $this->startTimer($this->periodSeconds);
        }
        if ($tries == $this->maxEventsInPeriod) {
            return Valve::Closing;
        } elseif ($tries > $this->maxEventsInPeriod) {
            return Valve::Closed;
        }

        return Valve::Open;
    }

    private function startTimer(int $duration): void
    {
        Timer::after($duration * 1000, function () {
            $n = 0;
            $cur = $this->currentEvents->get();
            $timesCalled = $n;
            if ($this->currentEvents->cmpset($cur, $n)) {
                $timesCalled = $cur;
            }
            if ($timesCalled > $this->maxEventsInPeriod) {
                ($this->throttledCallback)($timesCalled - $this->maxEventsInPeriod);
            }
        });
    }
}
