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
}
