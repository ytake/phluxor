<?php

declare(strict_types=1);

namespace Test\ActorSystem\Strategy;

use DateInterval;
use DateTimeImmutable;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\ActorContext;
use Phluxor\ActorSystem\Child\RestartStatistics;
use Phluxor\ActorSystem\Directive;
use Phluxor\ActorSystem\Props;
use Phluxor\ActorSystem\Strategy\AllForOneStrategy;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Test\NullProducer;

use function Swoole\Coroutine\run;

class AllForOneStrategyTest extends TestCase
{
    public function testAllForOneStrategyRestartPermission(): void
    {
        $duration = new DateInterval('PT1S');
        $strategy = new AllForOneStrategy(
            maxNrOfRetries: 0,
            withinDuration: $duration,
            decider: fn($reason) => Directive::Restart,
        );
        $rs = new RestartStatistics();
        $ref = new ReflectionMethod($strategy, 'shouldStop');
        $this->assertTrue($ref->invoke($strategy, $rs));
        $this->assertSame(0, $rs->numberOfFailures($duration));

        $duration = new DateInterval('PT1S');
        $strategy = new AllForOneStrategy(
            maxNrOfRetries: 1,
            withinDuration: $duration,
            decider: fn($reason) => Directive::Restart,
        );
        $rs = new RestartStatistics();
        $ref = new ReflectionMethod($strategy, 'shouldStop');
        $this->assertFalse($ref->invoke($strategy, $rs));
        $this->assertSame(1, $rs->numberOfFailures($duration));
    }

    // restart when duration is 0 and exceeds max retries
    public function testShouldStopWhenDurationIsZeroAndExceedsMaxRetries(): void
    {
        $duration = new DateInterval('PT0S');
        $strategy = new AllForOneStrategy(
            maxNrOfRetries: 1,
            withinDuration: $duration,
            decider: fn($reason) => Directive::Restart,
        );
        $rs = new RestartStatistics([
            new DateTimeImmutable('-1 second'),
        ]);
        $ref = new ReflectionMethod($strategy, 'shouldStop');
        $this->assertTrue($ref->invoke($strategy, $rs));
        $this->assertSame(0, $rs->numberOfFailures($duration));
    }

    public function testShouldNotStopWhenDurationSetAndWithinWindow(): void
    {
        $duration = new DateInterval('PT10S');
        $strategy = new AllForOneStrategy(
            maxNrOfRetries: 2,
            withinDuration: $duration,
            decider: fn($reason) => Directive::Restart,
        );
        $rs = new RestartStatistics([
            new DateTimeImmutable('-5 second'),
        ]);
        $ref = new ReflectionMethod($strategy, 'shouldStop');
        $this->assertFalse($ref->invoke($strategy, $rs));
        $this->assertSame(2, $rs->numberOfFailures($duration));
    }

    public function testShouldStopWhenDurationSetWithinWindowAndExceedsMaxRetries(): void
    {
        $duration = new DateInterval('PT10S');
        $strategy = new AllForOneStrategy(
            maxNrOfRetries: 1,
            withinDuration: $duration,
            decider: fn($reason) => Directive::Restart,
        );
        $rs = new RestartStatistics([
            new DateTimeImmutable('-5 second'),
            new DateTimeImmutable('-5 second'),
        ]);
        $ref = new ReflectionMethod($strategy, 'shouldStop');
        $this->assertTrue($ref->invoke($strategy, $rs));
        $this->assertSame(0, $rs->numberOfFailures($duration));
    }

    public function testShouldStopWhenDurationSetOutsideWindow(): void
    {
        $duration = new DateInterval('PT10S');
        $strategy = new AllForOneStrategy(
            maxNrOfRetries: 1,
            withinDuration: $duration,
            decider: fn($reason) => Directive::Restart,
        );
        $rs = new RestartStatistics([
            new DateTimeImmutable('-11 second'),
            new DateTimeImmutable('-11 second'),
        ]);
        $ref = new ReflectionMethod($strategy, 'shouldStop');
        $this->assertFalse($ref->invoke($strategy, $rs));
        $this->assertSame(1, $rs->numberOfFailures($duration));
    }

    public function testAllForOneStrategyIncrementsFailureCount(): void
    {
        run(function () {
            go(function () {
                $duration = new DateInterval('PT10S');
                $strategy = new AllForOneStrategy(
                    maxNrOfRetries: 1,
                    withinDuration: $duration,
                    decider: fn($reason) => Directive::Restart,
                );
                $rs = new RestartStatistics();
                $system = ActorSystem::create();
                $props = Props::fromProducer(new NullProducer());
                $context = new ActorContext($system, $props, null);
                $isProceed = false;
                $system->getEventStream()->subscribe(function ($event) use ($system, &$isProceed) {
                    $this->assertInstanceOf(ActorSystem\Strategy\SupervisorEvent::class, $event);
                    $this->assertSame('reason', $event->getReason());
                    $this->assertSame(Directive::Restart, $event->getDirective());
                    $isProceed = true;
                });

                $strategy->handleFailure(
                    $system,
                    $context,
                    new ActorSystem\Ref(new ActorSystem\ProtoBuf\PID()),
                    $rs,
                    'reason',
                    'message'
                );
                $this->assertSame(1, $rs->failureCount());
                $this->assertTrue($isProceed);
            });
        });
    }
}
