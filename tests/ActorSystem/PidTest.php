<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use Brick\Math\Exception\MathException;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Ref;
use PHPUnit\Framework\TestCase;

class PidTest extends TestCase
{
    public function testShouldReturnDeadLetterProcess(): void
    {
        go(
        /**
         * @throws MathException
         */
            function () {
                $actor = ActorSystem::create();
                $pid = new ActorSystem\ProtoBuf\PID();
                $pid->setAddress('localhost');
                $pid->setId('test');
                $r = (new Ref($pid))->ref($actor);
                $this->assertInstanceOf(
                    ActorSystem\DeadLetterProcess::class,
                    $r
                );
            }
        );
        \Swoole\Event::wait();
    }
}
