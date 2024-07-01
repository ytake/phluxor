<?php

declare(strict_types=1);

namespace Test\Router;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Ref;
use Phluxor\Router\Config;
use Phluxor\Router\Message\Broadcast;
use PHPUnit\Framework\TestCase;
use Test\ProcessTrait;

use function Swoole\Coroutine\run;

class ProcessTest extends TestCase
{
    use ProcessTrait;

    public function testRouterSendsUserMessageToChild(): void
    {
        run(function () {
            \Swoole\Coroutine\go(function () {
                $proceed = false;
                $system = ActorSystem::create();
                $r = $this->spawnMockProcess(
                    $system,
                    'child',
                    null,
                    function (?Ref $pid, mixed $message) use (&$proceed) {
                        $proceed = true;
                        $this->assertSame('hello', $message->getMessage());
                    }
                );
                $set = new ActorSystem\RefSet($r['ref']);
                $rs = new TestRouterState($system, $set);
                $gr = new TestGroupRouter($system);
                $gr->setRouterState($rs);
                $routerRef = $system->root()->spawn(
                    ActorSystem\Props::fromFunction(
                        new ActorSystem\Message\ReceiveFunction(
                            function (ActorSystem\Context\ContextInterface $context) {
                            }
                        ),
                        ActorSystem\Props::withSpawnFunc(Config::spawner($gr))
                    )
                );
                $system->root()->send($routerRef, new Broadcast('hello'));
                $system->root()->requestWithCustomSender($routerRef, 'hello', $routerRef);
                $this->removeMockProcess($system, $r['ref']);
                $this->assertTrue($proceed);
            });
        });
    }
}
