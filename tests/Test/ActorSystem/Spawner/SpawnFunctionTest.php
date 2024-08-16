<?php

declare(strict_types=1);

namespace Test\ActorSystem\Spawner;

use Phluxor\ActorSystem;
use PHPUnit\Framework\TestCase;
use Test\VoidActor;

use function Swoole\Coroutine\run;

class SpawnFunctionTest extends TestCase
{
    public function testInvoke(): void
    {
        run(function () {
            go(function () {
                $actorSystem = ActorSystem::create();
                $spawner = new ActorSystem\Spawner\SpawnFunction();
                $pid = $spawner(
                    $actorSystem,
                    'test',
                    ActorSystem\Props::fromProducer(fn() => new VoidActor()),
                    $actorSystem->root()
                );
                $this->assertSame('test', (string)$pid->getRef());
                $pid = $spawner(
                    $actorSystem,
                    'test',
                    ActorSystem\Props::fromProducer(fn() => new VoidActor()),
                    $actorSystem->root()
                );
                $this->assertNotNull($pid->isError());
                $this->assertInstanceOf(ActorSystem\Exception\SpawnErrorException::class, $pid->isError());
            });
        });
    }
}
