<?php

declare(strict_types=1);

namespace Test\ActorSystem\Mailbox;

use Phluxor\ActorSystem\Mailbox\BatchingMailbox;
use Phluxor\ActorSystem\Mailbox\UnboundedMailboxQueue;
use Phluxor\ActorSystem\Message\MessageBatch;
use Phluxor\Buffer\Queue as RingBufferQueue;
use Phluxor\Mspc\Queue as MspcQueue;
use Phluxor\ActorSystem\Dispatcher\CoroutineDispatcher;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine;

class BatchingMailboxTest extends TestCase
{
    private function batchingMailbox(): BatchingMailbox
    {
        return new BatchingMailbox(
            new UnboundedMailboxQueue(new RingBufferQueue(10)),
            new UnboundedMailboxQueue(new RingBufferQueue(10)),
            100,
            []
        );
    }

    public function testUnboundedLockFreeMailboxUserMessageConsistency(): void
    {
        Coroutine\run(function () {
            go(function () {
                $mspc = new MspcQueue();
                $max = 100;
                $c = 100;
                $wg = new Coroutine\WaitGroup();
                $wg->add();
                $q = $this->batchingMailbox();
                $counter = 0;
                $invoker = new StubInvoker(0, $max, $wg);
                $invoker->withUserMessageReceiveHandler(function (mixed $message) use (&$counter) {
                    $this->assertInstanceOf(MessageBatch::class, $message);
                    $counter++;
                });
                $q->registerHandlers(
                    $invoker,
                    new CoroutineDispatcher(300)
                );
                for ($j = 0; $j < $c; $j++) {
                    $cmax = $max / $c;
                    go(function ($q, $cmax) {
                        if (rand(0, 10) === 0) {
                            Coroutine::sleep(rand(1, 2));
                        }
                        for ($i = 0; $i < $cmax; $i++) {
                            $q->postUserMessage($i);
                        }
                    }, $q, $cmax);
                }
                $wg->wait();
                $this->assertSame($max, $counter);
                $this->assertTrue($mspc->isEmpty());
            });
        });
    }

    public function testUnboundedLockFreeMailboxSystemMessageConsistency(): void
    {
        Coroutine\run(function () {
            go(function () {
                $max = 1000;
                $c = 100;
                $wg = new Coroutine\WaitGroup();
                $wg->add();
                $q = $this->batchingMailbox();
                $invoker = new StubInvoker(0, $max, $wg);
                $q->registerHandlers(
                    $invoker,
                    new CoroutineDispatcher(300)
                );
                for ($j = 0; $j < $c; $j++) {
                    $cmax = $max / $c;
                    go(function ($q, $cmax) {
                        if (rand(0, 10) === 0) {
                            Coroutine::sleep(rand(1, 2));
                        }
                        for ($i = 0; $i < $cmax; $i++) {
                            $q->postSystemMessage($i);
                        }
                    }, $q, $cmax);
                }
                $wg->wait();
                $this->assertSame(0, $q->userMessageCount());
                $this->assertSame(0, $q->systemMessageCount());
            });
        });
    }
}
