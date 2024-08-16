<?php

declare(strict_types=1);

namespace Test\Router;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Message\ActorInterface;
use Phluxor\Router\ProtoBuf\GetRoutees;
use Phluxor\Router\ProtoBuf\Routees;
use Phluxor\Router\RoundRobin\PoolRouter;
use PHPUnit\Framework\TestCase;

use function Swoole\Coroutine\run;

class PoolRouterTest extends TestCase
{
    public function testBroadcastGroupPoolCreatesRoutees(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $props = PoolRouter::create(
                    3,
                    ActorSystem\Props::withProducer(fn() => $this->noneActor())
                );
                $router = $system->root()->spawn($props);
                $future = $system->root()->requestFuture($router, new GetRoutees(), 1000);
                $v = $future->result()->value();
                $this->assertInstanceOf(Routees::class, $v);
                $this->assertCount(3, $v->getPids());
            });
        });
    }

    private function noneActor(): ActorInterface
    {
        return new class implements ActorInterface {
            public function receive(ContextInterface $context): void
            {
                // none
            }
        };
    }
}