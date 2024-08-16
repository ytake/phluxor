<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use Phluxor\ActorSystem\Throttle;
use Phluxor\ActorSystem\Valve;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\WaitGroup;

use function Swoole\Coroutine\run;

class ThrottleTest extends TestCase
{
    public function testThrottle(): void
    {
        run(function () {
            go(function () {
                $wg = new WaitGroup();
                $wg->add();
                $throttle = new Throttle(10, 1, function () use ($wg) {
                    $wg->done();
                });
                $throttle->shouldThrottle();
                $v = $throttle->shouldThrottle();
                $this->assertEquals(Valve::Open, $v);

                for ($i = 0; $i < 8; $i++) {
                    $v = $throttle->shouldThrottle();
                }
                $this->assertEquals(Valve::Closing, $v);
                $this->assertEquals(Valve::Closed, $throttle->shouldThrottle());

                $wg->wait();
                $this->assertEquals(Valve::Open, $throttle->shouldThrottle());
            });
        });
    }
}
