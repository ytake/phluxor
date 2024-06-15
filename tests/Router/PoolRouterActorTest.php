<?php

declare(strict_types=1);

namespace Test\Router;

use Phluxor\ActorSystem;
use Phluxor\Router\PoolRouterActor;
use Phluxor\Router\ProtoBuf\AddRoutee;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\WaitGroup;
use Test\MockContext;

use function Swoole\Coroutine\run;

class PoolRouterActorTest extends TestCase
{
    public function testPoolRouterReceiveAddRoute(): void
    {
        run(function () {
            \Swoole\Coroutine\go(function () {
                $system = ActorSystem::create();
                $state = new TestRouterState($system, new ActorSystem\RefSet());
                $a = new PoolRouterActor(
                    ActorSystem\Props::fromFunction(
                        new ActorSystem\Message\ReceiveFunction(
                            function (ActorSystem\Context\ContextInterface $context) {
                                var_dump($context->message());
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
                        'PID' => new ActorSystem\ProtoBuf\PID([
                            'address' => 'test',
                            'id' => 1
                        ])
                    ]);
                });
                $a->receive($m);
                $routees = $state->getRoutees();
                $this->assertTrue(
                    $routees->contains(new ActorSystem\Ref(new ActorSystem\ProtoBuf\PID([
                    'address' => 'test',
                    'id' => 1
                    ])))
                );
            });
        });
    }
}
