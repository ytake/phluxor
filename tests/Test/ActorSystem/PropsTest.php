<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use Closure;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Context\ReceiverInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Message\ReceiveFunction;
use Phluxor\ActorSystem\Message\ReceiverFunctionInterface;
use Phluxor\ActorSystem\Props;
use Phluxor\Swoole\Coroutine\WaitGroup;
use PHPUnit\Framework\TestCase;
use Test\VoidActor;

use function Phluxor\Swoole\Coroutine\run;

class PropsTest extends TestCase
{
    public function testPropFromProducer(): void
    {
        $fun = Props::fromFunction(
            new ReceiveFunction(function (ContextInterface $context) {
            }),
            Props::withOnInit(function (ContextInterface $context) {
            })
        );
        $this->assertNotSame($fun, $fun->clone());
    }

    public function testPropFromProducerWithMiddleware(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $isCalled = false;
                $wg = new WaitGroup();
                $wg->add();
                $props = Props::fromProducer(
                    fn() => new VoidActor(),
                    Props::withReceiverMiddleware(
                        $this->mockReceiverMiddleware(
                            function (ContextInterface $context, MessageEnvelope $messageEnvelope) use (&$isCalled, $wg): void {
                                if ($messageEnvelope->getMessage() === 'hello') {
                                    $this->assertSame('hello', $messageEnvelope->getMessage());
                                    $isCalled = true;
                                    $wg->done();
                                }
                            }
                        )
                    )
                );
                $ref = $system->root()->spawn($props);
                $system->root()->send($ref, 'hello');
                $wg->wait();
                $this->assertTrue($isCalled);
            });
        });
    }

    private function mockReceiverMiddleware(Closure|ReceiverFunctionInterface $next): Props\ReceiverMiddlewareInterface
    {
        return new readonly class($next) implements Props\ReceiverMiddlewareInterface {

            public function __construct(
                private Closure|ReceiverFunctionInterface $next
            ) {
            }

            public function __invoke(
                Closure|ReceiverFunctionInterface $next
            ): ReceiverFunctionInterface {
                return new readonly class($this->next) implements ReceiverFunctionInterface {

                    public function __construct(
                        private Closure|ReceiverFunctionInterface $next
                    ) {
                    }
                    public function __invoke(
                        ReceiverInterface|ContextInterface $context,
                        MessageEnvelope $messageEnvelope
                    ): void {
                        $next = $this->next;
                        $next($context, $messageEnvelope);
                    }
                };
            }
        };
    }
}
