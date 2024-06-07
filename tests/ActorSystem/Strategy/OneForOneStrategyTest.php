<?php

declare(strict_types=1);

namespace Test\ActorSystem\Strategy;

use DateInterval;
use DateTimeImmutable;
use Phluxor\ActorSystem\Directive;
use Phluxor\ActorSystem\Child\RestartStatistics;
use Phluxor\ActorSystem\Strategy\OneForOneStrategy;
use PHPUnit\Framework\TestCase;

class OneForOneStrategyTest extends TestCase
{
    public function testOneForOneStrategyRestartPermission(): void
    {
        $duration = new DateInterval('PT1S');
        $strategy = new OneForOneStrategy(
            maxNrOfRetries: 0,
            withinDuration: $duration,
            decider: fn($reason) => Directive::Restart,
        );
        $rs = new RestartStatistics();
        $this->assertTrue($strategy->shouldStop($rs));
        $this->assertSame(0, $rs->numberOfFailures($duration));

        $duration = new DateInterval('PT1S');
        $strategy = new OneForOneStrategy(
            maxNrOfRetries: 1,
            withinDuration: $duration,
            decider: fn($reason) => Directive::Restart,
        );
        $rs = new RestartStatistics();
        $this->assertFalse($strategy->shouldStop($rs));
        $this->assertSame(1, $rs->numberOfFailures($duration));
    }

    // restart when duration is 0 and exceeds max retries
    public function testShouldStopWhenDurationIsZeroAndExceedsMaxRetries(): void
    {
        $duration = new DateInterval('PT0S');
        $strategy = new OneForOneStrategy(
            maxNrOfRetries: 1,
            withinDuration: $duration,
            decider: fn($reason) => Directive::Restart,
        );
        $rs = new RestartStatistics([
            new DateTimeImmutable('-1 second'),
        ]);
        $this->assertTrue($strategy->shouldStop($rs));
        $this->assertSame(0, $rs->numberOfFailures($duration));
    }

    public function testShouldNotStopWhenDurationSetAndWithinWindow(): void
    {
        $duration = new DateInterval('PT10S');
        $strategy = new OneForOneStrategy(
            maxNrOfRetries: 2,
            withinDuration: $duration,
            decider: fn($reason) => Directive::Restart,
        );
        $rs = new RestartStatistics([
            new DateTimeImmutable('-5 second'),
        ]);
        $this->assertFalse($strategy->shouldStop($rs));
        $this->assertSame(2, $rs->numberOfFailures($duration));
    }

    public function testShouldStopWhenDurationSetWithinWindowAndExceedsMaxRetries(): void
    {
        $duration = new DateInterval('PT10S');
        $strategy = new OneForOneStrategy(
            maxNrOfRetries: 1,
            withinDuration: $duration,
            decider: fn($reason) => Directive::Restart,
        );
        $rs = new RestartStatistics([
            new DateTimeImmutable('-5 second'),
            new DateTimeImmutable('-5 second'),
        ]);
        $this->assertTrue($strategy->shouldStop($rs));
        $this->assertSame(0, $rs->numberOfFailures($duration));
    }

    public function testShouldStopWhenDurationSetOutsideWindow(): void
    {
        $duration = new DateInterval('PT10S');
        $strategy = new OneForOneStrategy(
            maxNrOfRetries: 1,
            withinDuration: $duration,
            decider: fn($reason) => Directive::Restart,
        );
        $rs = new RestartStatistics([
            new DateTimeImmutable('-11 second'),
            new DateTimeImmutable('-11 second'),
        ]);
        $this->assertFalse($strategy->shouldStop($rs));
        $this->assertSame(1, $rs->numberOfFailures($duration));
    }
}
