<?php

declare(strict_types=1);

namespace Test;

use Brick\Math\Exception\MathException;
use Phluxor\ActorSystem;
use PHPUnit\Framework\TestCase;

use function Swoole\Coroutine\run;

class ActorSystemTest extends TestCase
{
    public function testActorSystem(): void
    {
        run(function () {
            go(function () {
                $actor = new ActorSystem();
                $actor->shutdown();
                $this->assertTrue($actor->isStopped());
            });
        });
    }

    public function testActorSystemCreate(): void
    {
        run(function () {
            go(
            /**
             * @throws MathException
             */
                function () {
                    $actor = ActorSystem::create();
                    $this->assertIsString($actor->getId());
                    $this->assertInstanceOf(ActorSystem\ProcessRegistryValue::class, $actor->getProcessRegistry());
                }
            );
        });
    }
}
