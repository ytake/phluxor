<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Ref;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine;
use Test\ProcessTrait;

use function go;
use function Swoole\Coroutine\run;

class FutureTest extends TestCase
{
    use ProcessTrait;

    public function testWaitWithTimeout(): void
    {
        run(function () {
            go(function () {
                $f = ActorSystem\Future::create(ActorSystem::create(), 2);
                $pid = $f->pid();
                $this->assertNotNull($pid, "Future should have a PID.");
                $f->stop($pid);
            });
        });
    }

    public function testFuturePipeToMessage(): void
    {
        run(function () {
            go(function () {
                $count = 0;
                $system = ActorSystem::create();
                $a1 = $this->spawnMockProcess(
                    $system,
                    'a1',
                    null,
                    function (?Ref $pid, mixed $message) use (&$count) {
                        $this->assertSame('hello', $message);
                        $count++;
                    }
                );
                $a2 = $this->spawnMockProcess(
                    $system,
                    'a2',
                    null,
                    function (?Ref $pid, mixed $message) use (&$count) {
                        $this->assertSame('hello', $message);
                        $count++;
                    }
                );
                $a3 = $this->spawnMockProcess(
                    $system,
                    'a3',
                    null,
                    function (?Ref $pid, mixed $message) use (&$count) {
                        $this->assertSame('hello', $message);
                        $count++;
                    }
                );
                $future = ActorSystem\Future::create($system, 1);
                $future->pipeTo($a1['ref']);
                $future->pipeTo($a2['ref']);
                $future->pipeTo($a3['ref']);
                $result = $system->getProcessRegistry()->get($future->pid());
                $this->assertInstanceOf(ActorSystem\FutureProcess::class, $result->getProcess());
                $result->getProcess()->sendUserMessage($future->pid(), 'hello');
                $this->assertSame(3, $count);
                $this->removeMockProcess($system, $a1['ref']);
                $this->removeMockProcess($system, $a2['ref']);
                $this->removeMockProcess($system, $a3['ref']);
                $this->assertCount(0, $future->pipes());
            });
        });
    }

    public function testFuturePipeToTimeoutSendException(): void
    {
        run(function () {
            go(function () {
                $count = 0;
                $system = ActorSystem::create();
                $a1 = $this->spawnMockProcess(
                    $system,
                    'a1',
                    null,
                    function (?Ref $pid, mixed $message) use (&$count) {
                        $this->assertInstanceOf(
                            ActorSystem\Exception\FutureTimeoutException::class,
                            $message
                        );
                        $count++;
                    }
                );
                $a2 = $this->spawnMockProcess(
                    $system,
                    'a2',
                    null,
                    function (?Ref $pid, mixed $message) use (&$count) {
                        $this->assertInstanceOf(
                            ActorSystem\Exception\FutureTimeoutException::class,
                            $message
                        );
                        $count++;
                    }
                );
                $a3 = $this->spawnMockProcess(
                    $system,
                    'a3',
                    null,
                    function (?Ref $pid, mixed $message) use (&$count) {
                        $this->assertInstanceOf(
                            ActorSystem\Exception\FutureTimeoutException::class,
                            $message
                        );
                        $count++;
                    }
                );
                $future = ActorSystem\Future::create($system, 1);
                $future->pipeTo($a1['ref']);
                $future->pipeTo($a2['ref']);
                $future->pipeTo($a3['ref']);
                $result = $system->getProcessRegistry()->get($future->pid());
                $this->assertInstanceOf(ActorSystem\FutureProcess::class, $result->getProcess());
                Coroutine::sleep(1);
                $this->removeMockProcess($system, $a1['ref']);
                $this->removeMockProcess($system, $a2['ref']);
                $this->removeMockProcess($system, $a3['ref']);
                $this->assertCount(0, $future->pipes());
            });
        });
    }

    public function testFutureCreateTimeoutNoRace(): void
    {
        run(fn: function () {
            $system = ActorSystem::create();
            go(function (ActorSystem $system) {
                $future = ActorSystem\Future::create($system, 1);
                $root = $system->root();
                $a = $root->spawn(
                    ActorSystem\Props::fromFunction(
                        new ActorSystem\Message\ReceiveFunction(
                            function (ActorSystem\Context\ContextInterface $context) use ($future) {
                                if ($context->message() instanceof ActorSystem\Message\Started) {
                                    $context->send($future->pid(), 'echo');
                                }
                            }
                        )
                    )
                );
                $this->assertNull($root->stopFuture($a)?->wait());
                $r = $future->result();
                $this->assertNull($r->error());
                $this->assertSame('echo', $r->value());
            }, $system);
        });
    }

    public function testFutureResultDeadLetterResponse(): void
    {
        run(function () {
            $system = ActorSystem::create();
            go(function (ActorSystem $system) {
                $future = ActorSystem\Future::create($system, 1);
                $root = $system->root();
                $root->send($future->pid(), new ActorSystem\ProtoBuf\DeadLetterResponse());
                $r = $future->result();
                $this->assertInstanceOf(
                    ActorSystem\Exception\FutureTimeoutException::class,
                    $r->error()
                );
                $this->assertNull($r->value());
            }, $system);
        });
    }

    public function testFutureResultTimeout(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $future = ActorSystem\Future::create($system, 1);
                $r = $future->result();
                $this->assertInstanceOf(
                    ActorSystem\Exception\FutureTimeoutException::class,
                    $r->error()
                );
                $this->assertNull($r->value());
            });
        });
    }

    public function testFutureResultSuccess(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $future = ActorSystem\Future::create($system, 1);
                $system->root()->send($future->pid(), 'echo');
                $r = $future->result();
                $this->assertNotInstanceOf(
                    ActorSystem\Exception\FutureTimeoutException::class,
                    $r->error()
                );
                $this->assertNotNull($r->value());
            });
        });
    }
}
