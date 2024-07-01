<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\ActorContext;
use Phluxor\ActorSystem\Behavior;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Message\ReceiveFunction;
use Phluxor\ActorSystem\Props;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Test\BehaviorMessage;
use Test\EchoRequest;
use Test\EchoSetBehaviorActor;
use Test\NullProducer;
use Test\PopBehaviorMessage;

use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;

class BehaviorTest extends TestCase
{
    public function testBehaviorLength(): void
    {
        $behavior = new Behavior();
        $reflection = new ReflectionClass($behavior);
        $property = $reflection->getProperty('behaviors');
        $this->assertCount(0, $property->getValue($behavior));
    }

    public function testBehaviorPush(): void
    {
        $behavior = new Behavior();
        $reflection = new ReflectionClass($behavior);
        $reflection->getMethod('push')
            ->invoke(
                $behavior,
                new ReceiveFunction(
                    fn(ContextInterface $context) => null
                )
            );
        $reflection->getMethod('push')
            ->invoke(
                $behavior,
                new ReceiveFunction(
                    fn(ContextInterface $context) => null
                )
            );
        $property = $reflection->getProperty('behaviors');
        $this->assertCount(2, $property->getValue($behavior));
    }

    public function testBehaviorClear(): void
    {
        $behavior = new Behavior();
        $reflection = new ReflectionClass($behavior);
        $reflection->getMethod('push')
            ->invoke(
                $behavior,
                new ReceiveFunction(
                    fn(ContextInterface $context) => null
                )
            );
        $reflection->getMethod('push')
            ->invoke(
                $behavior,
                new ReceiveFunction(
                    fn(ContextInterface $context) => null
                )
            );
        $property = $reflection->getProperty('behaviors');
        $this->assertCount(2, $property->getValue($behavior));
        $reflection->getMethod('clear')
            ->invoke($behavior);
        $this->assertCount(0, $property->getValue($behavior));
    }

    public function testBehaviorPeeks(): void
    {
        $behavior = new Behavior();
        $reflection = new ReflectionClass($behavior);
        $c1 = new ReceiveFunction(
            function (ContextInterface $context) {
                echo 1;
            }
        );
        $c2 = new ReceiveFunction(
            function (ContextInterface $context) {
                echo 2;
            }
        );
        $reflection->getMethod('push')
            ->invoke($behavior, $c1);
        $reflection->getMethod('push')
            ->invoke($behavior, $c2);
        $property = $reflection->getProperty('behaviors');
        $this->assertCount(2, $property->getValue($behavior));
        $system = ActorSystem::create();
        $context = new ActorContext($system, Props::fromProducer(new NullProducer()), null);
        $v = $reflection->getMethod('peek')
            ->invoke($behavior);
        $v->receive($context);
        $this->expectOutputString('2');
    }

    public function testBehaviorStackPopExpectedOrder(): void
    {
        $behavior = new Behavior();
        $reflection = new ReflectionClass($behavior);
        $c1 = new ReceiveFunction(
            function (ContextInterface $context) {
                echo 1;
            }
        );
        $c2 = new ReceiveFunction(
            function (ContextInterface $context) {
                echo 2;
            }
        );
        $reflection->getMethod('push')
            ->invoke($behavior, $c1);
        $reflection->getMethod('push')
            ->invoke($behavior, $c2);
        $property = $reflection->getProperty('behaviors');
        $this->assertCount(2, $property->getValue($behavior));
        $system = ActorSystem::create();
        $context = new ActorContext($system, Props::fromProducer(new NullProducer()), null);
        $expected = [2, 1];
        foreach ($expected as $e) {
            $v = $reflection->getMethod('pop')
                ->invoke($behavior);
            $v->receive($context);
            $this->expectOutputString(join('', $expected));
        }
    }

    public function testActorCanSetBehavior(): void
    {
        run(function () {
            $system = ActorSystem::create();
            go(function (ActorSystem $system) {
                $pid = $system->root()->spawn(
                    Props::fromProducer(
                        fn() => new EchoSetBehaviorActor()
                    )
                );
                $system->root()->send($pid, new BehaviorMessage());
                $future = $system->root()->requestFuture($pid, new EchoRequest(), 1);
                $this->assertNull($future->result()->error());
                $system->root()->stop($pid);
            }, $system);
        });
    }

    public function testActorCanPopBehavior(): void
    {
        run(function () {
            $system = ActorSystem::create();
            go(function (ActorSystem $system) {
                $pid = $system->root()->spawn(
                    Props::fromProducer(
                        fn() => new EchoSetBehaviorActor()
                    )
                );
                $system->root()->send($pid, new BehaviorMessage());
                $system->root()->send($pid, new PopBehaviorMessage());
                $future = $system->root()->requestFuture($pid, new EchoRequest(), 1);
                $this->assertNull($future->result()->error());
                $system->root()->stop($pid);
            }, $system);
        });
    }
}
