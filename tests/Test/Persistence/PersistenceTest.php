<?php

declare(strict_types=1);

namespace Test\Persistence;

use Phluxor\ActorSystem;
use Phluxor\Persistence\EventSourcedReceiver;
use Phluxor\Persistence\InMemoryProvider;
use PHPUnit\Framework\TestCase;
use Test\Persistence\ProtoBuf\TestMessage;

use function Phluxor\Swoole\Coroutine\run;

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
                        new EventSourcedReceiver(new InMemoryStateProvider(new InMemoryProvider(2)))
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
}
