<?php

declare(strict_types=1);

namespace Test\ActorSystem\Channel;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Channel\TypedChannel;
use PHPUnit\Framework\TestCase;
use Test\EchoRequest;

use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;

class TypedChannelTest extends TestCase
{
    public function testReceiveStringFromTypedChannel(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $c = new TypedChannel(
                    $system,
                    fn(mixed $message): bool => is_string($message)
                );
                $system->root()->send($c->getRef(), "hello");
                $system->root()->send($c->getRef(), "world");

                $this->assertEquals("hello", $c->result());
                $this->assertEquals("world", $c->result());
            });
        });
    }

    public function testReceiveEchoRequestFromTypedChannel(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $c = new TypedChannel(
                    $system,
                    fn(mixed $message): bool => $message instanceof EchoRequest
                );

                $r = $system->root()->spawn(
                    ActorSystem\Props::fromFunction(
                        new ActorSystem\Message\ReceiveFunction(
                            function (ActorSystem\Context\ContextInterface $context) use ($c) {
                                $msg = $context->message();
                                $context->send($c->getRef(), $msg);
                            }
                        )
                    )
                );
                $system->root()->send($r, "hello");
                $system->root()->send($r, new EchoRequest());

                $this->assertInstanceOf(EchoRequest::class, $c->result());
            });
        });
    }
}
