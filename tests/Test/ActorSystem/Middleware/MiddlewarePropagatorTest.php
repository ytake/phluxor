<?php

declare(strict_types=1);

namespace Test\ActorSystem\Middleware;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Middleware\Propagator\MiddlewarePropagation;
use Phluxor\ActorSystem\Props\SpawnMiddlewareInterface;
use PHPUnit\Framework\TestCase;
use Swoole\Lock;

use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;

class MiddlewarePropagatorTest extends TestCase
{
    private int $spawnCounter = 0;

    public function testMiddlewarePropagator(): void
    {
        run(function () {
            go(function () {
                $lock = new Lock(Lock::MUTEX);
                $system = ActorSystem::create();
                $propagator = new MiddlewarePropagation();
                $propagator->setItselfForwarded()
                    ->setSpawnMiddleware(
                        $this->spawnMiddleware($lock)
                    );
                $rootContext = new ActorSystem\RootContext($system);
                $rootContext->withSpawnMiddleware($propagator->spawnMiddleware());
                $root = $rootContext->spawn($this->start(5));
                $rootContext->stopFuture($root)->wait();
                $this->assertSame(5, $this->spawnCounter);
            });
        });
    }

    /**
     * @param Lock $lock
     * @return SpawnMiddlewareInterface
     */
    public function spawnMiddleware(Lock $lock): SpawnMiddlewareInterface
    {
        return new TestMiddleware($this->spawnCounter, $lock);
    }

    public function start(int $input): ActorSystem\Props
    {
        return ActorSystem\Props::fromFunction(
            new ActorSystem\Message\ReceiveFunction(
                function (ContextInterface $c) use ($input) {
                    $message = $c->message();
                    if ($message instanceof ActorSystem\Message\Started) {
                        if ($input > 0) {
                            $c->spawn($this->start($input - 1));
                        }
                    }
                }
            )
        );
    }
}
