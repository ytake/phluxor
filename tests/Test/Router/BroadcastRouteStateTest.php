<?php

declare(strict_types=1);

namespace Test\Router;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Message\ActorInterface;
use Phluxor\ActorSystem\Ref;
use Phluxor\Router\Broadcast\GroupRouter;
use Phluxor\Router\ProtoBuf\AddRoutee;
use PHPUnit\Framework\TestCase;
use Test\ProcessTrait;
use Test\Router\ConsistentHash\Received;
use Test\VoidActor;

use function Phluxor\Swoole\Coroutine\run;

class BroadcastRouteStateTest extends TestCase
{
    use ProcessTrait;

    public function testShouldBroadcastMessageToAllRoutees(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $count = 0;
                $count2 = 0;
                $actor = $this->spawnMockProcess(
                    $system,
                    'mock1',
                    null,
                    function (?Ref $pid, mixed $message) use (&$count) {
                        $count++;
                    }
                );
                $actor2 = $this->spawnMockProcess(
                    $system,
                    'mock2',
                    null,
                    function (?Ref $pid, mixed $message) use (&$count2) {
                        $count2++;
                    }
                );
                $g = $system->root()->spawn(GroupRouter::create($actor['ref'], $actor2['ref']));
                $props = ActorSystem\Props::fromProducer(fn() => new VoidActor());
                for ($i = 0; $i < 100; $i++) {
                    $ref = $system->root()->spawnNamed($props, 'test' . $i);
                    $system->root()->send($g, new AddRoutee(['pid' => $ref->getRef()->protobufPid()]));
                    $system->root()->send($g, 'hello');
                }
                $this->assertSame($count, $count2);
            });
        });
    }

    public function testAllRouteesReceiveMessages(): void
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
                $system->root()->send($g, "hello!");
                $cases = [
                    'routee1' => 'hello!',
                    'routee2' => 'hello!',
                    'routee3' => 'hello!',
                ];
                foreach ($cases as $routee => $message) {
                    $ref = new ActorSystem\Ref(new ActorSystem\ProtoBuf\Pid([
                        'id' => $routee,
                        'address' => ActorSystem::LOCAL_ADDRESS
                    ]));
                    $receiveCount = $system->root()->requestFuture($ref, new Received, 2000);
                    $this->assertSame($message, $receiveCount->result()->value());
                }
            });
        });
    }

    private function myActor(): ActorInterface
    {
        return new class implements ActorSystem\Message\ActorInterface {
            private string $received = '';

            public function receive(ContextInterface $context): void
            {
                $msg = $context->message();
                switch (true) {
                    case $msg instanceof Received:
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