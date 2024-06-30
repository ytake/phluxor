<?php

declare(strict_types=1);

namespace Test\ActorSystem\Mailbox;

use Phluxor\ActorSystem\Mailbox\BoundedMailboxQueue;
use PHPUnit\Framework\TestCase;

class BoundedMailboxQueueTest extends TestCase
{
    public function testPushAndPop(): void
    {
        $queue = new BoundedMailboxQueue(2, false);
        $queue->push(1);
        $queue->push(2);

        $this->assertEquals(1, $queue->pop()->value());
        $this->assertEquals(2, $queue->pop()->value());

        $queue->push(1);
        $queue->push(2);
        $queue->push(3);

        $this->assertEquals(1, $queue->pop()->value());
        $this->assertEquals(2, $queue->pop()->value());
        $this->assertEquals(3, $queue->pop()->value());
    }

    public function testShouldDropOldMessagesWhenQueueIsFull(): void
    {
        $queue = new BoundedMailboxQueue(2, true);
        $queue->push(1);
        $queue->push(2);
        $queue->push(3);

        $this->assertEquals(2, $queue->pop()->value());
        $this->assertEquals(3, $queue->pop()->value());
    }
}
