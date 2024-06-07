<?php

declare(strict_types=1);

namespace Test\ActorSystem\Spawner;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Props;
use Phluxor\ActorSystem\Spawner\DefaultSpawner;
use PHPUnit\Framework\TestCase;
use Test\VoidActor;

class DefaultSpawnerTest extends TestCase
{
    // should be spawn actor
    public function testSpawnActor(): void
    {
        $actorSystem = ActorSystem::create();
        go(function (ActorSystem $actorSystem) {
            $spawner = new DefaultSpawner();
            $pid = $spawner(
                $actorSystem,
                'test',
                Props::fromProducer(fn() => new VoidActor()),
                $actorSystem->root()
            );
            $this->assertSame('test', (string)$pid->getPid());
            $pid = $spawner(
                $actorSystem,
                'test',
                Props::fromProducer(fn() => new VoidActor()),
                $actorSystem->root()
            );
            $this->assertNotNull($pid->isError());
            $this->assertInstanceOf(ActorSystem\Exception\SpawnErrorException::class, $pid->isError());
        }, $actorSystem);
        \Swoole\Event::wait();
    }
}
