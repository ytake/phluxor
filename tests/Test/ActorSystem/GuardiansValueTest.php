<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use DateInterval;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Directive;
use Phluxor\ActorSystem\GuardiansValue;
use Phluxor\ActorSystem\Strategy\OneForOneStrategy;
use PHPUnit\Framework\TestCase;

use function Swoole\Coroutine\run;

class GuardiansValueTest extends TestCase
{
    public function testShouldReturnGuardianRef(): void
    {
        run(function () {
            go(function() {
                $system = ActorSystem::create();
                $guardian = new GuardiansValue($system);
                $duration = new DateInterval('PT1S');
                $strategy = new OneForOneStrategy(
                    maxNrOfRetries: 0,
                    withinDuration: $duration,
                    decider: fn($reason) => Directive::Restart,
                );
                $ref = $guardian->getGuardianRef($strategy);
                $this->assertNotNull($ref, "Guardian should have a Ref.");
                $this->assertSame('guardian$1', (string) $ref);
                $strategy = new OneForOneStrategy(
                    maxNrOfRetries: 0,
                    withinDuration: $duration,
                    decider: fn($reason) => Directive::Restart,
                );
                $ref = $guardian->getGuardianRef($strategy);
                $this->assertSame('guardian$2', (string) $ref);
            });
        });
    }
}
