<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use Phluxor\ActorSystem;
use PHPUnit\Framework\TestCase;

class RootContextTest extends TestCase
{
    public function testRootContext(): void
    {
        $system = ActorSystem::create();
        go(function (ActorSystem $system) {
            $this->assertInstanceOf(ActorSystem::class, $system->root()->actorSystem());
            $this->assertInstanceOf(ActorSystem\RootContext::class, $system->root());
            $this->assertSame('', (string) $system->root()->self());
        }, $system);
        \Swoole\Event::wait();
    }
}
