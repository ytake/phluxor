<?php

declare(strict_types=1);

namespace Test\Router;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Message\ActorInterface;
use Phluxor\Router\ProtoBuf\AddRoutee;
use Phluxor\Router\ProtoBuf\GetRoutees;
use Phluxor\Router\ProtoBuf\RemoveRoutee;
use Phluxor\Router\ProtoBuf\Routees;
use Phluxor\Router\RoundRobin\GroupRouter;
use PHPUnit\Framework\TestCase;

use function Swoole\Coroutine\go;
use function Phluxor\Swoole\Coroutine\run;

class RoundRobinGroupTest extends TestCase
{
    public function testRoundRobinGroupRouterRouteesReceiveMessagesInRoundRobin(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $roundRobin = $this->createRoundRobinRouterWith3Routees($system);
                $system->root()->send($roundRobin, '1');
                $this->assertSame(
                    '1',
                    $system->root()->requestFuture(
                        $this->genRef('routee1', $system),
                        'received?',
                        1000
                    )->result()->value()
                );
                $this->assertEmpty(
                    $system->root()->requestFuture(
                        $this->genRef('routee2', $system),
                        'received?',
                        1000
                    )->result()->value()
                );
                $this->assertEmpty(
                    $system->root()->requestFuture(
                        $this->genRef('routee3', $system),
                        'received?',
                        1000
                    )->result()->value()
                );
                $system->root()->send($roundRobin, '2');
                $system->root()->send($roundRobin, '3');
                $this->assertSame(
                    '1',
                    $system->root()->requestFuture(
                        $this->genRef('routee1', $system),
                        'received?',
                        1000
                    )->result()->value()
                );
                $this->assertSame(
                    '2',
                    $system->root()->requestFuture(
                        $this->genRef('routee2', $system),
                        'received?',
                        1000
                    )->result()->value()
                );
                $this->assertSame(
                    '3',
                    $system->root()->requestFuture(
                        $this->genRef('routee3', $system),
                        'received?',
                        1000
                    )->result()->value()
                );
                $system->root()->send($roundRobin, '4');
                $this->assertSame(
                    '4',
                    $system->root()->requestFuture(
                        $this->genRef('routee1', $system),
                        'received?',
                        1000
                    )->result()->value()
                );
            });
        });
    }

    public function testRoundRobinGroupRouterRouteesCanBeRemoved(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $roundRobin = $this->createRoundRobinRouterWith3Routees($system);
                $system->root()->send(
                    $roundRobin,
                    new RemoveRoutee(['pid' => $this->genRef('routee1', $system)->protobufPid()])
                );
                /** @var Routees $routees */
                $routees = $system->root()->requestFuture($roundRobin, new GetRoutees(), 1000)->result()->value();
                $this->assertInstanceOf(Routees::class, $routees);
                $pids = $routees->getPids();
                foreach ($pids as $pid) {
                    $this->assertNotSame('routee1', $pid->getId());
                }
            });
        });
    }

    public function testRoundRobinGroupRouterRouteesCanBeAdded(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $roundRobin = $this->createRoundRobinRouterWith3Routees($system);
                $routee4 = $system->root()->spawnNamed(
                    ActorSystem\Props::fromProducer(fn() => $this->myActor()),
                    'routee4'
                );
                $system->root()->send(
                    $roundRobin,
                    new AddRoutee(['pid' => $routee4->getRef()->protobufPid()])
                );
                /** @var Routees $routees */
                $routees = $system->root()->requestFuture($roundRobin, new GetRoutees(), 1000)->result()->value();
                $this->assertInstanceOf(Routees::class, $routees);
                $pids = $routees->getPids();
                $this->assertSame(4, $pids->count());
            });
        });
    }

    public function testRoundRobinGroupRouterAddedRouteesReceiveMessages(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $roundRobin = $this->createRoundRobinRouterWith3Routees($system);
                $routee4 = $system->root()->spawnNamed(
                    ActorSystem\Props::fromProducer(fn() => $this->myActor()),
                    'routee4'
                );
                $system->root()->send(
                    $roundRobin,
                    new AddRoutee(['pid' => $routee4->getRef()->protobufPid()])
                );
                \Swoole\Coroutine::sleep(0.1);
                $system->root()->send($roundRobin, '1');
                $system->root()->send($roundRobin, '1');
                $system->root()->send($roundRobin, '1');
                $system->root()->send($roundRobin, '1');
                foreach (['routee1', 'routee2', 'routee3', 'routee4'] as $routee) {
                    $this->assertSame(
                        '1',
                        $system->root()->requestFuture(
                            $this->genRef($routee, $system),
                            'received?',
                            1000
                        )->result()->value()
                    );
                }
            });
        });
    }

    public function testRoundRobinGroupRouterRemovedRouteesNoLongerReceiveMessages(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $roundRobin = $this->createRoundRobinRouterWith3Routees($system);
                $system->root()->send($roundRobin, '0');
                $system->root()->send($roundRobin, '0');
                $system->root()->send($roundRobin, '0');
                $system->root()->send(
                    $roundRobin,
                    new RemoveRoutee(['pid' => $this->genRef('routee1', $system)->protobufPid()])
                );
                \Swoole\Coroutine::sleep(0.1);
                $system->root()->send($roundRobin, '3');
                $system->root()->send($roundRobin, '3');
                $system->root()->send($roundRobin, '3');
                $this->assertSame(
                    '0',
                    $system->root()->requestFuture(
                        $this->genRef('routee1', $system),
                        'received?',
                        1000
                    )->result()->value()
                );
                $this->assertSame(
                    '3',
                    $system->root()->requestFuture(
                        $this->genRef('routee2', $system),
                        'received?',
                        1000
                    )->result()->value()
                );
                $this->assertSame(
                    '3',
                    $system->root()->requestFuture(
                        $this->genRef('routee3', $system),
                        'received?',
                        1000
                    )->result()->value()
                );
            });
        });
    }

    private function genRef(string $name, ActorSystem $system): ActorSystem\Ref
    {
        return new ActorSystem\Ref(new ActorSystem\ProtoBuf\Pid([
            'id' => $name,
            'address' => ActorSystem::LOCAL_ADDRESS
        ]));
    }

    private function createRoundRobinRouterWith3Routees(ActorSystem $system): ActorSystem\Ref
    {
        /** @var ActorSystem\Ref[] $routees */
        $routees = [];
        for ($i = 1; $i <= 3; $i++) {
            $routees[] = $system->root()->spawnNamed(
                ActorSystem\Props::fromProducer(fn() => $this->myActor()),
                'routee' . $i
            )->getRef();
        }
        $ref = $system->root()->spawn(GroupRouter::create(...$routees));
        if (!$ref instanceof ActorSystem\Ref) {
            throw new \RuntimeException('Failed to create router');
        }
        return $ref;
    }

    private function myActor(): ActorInterface
    {
        return new class implements ActorSystem\Message\ActorInterface {
            private string $received = '';

            public function receive(ContextInterface $context): void
            {
                $msg = $context->message();
                switch (true) {
                    case $msg == 'received?':
                        $context->respond($this->received);
                        break;
                    case is_string($msg):
                        $this->received = $msg;
                        break;
                }
            }
        };
    }
}