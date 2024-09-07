<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use Phluxor\ActorSystem;
use PHPUnit\Framework\TestCase;

use function Swoole\Coroutine\run;

class RootContextTest extends TestCase
{
    public function testRootContext(): void
    {
        run(function () {
            go(function () {
                $system = ActorSystem::create();
                $this->assertInstanceOf(ActorSystem::class, $system->root()->actorSystem());
                $this->assertInstanceOf(ActorSystem\RootContext::class, $system->root());
                $this->assertSame('', (string) $system->root()->self());
            });
        });
    }
}
