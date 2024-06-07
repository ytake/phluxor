<?php

declare(strict_types=1);

namespace Test\ActorSystem\Child;

use Phluxor\ActorSystem\Child\RestartStatistics;
use PHPUnit\Framework\TestCase;

class RestartStatisticsTest extends TestCase
{
    private RestartStatistics $restartStats;

    protected function setUp(): void
    {
        $this->restartStats = new RestartStatistics();
    }

    public function testFailureCountInitiallyZero(): void
    {
        $this->assertSame(0, $this->restartStats->failureCount(), "Initial failure count should be zero.");
    }

    public function testFailIncreasesFailureCount(): void
    {
        $this->restartStats->fail();
        $this->assertSame(1, $this->restartStats->failureCount(), "Failure count should be 1 after one fail.");
    }

    public function testResetClearsFailures(): void
    {
        $this->restartStats->fail();
        $this->restartStats->reset();
        $this->assertSame(0, $this->restartStats->failureCount(), "Failure count should be reset to zero.");
    }

    public function testNumberOfFailuresWithinDuration(): void
    {
        $this->restartStats->fail(); // First failure
        sleep(2); // Wait for 2 seconds
        $this->restartStats->fail(); // Second failure

        $interval = new \DateInterval('PT3S'); // 3 seconds interval
        $this->assertSame(
            2,
            $this->restartStats->numberOfFailures($interval),
            "Should count 2 failures within 3 seconds."
        );

        $interval = new \DateInterval('PT1S'); // 1 second interval
        $this->assertSame(
            1,
            $this->restartStats->numberOfFailures($interval),
            "Should count 1 failure within 1 second."
        );
    }

    public function testNumberOfFailuresNoDuration(): void
    {
        $this->restartStats->fail(); // Add a failure
        $this->assertSame(
            1,
            $this->restartStats->numberOfFailures(new \DateInterval('PT0S')),
            "Should return total failures when no duration is specified."
        );
    }
}
