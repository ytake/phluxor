<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Child;

use DateInterval;
use DateTimeImmutable;

class RestartStatistics
{
    /**
     * @param DateTimeImmutable[] $failureTimes
     */
    public function __construct(
        private array $failureTimes = []
    ) {
    }

    /**
     * returns failure count
     * @return int
     */
    public function failureCount(): int
    {
        return count($this->failureTimes);
    }

    /**
     * increases the associated actors' failure count
     * @return void
     */
    public function fail(): void
    {
        $this->failureTimes[] = new DateTimeImmutable();
    }

    /**
     * the associated actors' failure count
     * @return void
     */
    public function reset(): void
    {
        $this->failureTimes = [];
    }

    /**
     * returns number of failures within a given duration
     * @param DateInterval $withinDuration
     * @return int
     */
    public function numberOfFailures(DateInterval $withinDuration): int
    {
        if ($withinDuration->s === 0) {
            return count($this->failureTimes);
        }

        $num = 0;
        $currTime = new DateTimeImmutable();
        foreach ($this->failureTimes as $time) {
            if ($currTime->getTimestamp() - $time->getTimestamp() < $withinDuration->s) {
                $num++;
            }
        }

        return $num;
    }

    /**
     * @param DateTimeImmutable $time
     * @return void
     */
    public function append(DateTimeImmutable $time): void
    {
        $this->failureTimes[] = $time;
    }
}
