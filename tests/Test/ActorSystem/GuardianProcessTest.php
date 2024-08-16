<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use DateInterval;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\GuardianProcess;
use Phluxor\ActorSystem\Ref;
use PHPUnit\Framework\TestCase;
use Test\ProcessTrait;

use function Swoole\Coroutine\run;

class GuardianProcessTest extends TestCase
{
    use ProcessTrait;

    public function testShouldReturnGuardianRef(): void
    {
        run(function () {
            go(function() {
                $system = ActorSystem::create();
                $duration = new DateInterval('PT1S');
                $strategy = new ActorSystem\Strategy\OneForOneStrategy(
                    maxNrOfRetries: 0,
                    withinDuration: $duration,
                    decider: fn($reason) => ActorSystem\Directive::Restart,
                );
                $isReceived = false;
                $actor = $this->spawnMockProcess(
                    $system,
                    'actor1',
                    function (?Ref $pid, mixed $message) use (&$isReceived) {
                        $isReceived = true;
                    }
                );
                $guardian = new GuardianProcess(
                    new ActorSystem\GuardiansValue($system),
                    $actor['ref'],
                    $strategy
                );
                $this->assertEquals('actor1', (string) $guardian->getRef());
                $this->assertFalse($isReceived);
            });
        });
    }
}