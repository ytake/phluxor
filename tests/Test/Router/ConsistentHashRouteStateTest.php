<?php

declare(strict_types=1);

namespace Test\Router;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Message\ActorInterface;
use Phluxor\Router\ConsistentHash\GroupRouter;
use Phluxor\Router\Message\Broadcast;
use Phluxor\Router\ProtoBuf\AddRoutee;
use Phluxor\Router\ProtoBuf\GetRoutees;
use Phluxor\Router\ProtoBuf\RemoveRoutee;
use Phluxor\Router\ProtoBuf\Routees;
use PHPUnit\Framework\TestCase;
use Test\ProcessTrait;
use Test\Router\ConsistentHash\HashMessage;
use Test\Router\ConsistentHash\ReceiveCount;

use function Phluxor\Swoole\Coroutine\run;

class ConsistentHashRouteStateTest extends TestCase
{
    use ProcessTrait;

    public function testShouldAlwaysGoToSameRoutee(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $routees = [];
                for ($i = 1; $i <= 3; $i++) {
                    $routees[] = $system->root()->spawnNamed(
                        ActorSystem\Props::fromProducer(fn() => $this->myActor()),
                        'routee' . $i
                    )->getRef();
                }
                $g = $system->root()->spawn(GroupRouter::create(...$routees));
                for ($i = 0; $i < 10; $i++) {
                    $system->root()->send($g, new HashMessage('message1'));
                }
                $cases = [
                    'routee1' => 0,
                    'routee2' => 0,
                    'routee3' => 10,
                ];
                foreach ($cases as $routee => $count) {
                    $ref = new ActorSystem\Ref(new ActorSystem\ProtoBuf\Pid([
                        'id' => $routee,
                        'address' => ActorSystem::LOCAL_ADDRESS
                    ]));
                    $receiveCount = $system->root()->requestFuture($ref, new ReceiveCount(), 2000);
                    $this->assertSame($count, $receiveCount->result()->value());
                }
            });
        });
    }

    public function testShouldRouteesCanBeAdded(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $routees = [];
                for ($i = 1; $i <= 3; $i++) {
                    $routees[] = $system->root()->spawnNamed(
                        ActorSystem\Props::fromProducer(fn() => $this->myActor()),
                        'routee' . $i
                    )->getRef();
                }
                $g = $system->root()->spawn(GroupRouter::create(...$routees));

                $routee4 = $system->root()->spawnNamed(
                    ActorSystem\Props::fromProducer(fn() => $this->myActor()),
                    'routee4'
                );
                $system->root()->send($g, new AddRoutee(['pid' => $routee4->getRef()->protobufPid()]));
                sleep(1);
                $future = $system->root()->requestFuture($g, new GetRoutees(), 1000);
                $v = $future->result()->value();
                $this->assertInstanceOf(Routees::class, $v);
                $this->assertCount(4, $v->getPids());
            });
        });
    }

    public function testShouldRouteesCanBeRemoved(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $routees = [];
                for ($i = 1; $i <= 3; $i++) {
                    $routees[] = $system->root()->spawnNamed(
                        ActorSystem\Props::fromProducer(fn() => $this->myActor()),
                        'routee' . $i
                    )->getRef();
                }
                $g = $system->root()->spawn(GroupRouter::create(...$routees));
                $system->root()->send($g, new RemoveRoutee([
                    'pid' => new ActorSystem\ProtoBuf\Pid([
                        'id' => 'routee3',
                        'address' => ActorSystem::LOCAL_ADDRESS
                    ])
                ]));
                sleep(1);
                $future = $system->root()->requestFuture($g, new GetRoutees(), 1000);
                $v = $future->result()->value();
                $this->assertInstanceOf(Routees::class, $v);
                $this->assertCount(2, $v->getPids());
            });
        });
    }

    public function testShouldAlwaysGoToSameRouteeEvenWhenNewRouteeAdded(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $routees = [];
                for ($i = 1; $i <= 3; $i++) {
                    $routees[] = $system->root()->spawnNamed(
                        ActorSystem\Props::fromProducer(fn() => $this->myActor()),
                        'routee' . $i
                    )->getRef();
                }
                $g = $system->root()->spawn(GroupRouter::create(...$routees));

                $routee4 = $system->root()->spawnNamed(
                    ActorSystem\Props::fromProducer(fn() => $this->myActor()),
                    'routee4'
                );
                $system->root()->send($g, new AddRoutee(['pid' => $routee4->getRef()->protobufPid()]));
                sleep(1);
                for ($i = 0; $i < 10; $i++) {
                    $system->root()->send($g, new HashMessage('message1'));
                }
                $cases = [
                    'routee1' => 0,
                    'routee2' => 0,
                    'routee3' => 1,
                    'routee4' => 10,
                ];
                $system->root()->send($g, new HashMessage('message4'));
                foreach ($cases as $routee => $count) {
                    $ref = new ActorSystem\Ref(new ActorSystem\ProtoBuf\Pid([
                        'id' => $routee,
                        'address' => ActorSystem::LOCAL_ADDRESS
                    ]));
                    $receiveCount = $system->root()->requestFuture($ref, new ReceiveCount(), 2000);
                    $this->assertSame($count, $receiveCount->result()->value());
                }
            });
        });
    }

    public function testShouldMessageIsReassignedWhenRouteeRemoved(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $routees = [];
                for ($i = 1; $i <= 3; $i++) {
                    $routees[] = $system->root()->spawnNamed(
                        ActorSystem\Props::fromProducer(fn() => $this->myActor()),
                        'routee' . $i
                    )->getRef();
                }
                $g = $system->root()->spawn(GroupRouter::create(...$routees));
                $system->root()->send($g, new HashMessage('message1'));
                foreach (
                    [
                        'routee1' => 0,
                        'routee2' => 0,
                        'routee3' => 1,
                    ] as $routee => $count
                ) {
                    $ref = new ActorSystem\Ref(new ActorSystem\ProtoBuf\Pid([
                        'id' => $routee,
                        'address' => ActorSystem::LOCAL_ADDRESS
                    ]));
                    $receiveCount = $system->root()->requestFuture($ref, new ReceiveCount(), 2000);
                    $this->assertSame($count, $receiveCount->result()->value(), "Routee $routee");
                }

                $system->root()->send($g, new RemoveRoutee([
                    'pid' => new ActorSystem\ProtoBuf\Pid([
                        'id' => 'routee3',
                        'address' => ActorSystem::LOCAL_ADDRESS
                    ])
                ]));
                sleep(1);
                $system->root()->send($g, new HashMessage('message1'));
                $cases = [
                    'routee1' => 0,
                    'routee2' => 1,
                ];
                foreach ($cases as $routee => $count) {
                    $ref = new ActorSystem\Ref(new ActorSystem\ProtoBuf\Pid([
                        'id' => $routee,
                        'address' => ActorSystem::LOCAL_ADDRESS
                    ]));
                    $receiveCount = $system->root()->requestFuture($ref, new ReceiveCount(), 2000);
                    $this->assertSame($count, $receiveCount->result()->value(), "Routee $routee");
                }
            });
        });
    }

    public function testAllRouteesReceiveRouterBroadcastMessages(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $routees = [];
                for ($i = 1; $i <= 3; $i++) {
                    $routees[] = $system->root()->spawnNamed(
                        ActorSystem\Props::fromProducer(fn() => $this->myActor()),
                        'routee' . $i
                    )->getRef();
                }
                $g = $system->root()->spawn(GroupRouter::create(...$routees));
                $system->root()->send($g, new Broadcast('message1'));
                sleep(1);
                $cases = [
                    'routee1' => 1,
                    'routee2' => 1,
                    'routee3' => 1,
                ];
                foreach ($cases as $routee => $count) {
                    $ref = new ActorSystem\Ref(new ActorSystem\ProtoBuf\Pid([
                        'id' => $routee,
                        'address' => ActorSystem::LOCAL_ADDRESS
                    ]));
                    $receiveCount = $system->root()->requestFuture($ref, new ReceiveCount(), 2000);
                    $this->assertSame($count, $receiveCount->result()->value());
                }
            });
        });
    }

    private function myActor(): ActorInterface
    {
        return new class implements ActorSystem\Message\ActorInterface {
            private int $count = 0;

            public function receive(ContextInterface $context): void
            {
                $msg = $context->message();
                switch (true) {
                    case $msg == 'message1':
                    case $msg instanceof HashMessage:
                        $this->count++;
                        break;
                    case $msg instanceof ReceiveCount:
                        $context->respond($this->count);
                        break;
                    case $msg instanceof Broadcast:
                        $context->respond($msg);
                        break;
                }
            }
        };
    }
}
