<?php

declare(strict_types=1);

namespace Test\Router;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Ref;
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
                $system = ActorSystem::create();
                $r = $this->spawnMockProcess(
                    $system,
                    'child',
                    null,
                    function (?Ref $pid, mixed $message) {
                        var_dump($message);
                    }
                );
                $set = new ActorSystem\RefSet($r['ref']);
                $rs = new TestRouterState($system);
                $gr = new TestGroupRouter($system);
                $system->root()->spawn(
                    ActorSystem\Props::fromFunction(
                        new ActorSystem\Message\ReceiveFunction(
                            function (ActorSystem\Context\ContextInterface $context) {
                                var_dump($context->message());
                            }
                        ),
                        // ActorSystem\Props::withSpawnFunc()
                    )
                );
                $this->removeMockProcess($system, $r['ref']);
            });
        });
    }
}