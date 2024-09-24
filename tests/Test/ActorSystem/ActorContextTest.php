<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use DateInterval;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\ActorContext;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Props;
use Phluxor\ActorSystem\Ref;
use Phluxor\ActorSystem\Strategy\OneForOneStrategy;
use Phluxor\ActorSystem\Supervision\DefaultDecider;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\WaitGroup;
use Test\NullProducer;
use Test\ProcessTrait;

use function Swoole\Coroutine\run;


class ActorContextTest extends TestCase
{
    use ProcessTrait;

    /**
     * @param ActorSystem $system
     * @param WaitGroup $wg
     * @param int $count
     * @return ActorSystem\Ref|null
     */
    private function receiver(ActorSystem $system, WaitGroup $wg, int &$count): ActorSystem\Ref|null
    {
        return $system->root()->spawn(
            Props::fromFunction(
                new ActorSystem\Message\ReceiveFunction(
                    function (ContextInterface $context) use ($wg, &$count) {
                        if (is_bool($context->message())) {
                            $count++;
                            $wg->done();
                        }
                    }
                )
            )
        );
    }

    public function testSendMessageWithSenderMiddleware(): void
    {
        run(function () {
            go(function () {
                $wg = new WaitGroup();
                $system = ActorSystem::create();
                $mw = new MockSenderMiddleware();
                $props = Props::fromProducer(
                    new NullProducer(),
                    Props::withSupervisor(
                        new OneForOneStrategy(10, new DateInterval('PT10S'), new DefaultDecider())
                    ),
                    Props::withSenderMiddleware($mw)
                );
                $count = 0;
                $context = new ActorContext($system, $props, null);
                // 3 milliseconds
                $timeout = 3;

                $wg->add();
                $context->send($this->receiver($system, $wg, $count), true);
                $wg->wait();
                $this->assertSame(1, $count);

                $wg->add();
                $count = 0;
                $context->request($this->receiver($system, $wg, $count), true);
                $wg->wait();
                $this->assertSame(1, $count);

                $count = 0;
                $wg->add();
                $context->requestFuture($this->receiver($system, $wg, $count), true, $timeout)->wait();
                $wg->wait();

                $this->assertSame(1, $count);
            });
            $this->assertTrue(true);
        });
    }

    public function testActorContextStop(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $spawn = $this->spawnMockProcess($system, 'foo');
                $terminate = 0;
                $watcher = $this->spawnMockProcess(
                    $system,
                    'watcher',
                    function (?Ref $pid, mixed $message) use (&$terminate) {
                        /** @var ActorSystem\ProtoBuf\Terminated $message */
                        $this->assertInstanceOf(ActorSystem\ProtoBuf\Terminated::class, $message);
                        $this->assertSame('foo', $message->getWho()?->getId());
                        $terminate++;
                    }
                );
                $props = Props::fromProducer(
                    new NullProducer(),
                    Props::withSupervisor(
                        new OneForOneStrategy(10, new DateInterval('PT10S'), new DefaultDecider())
                    )
                );
                $context = new ActorContext($system, $props, null);
                $context->setSelf($spawn['ref']);
                $context->invokeSystemMessage(new ActorSystem\ProtoBuf\Stop());
                $context->invokeSystemMessage(
                    new ActorSystem\ProtoBuf\Watch(['watcher' => $watcher['ref']->protobufPid()])
                );
                $this->removeMockProcess($system, $spawn['ref']);
                $this->removeMockProcess($system, $watcher['ref']);
                $this->assertSame(1, $terminate);
            });
        });
    }

    public function testStash(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $spawn = $this->spawnMockProcess($system, 'foo');
                $props = Props::fromProducer(
                    new NullProducer(),
                    Props::withSupervisor(
                        new OneForOneStrategy(10, new DateInterval('PT10S'), new DefaultDecider())
                    )
                );
                $context = new ActorContext($system, $props, null);
                $context->setSelf($spawn['ref']);
                $context->stash();
                $this->assertNotNull($context->ensureExtras()->stash());
                $this->assertSame(1, $context->ensureExtras()->stash()->length());
                $this->assertNull($context->ensureExtras()->stash()->pop());
                $context->ensureExtras()->resetStash();
                $this->assertNull($context->ensureExtras()->stash());
                $this->removeMockProcess($system, $spawn['ref']);
            });
        });
    }

    public function testShouldReceiveMessageAfterRestart(): void
    {
        run(function () {
            go(function () {
                $counter = 0;
                $system = ActorSystem::create();
                $props = Props::fromFunction(
                    new ActorSystem\Message\ReceiveFunction(
                        function (ContextInterface $context) use (&$counter) {
                            $context->stash();
                            $message = $context->message();
                            if ($message === 'hello') {
                                $counter++;
                            }
                        }
                    )
                );
                $ref = $system->root()->spawn($props);
                for ($i = 0; $i < 4; $i++) {
                    $system->root()->send($ref, 'hello');
                }
                $system->root()->send($ref, new ActorSystem\Message\Restart());
                \Swoole\Coroutine::sleep(1);
                $this->assertSame(4, $counter);
            });
        });
    }

    public function testShouldReceiveTimeoutMessage(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $proceed = false;
                $props = Props::fromFunction(
                    new ActorSystem\Message\ReceiveFunction(
                        function (ContextInterface $context) use (&$proceed) {
                            $message = $context->message();
                            switch (true) {
                                case $message === 'hello':
                                    $context->setReceiveTimeout(new DateInterval('PT1S'));
                                    // any process that takes more than 1 second will be terminated
                                    break;
                                case $message instanceof ActorSystem\Message\ReceiveTimeout:
                                    $proceed = true;
                                    break;
                            }
                        }
                    )
                );
                $ref = $system->root()->spawn($props);
                $system->root()->send($ref, 'hello');
                \Swoole\Coroutine::sleep(2);
                $this->assertTrue($proceed);
            });
        });
    }

    public function testShouldNotReceiveTimeoutMessageAfterReset(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $proceed = false;
                $props = Props::fromFunction(
                    new ActorSystem\Message\ReceiveFunction(
                        function (ContextInterface $context) use (&$proceed) {
                            $message = $context->message();
                            switch (true) {
                                case $message === 'hello':
                                    $context->setReceiveTimeout(new DateInterval('PT1S'));
                                    break;
                                case $message === 'reset':
                                    // reset the timeout
                                    $context->setReceiveTimeout(new DateInterval('PT0S'));
                                    break;
                                case $message instanceof ActorSystem\Message\ReceiveTimeout:
                                    $proceed = true;
                                    break;
                            }
                        }
                    )
                );
                $ref = $system->root()->spawn($props);
                $system->root()->send($ref, 'hello');
                $system->root()->send($ref, 'reset');
                \Swoole\Coroutine::sleep(2);
                $this->assertFalse($proceed);
            });
        });
    }

    public function testShouldNotReceiveTimeoutMessageAfterNoInfluence(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $proceed = false;
                $count = 0;
                $wg = new WaitGroup();
                $props = Props::fromFunction(
                    new ActorSystem\Message\ReceiveFunction(
                        function (ContextInterface $context) use (&$proceed, &$count, $wg) {
                            $message = $context->message();
                            switch (true) {
                                case $message === 'hello':
                                    $wg->add();
                                    $context->setReceiveTimeout(new DateInterval('PT1S'));
                                    break;
                                case $message instanceof NoInfluence:
                                    $context->setReceiveTimeout(new DateInterval('PT1S'));
                                    break;
                                case $message instanceof ActorSystem\Message\ReceiveTimeout:
                                    $proceed = true;
                                    $count++;
                                    $wg->done();
                                    break;
                            }
                        }
                    )
                );
                $ref = $system->root()->spawn($props);
                $system->root()->send($ref, 'hello');
                $system->root()->send($ref, 'hell');
                $system->root()->send($ref, new NoInfluence());
                $system->root()->send($ref, 'hell');
                \Swoole\Coroutine::sleep(4);
                $wg->wait();
                $this->assertTrue($proceed);
                $this->assertSame(1, $count);
            });
        });
    }
}
