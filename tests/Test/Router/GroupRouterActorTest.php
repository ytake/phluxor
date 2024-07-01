<?php

declare(strict_types=1);

namespace Test\Router;

use Phluxor\ActorSystem;
use Phluxor\Router\GroupRouterActor;
use Phluxor\Router\Message\Broadcast;
use Phluxor\Router\ProtoBuf\AddRoutee;
use Phluxor\Router\ProtoBuf\GetRoutees;
use Phluxor\Router\ProtoBuf\RemoveRoutee;
use Phluxor\Router\ProtoBuf\Routees;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\WaitGroup;
use Test\MockContext;
use Test\ProcessTrait;

use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;

class GroupRouterActorTest extends TestCase
{
    use ProcessTrait;

    public function testGroupRouterReceiveAddRoute(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $state = new TestRouterState($system, new ActorSystem\RefSet());
                $a = new GroupRouterActor(
                    ActorSystem\Props::fromFunction(
                        new ActorSystem\Message\ReceiveFunction(
                            function (ActorSystem\Context\ContextInterface $context) {
                                // none
                            }
                        ),
                    ),
                    new TestGroupRouter($system),
                    $state,
                    new WaitGroup()
                );
                $m = new MockContext();
                $m->messageHandle(function () {
                    return new AddRoutee([
                        'Pid' => new ActorSystem\ProtoBuf\Pid([
                            'address' => 'test',
                            'id' => 1
                        ])
                    ]);
                });
                $a->receive($m);
                $routees = $state->getRoutees();
                $this->assertTrue(
                    $routees->contains(new ActorSystem\Ref(new ActorSystem\ProtoBuf\Pid([
                    'address' => 'test',
                    'id' => 1
                    ])))
                );
            });
        });
    }

    public function testGroupRouterReceiveAddRouteNoDuplicates(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $state = new TestRouterState($system, new ActorSystem\RefSet());
                $a = new GroupRouterActor(
                    ActorSystem\Props::fromFunction(
                        new ActorSystem\Message\ReceiveFunction(
                            function (ActorSystem\Context\ContextInterface $context) {
                                // none
                            }
                        ),
                    ),
                    new TestGroupRouter($system),
                    $state,
                    new WaitGroup()
                );
                $p = $system->newLocalAddress('p1');
                $m = new MockContext();
                $m->messageHandle(function () use ($p) {
                    return new AddRoutee([
                        'Pid' => $p->protobufPid()
                    ]);
                });
                $a->receive($m);
                $m->messageHandle(function () {
                    return new GetRoutees();
                });
                $proceed = false;
                $m->respondHandle(function ($response) use (&$proceed) {
                    $this->assertInstanceOf(Routees::class, $response);
                    $proceed = true;
                    /** @var Routees  $response*/
                    $this->assertSame(1, $response->getPIDs()->count());
                });
                $a->receive($m);
                $this->assertTrue($proceed);
            });
        });
    }

    public function testGroupRouterReceiveRemoveRoute(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $state = new TestRouterState($system, new ActorSystem\RefSet());
                $a = new GroupRouterActor(
                    ActorSystem\Props::fromFunction(
                        new ActorSystem\Message\ReceiveFunction(
                            function (ActorSystem\Context\ContextInterface $context) {
                                // none
                            }
                        ),
                    ),
                    new TestGroupRouter($system),
                    $state,
                    new WaitGroup()
                );
                $p1 = $this->spawnMockProcess($system, 'p1');
                $p = $system->newLocalAddress('p2');
                $m = new MockContext();
                $m->messageHandle(function () use ($p) {
                    return new AddRoutee([
                        'Pid' => $p->protobufPid()
                    ]);
                });
                $a->receive($m);
                $m->messageHandle(function () use ($p1) {
                    return new AddRoutee([
                        'Pid' => $p1['ref']->protobufPid()
                    ]);
                });
                $a->receive($m);
                // remove p1
                $m->messageHandle(function () use ($p1) {
                    return new RemoveRoutee([
                        'Pid' => $p1['ref']->protobufPid()
                    ]);
                });
                $m->sendHandle(function (?ActorSystem\Ref $ref, $message) use ($p1) {
                    $this->assertInstanceOf(ActorSystem\ProtoBuf\PoisonPill::class, $message);
                });
                $a->receive($m);
                $m->messageHandle(function () {
                    return new GetRoutees();
                });
                $proceed = false;
                $m->respondHandle(function ($response) use (&$proceed) {
                    $this->assertInstanceOf(Routees::class, $response);
                    $proceed = true;
                    /** @var Routees  $response*/
                    $this->assertSame(1, $response->getPIDs()->count());
                });
                $a->receive($m);
                $this->assertTrue($proceed);
            });
        });
    }

    public function testGroupRouterReceiveBroadcastMessage(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $state = new TestRouterState($system, new ActorSystem\RefSet());
                $a = new GroupRouterActor(
                    ActorSystem\Props::fromFunction(
                        new ActorSystem\Message\ReceiveFunction(
                            function (ActorSystem\Context\ContextInterface $context) {
                                // none
                            }
                        ),
                    ),
                    new TestGroupRouter($system),
                    $state,
                    new WaitGroup()
                );
                $m = new MockContext();
                $p1 = $system->newLocalAddress('p1');
                $m->messageHandle(function () use ($p1) {
                    return new AddRoutee([
                        'Pid' => $p1->protobufPid()
                    ]);
                });
                $a->receive($m);
                $p2 = $system->newLocalAddress('p2');
                $m->messageHandle(function () use ($p2) {
                    return new AddRoutee([
                        'Pid' => $p2->protobufPid()
                    ]);
                });
                $a->receive($m);
                $m->messageHandle(function () {
                    return new Broadcast('hello');
                });
                $count = 0;
                $m->requestWithCustomSenderHandle(
                    function (?ActorSystem\Ref $pid, $message, ?ActorSystem\Ref $sender) use (&$count) {
                        $this->assertSame('hello', $message);
                        $count++;
                    }
                );
                $a->receive($m);
                $this->assertSame(2, $count);
            });
        });
    }
}
