<?php

declare(strict_types=1);

namespace Example\Persistence;

use Phluxor\ActorSystem;
use Phluxor\Persistence\EventSourcedReceiver;
use Phluxor\Persistence\Mysql\Connection;
use Phluxor\Persistence\Mysql\DefaultSchema;
use Phluxor\Persistence\Mysql\Dsn;
use Phluxor\Persistence\Mysql\MysqlProvider;
use Psr\Log\LoggerInterface;
use Test\Persistence\ProtoBuf\TestMessage;

use function Swoole\Coroutine\run;

class SampleSystem
{
    public function main(): void
    {
        run(function () {
            \Swoole\Coroutine\go(function () {
                $system = ActorSystem::create();
                $props = ActorSystem\Props::fromProducer(fn() => new PersistenceActor(),
                    ActorSystem\Props::withReceiverMiddleware(
                        new EventSourcedReceiver(
                            $this->mysqlProvider($system->getLogger(), 3)
                        )
                    ));
                $ref = $system->root()->spawnNamed($props, 'test.actor');
                $system->root()->send($ref->getRef(), new TestMessage(['message' => 'hello']));
            });
        });
    }

    private function mysqlProvider(
        LoggerInterface $logger,
        int $snapshotInterval
    ): MysqlProvider {
        $conn = new Connection(
            new Dsn(
                '127.0.0.1',
                3306,
                'sample',
                'user',
                'passw@rd'
            ));
        return new MysqlProvider(
            $conn->proxy(),
            new DefaultSchema(),
            $snapshotInterval,
            $logger
        );
    }
}
