<?php

declare(strict_types=1);

namespace Test\ActorSystem\Mailbox;

use Swoole\Coroutine\WaitGroup;
use Phluxor\ActorSystem\QueueResult;
use Phluxor\Mspc\Queue as MspcQueue;
use Phluxor\ActorSystem\Dispatcher\CoroutineDispatcher;
use Phluxor\ActorSystem\Mailbox\BoundedMailboxQueue;
use Phluxor\ActorSystem\Mailbox\UnboundedLochFree;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine;

use function Swoole\Coroutine\run;

class DefaultMailboxTest extends TestCase
{
    public function testUnboundedLockFreeMailboxUserMessageConsistency(): void
    {
        run(function () {
            go(function () {
                $mspc = new MspcQueue();
                $max = 1000;
                $c = 100;
                $wg = new WaitGroup();
                $wg->add();
                $p = new UnboundedLochFree($mspc);
                $q = $p();
                $invoker = new StubInvoker(0, $max, $wg);
                $invoker->withUserMessageReceiveHandler(function (mixed $message) {
                    $this->assertInstanceOf(QueueResult::class, $message);
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
                $this->assertTrue($mspc->isEmpty());
            });
        });
    }

    public function testUnboundedLockFreeMailboxSystemMessageConsistency(): void
    {
        run(function () {
            go(function () {
                $mspc = new MspcQueue();
                $max = 1000;
                $c = 100;
                $wg = new WaitGroup();
                $wg->add();
                $p = new UnboundedLochFree($mspc);
                $q = $p();
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
                $this->assertTrue($mspc->isEmpty());
            });
        });
    }

    public function testBoundedMailbox(): void
    {
        run(function () {
            $mailbox = new BoundedMailboxQueue(3, false);
            $mailbox->push(1);
            $mailbox->push(2);
            $mailbox->push(3);
            $mailbox->push(4);
            $this->assertEquals(1, $mailbox->pop()->value());
        });
    }

    public function testBoundedDroppingMailbox(): void
    {
        run(function () {
            $mailbox = new BoundedMailboxQueue(3, true);
            $mailbox->push(1);
            $mailbox->push(2);
            $mailbox->push(3);
            $mailbox->push(4);
            $this->assertEquals(2, $mailbox->pop()->value());
        });
    }
}
