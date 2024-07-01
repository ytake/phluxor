<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use Phluxor\ActorSystem;
use Phluxor\EventStream\Subscription;
use PHPUnit\Framework\TestCase;
use chan;
use Swoole\Event;

class EventStreamProcessTest extends TestCase
{
    public function testSendsMessageToEventStream(): void
    {
        $actorSystem = ActorSystem::create();
        go(function (ActorSystem $actorSystem) {
            $p = false;
            $chan = new chan(1);
            $subscription = $actorSystem->getEventStream()?->subscribe(function ($message) use ($chan, &$p) {
                $chan->push(true);
                $p = true;
                $this->assertSame('test', $message);
            });
            $this->assertInstanceOf(Subscription::class, $subscription);
            $pid = $actorSystem->newLocalAddress('eventstream');
            $actorSystem->root()->send(
                $pid,
                new ActorSystem\Message\MessageEnvelope(null, 'test', null)
            );
            $actorSystem->getEventStream()?->unsubscribe($subscription);
            $chan->close();
            $this->assertTrue($p);
        }, $actorSystem);
        Event::wait();
    }
}
