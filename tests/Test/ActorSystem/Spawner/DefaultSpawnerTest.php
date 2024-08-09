<?php

declare(strict_types=1);

namespace Test\ActorSystem\Spawner;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Props;
use Phluxor\ActorSystem\Spawner\DefaultSpawner;
use PHPUnit\Framework\TestCase;
use Test\VoidActor;

use function Phluxor\Swoole\Coroutine\run;

class DefaultSpawnerTest extends TestCase
{
    // should be spawn actor
    public function testSpawnActor(): void
    {
        run(function () {
            go(function () {
                $actorSystem = ActorSystem::create();
                $spawner = new DefaultSpawner();
                $pid = $spawner(
                    $actorSystem,
                    'test',
                    Props::fromProducer(fn() => new VoidActor()),
                    $actorSystem->root()
                );
                $this->assertSame('test', (string)$pid->getRef());
                $pid = $spawner(
                    $actorSystem,
                    'test',
                    Props::fromProducer(fn() => new VoidActor()),
                    $actorSystem->root()
                );
                $this->assertNotNull($pid->isError());
                $this->assertInstanceOf(ActorSystem\Exception\SpawnErrorException::class, $pid->isError());
            });
        });
    }
}
