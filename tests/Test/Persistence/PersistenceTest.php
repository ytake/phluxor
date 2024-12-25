<?php

declare(strict_types=1);

namespace Test\Persistence;

use Phluxor\ActorSystem;
use Phluxor\Persistence\EventSourcedBehavior;
use Phluxor\Persistence\InMemoryProvider;
use Phluxor\Persistence\Message\RequestSnapshot;
use Phluxor\Persistence\Mixin;
use Phluxor\Persistence\PersistentInterface;
use PHPUnit\Framework\TestCase;
use Test\Persistence\ProtoBuf\TestMessage;

use Test\Persistence\ProtoBuf\TestSnapshot;

use function Swoole\Coroutine\run;

class PersistenceTest extends TestCase
{
    public function testPersistence(): void
    {
        $state = new DataState(2);
        $messages = ['hello', 'world'];
        $state = $state->initialize(1, ...$messages);
        $returns = [];
        $state->getEvents(
            'test.actor',
            0,
            1,
            function (mixed $event) use (&$returns): void {
                $returns[] = $event->getMessage();
            }
        );
        $this->assertEquals($messages, $returns);
    }

    public function testRecovers(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $deadLetter = false;
                $system->getEventStream()?->subscribe(function (mixed $event) use (&$deadLetter): void {
                    if ($event instanceof ActorSystem\DeadLetterEvent) {
                        $deadLetter = true;
                    }
                });
                $props = ActorSystem\Props::fromProducer(function () {
                    return new InMemoryTestActor();
                },
                    ActorSystem\Props::withReceiverMiddleware(
                        new EventSourcedBehavior(new InMemoryStateProvider(new InMemoryProvider(2)))
                    ));
                $ref = $system->root()->spawnNamed($props, 'test.actor');
                $this->assertNull($ref->isError());
                $system->root()->send($ref->getRef(), new TestMessage(['message' => 'hello']));
                $f = $system->root()->requestFuture($ref->getRef(), new Query(), 1);
                $this->assertSame('hello', $f->result()->value());

                $system->root()->poisonFuture($ref->getRef())?->wait();
                $system->root()->send($ref->getRef(), new TestMessage(['message' => 'world']));
                $ref = $system->root()->spawnNamed($props, 'test.actor');
                $this->assertNull($ref->isError());
                $f = $system->root()->requestFuture($ref->getRef(), new Query(), 1);
                $this->assertSame('hello', $f->result()->value());
                $system->root()->send($ref->getRef(), new TestMessage(['message' => 'hello2']));
                $system->root()->send($ref->getRef(), new TestMessage(['message' => 'hello3']));
                $this->assertTrue($deadLetter);
                $f = $system->root()->requestFuture($ref->getRef(), new Query(), 1);
                $this->assertSame('hello3', $f->result()->value());
                $system->root()->poisonFuture($ref->getRef())?->wait();
            });
        });
    }

    public function testReceiveRecovery(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $deadLetter = false;
                $system->getEventStream()?->subscribe(function (mixed $event) use (&$deadLetter): void {
                    if ($event instanceof ActorSystem\DeadLetterEvent) {
                        $deadLetter = true;
                    }
                });
                $props = ActorSystem\Props::fromProducer(fn() => $this->receiveRecoverActor(),
                    ActorSystem\Props::withReceiverMiddleware(
                        new EventSourcedBehavior(new InMemoryStateProvider(new InMemoryProvider(1)))
                    ));
                $ref = $system->root()->spawnNamed($props, 'test.actor');
                $this->assertNull($ref->isError());
                $system->root()->send($ref->getRef(), new TestMessage(['message' => 'hello']));
                $f = $system->root()->requestFuture($ref->getRef(), new Query(), 1);
                $this->assertSame('hello', $f->result()->value());

                $system->root()->poisonFuture($ref->getRef())?->wait();
                $system->root()->send($ref->getRef(), new TestMessage(['message' => 'world']));
                $ref = $system->root()->spawnNamed($props, 'test.actor');
                $this->assertNull($ref->isError());
                $f = $system->root()->requestFuture($ref->getRef(), new Query(), 1);
                $this->assertSame('hello', $f->result()->value());
                $system->root()->send($ref->getRef(), new TestMessage(['message' => 'hello2']));
                $system->root()->send($ref->getRef(), new TestMessage(['message' => 'hello3']));
                $this->assertTrue($deadLetter);
                $f = $system->root()->requestFuture($ref->getRef(), new Query(), 1);
                $this->assertSame('hello3', $f->result()->value());
                $f = $system->root()->requestFuture($ref->getRef(), new Messages(), 1);
                $this->assertSame(
                    [
                        'Phluxor\Persistence\Message\OfferSnapshot',
                        'Test\Persistence\ProtoBuf\TestMessage',
                        'Phluxor\Persistence\Message\ReplayCompleted'
                    ],
                    $f->result()->value()
                );
            });
        });
    }

    private function receiveRecoverActor(): ActorSystem\Message\ActorInterface
    {
        return new class() implements ActorSystem\Message\ActorInterface, PersistentInterface {
            use Mixin;

            private string $state = '';
            /** @var string[] */
            private array $receiveRecoverMessages = [];

            public function receive(ActorSystem\Context\ContextInterface $context): void
            {
                $msg = $context->message();
                switch (true) {
                    case $msg instanceof RequestSnapshot:
                        $this->persistenceSnapshot(new TestSnapshot(['message' => $this->state]));
                        break;
                    case $msg instanceof TestSnapshot:
                        $this->state = $msg->getMessage();
                        break;
                    case $msg instanceof TestMessage:
                        if (!$this->recovering()) {
                            $this->persistenceReceive($msg);
                        }
                        $this->state = $msg->getMessage();
                        break;
                    case $msg instanceof Query:
                        $context->respond($this->state);
                        break;
                    case $msg instanceof Messages:
                        $context->respond($this->receiveRecoverMessages);
                        break;
                }
            }

            public function receiveRecover(mixed $message): void
            {
                $this->receiveRecoverMessages[] = get_debug_type($message);
            }
        };
    }

    // snapshot取得を実行してもSenderが上書きされないことを確認
    public function testReceiveRecoverySender(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $props = ActorSystem\Props::fromProducer(fn() => $this->receiveRecoverActorSender(),
                    ActorSystem\Props::withReceiverMiddleware(
                        new EventSourcedBehavior(new InMemoryStateProvider(new InMemoryProvider(1)))
                    ));
                $ref = $system->root()->spawnNamed($props, 'test.actor');
                $system->root()->requestFuture($ref->getRef(), new TestMessage(['message' => 'hello3']), 1);
                $system->root()->requestFuture($ref->getRef(), new TestMessage(['message' => 'hello3']), 1);
                $system->root()->requestFuture($ref->getRef(), new TestMessage(['message' => 'hello3']), 1);
                $system->root()->requestFuture($ref->getRef(), new TestMessage(['message' => 'hello3']), 1);
                $system->root()->requestFuture($ref->getRef(), new TestMessage(['message' => 'hello3']), 1);
                $s = $system->root()->poisonFuture($ref->getRef())?->wait();
                $ref = $system->root()->spawnNamed($props, 'test.actor');
                $r = $system->root()->requestFuture($ref->getRef(), new TestMessage(['message' => 'hello3']), 1);
                $this->assertSame('ok', $r->result()->value());
                $r = $system->root()->requestFuture($ref->getRef(), new TestMessage(['message' => 'hello3']), 1);
                $this->assertSame('ok', $r->result()->value());
            });
        });
    }

    private function receiveRecoverActorSender(): ActorSystem\Message\ActorInterface
    {
        return new class() implements ActorSystem\Message\ActorInterface, PersistentInterface {
            use Mixin;

            private string $state = '';
            /** @var string[] */
            private array $receiveRecoverMessages = [];

            public function receive(ActorSystem\Context\ContextInterface $context): void
            {
                $msg = $context->message();
                switch (true) {
                    case $msg instanceof RequestSnapshot:
                        $this->persistenceSnapshot(new TestSnapshot(['message' => $this->state]));
                        break;
                    case $msg instanceof TestMessage:
                        if (!$this->recovering()) {
                            $this->persistenceReceive($msg);
                        }
                        $context->respond('ok');
                        break;
                    case $msg instanceof Messages:
                        $context->respond($this->receiveRecoverMessages);
                        break;
                }
            }

            public function receiveRecover(mixed $message): void
            {
                $this->receiveRecoverMessages[] = get_debug_type($message);
            }
        };
    }
}
