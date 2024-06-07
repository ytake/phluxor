<?php

declare(strict_types=1);

namespace Test\Buffer;

use Phluxor\Buffer\Queue;
use PHPUnit\Framework\TestCase;

class QueueTest extends TestCase
{
    public function testPushPop(): void
    {
        go(function () {
            $queue = new Queue(10);
            $queue->push("hello");
            $result = $queue->pop();
            $this->assertEquals("hello", $result->value());
            $this->assertTrue($queue->isEmpty());
        });
        \Swoole\Event::wait();
    }

    public function testPushPopRepeated(): void
    {
        go(function () {
            $queue = new Queue(10);
            for ($i = 0; $i < 100; $i++) {
                $queue->push("hello");
                $result = $queue->pop();
                $this->assertEquals("hello", $result->value());
                $this->assertTrue($queue->isEmpty());
            }
        });
        \Swoole\Event::wait();
    }

    public function testPushPopMany(): void
    {
        go(function () {
            $queue = new Queue(10);
            for ($i = 0; $i < 1000; $i++) {
                $item = sprintf("hello%d", $i);
                $queue->push($item);
                $this->assertEquals($item, $queue->pop()->value());
            }
            $this->assertTrue($queue->isEmpty());
        });
        \Swoole\Event::wait();
    }

    public function testPushPopMany2(): void
    {
        go(function () {
            $queue = new Queue(10);
            for ($i = 0; $i < 1000; $i++) {
                $queue->push(sprintf("hello%d", $i));
            }
            for ($i = 0; $i < 1000; $i++) {
                $item = sprintf("hello%d", $i);
                $this->assertEquals($item, $queue->pop()->value());
            }
            $this->assertTrue($queue->isEmpty());
        });
        \Swoole\Event::wait();
    }
}
