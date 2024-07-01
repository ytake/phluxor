<?php

declare(strict_types=1);

namespace Test\ActorSystem\Strategy;

use DateInterval;
use DateTimeImmutable;
use Phluxor\ActorSystem\Child\RestartStatistics;
use Phluxor\ActorSystem\Strategy\ExponentialBackoffStrategy;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ExponentialBackoffStrategyTest extends TestCase
{
    public function testExponentialBackoffStrategySetFailureCount(): void
    {
        $cases = [
            [
                'n' => 'failure outside window; increment count',
                'ft' => new DateTimeImmutable("-11 second"),
                'fc' => 10,
                'expected' => 1,
            ],
            [
                'n' => 'failure inside window; increment count',
                'ft' => new DateTimeImmutable("-9 second"),
                'fc' => 10,
                'expected' => 11,
            ],
        ];
        foreach ($cases as $case) {
            $s = new ExponentialBackoffStrategy(
                new DateInterval("PT10S"),
                new DateInterval("PT0S")
            );
            $rs = new RestartStatistics();
            for ($i = 0; $i < $case['fc']; $i++) {
                $rs->append($case['ft']);
            }
            $ref = new ReflectionMethod($s, 'setFailureCount');
            $ref->invoke($s, $rs);
            $this->assertSame($case['expected'], $rs->failureCount());
        }
    }

    public function testExponentialBackoffStrategyIncrementsFailureCount(): void
    {
        $s = new ExponentialBackoffStrategy(
            new DateInterval("PT10S"),
            new DateInterval("PT0S")
        );
        $rs = new RestartStatistics();
        for ($i = 0; $i < 3; $i++) {
            $ref = new ReflectionMethod($s, 'setFailureCount');
            $ref->invoke($s, $rs);
        }
        $this->assertSame(3, $rs->failureCount());
    }

    public function testExponentialBackoffStrategyResetsFailureCount(): void
    {
        $rs = new RestartStatistics();
        for ($i = 0; $i < 10; $i++) {
            $rs->append(new DateTimeImmutable('-11 second'));
        }
        $s = new ExponentialBackoffStrategy(
            new DateInterval("PT10S"),
            new DateInterval("PT1S")
        );
        $ref = new ReflectionMethod($s, 'setFailureCount');
        $ref->invoke($s, $rs);
        $this->assertSame(1, $rs->failureCount());
    }
}
