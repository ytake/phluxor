<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use PHPUnit\Framework\TestCase;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\ProcessRegistryValue;
use Phluxor\ActorSystem\DeadLetterProcess;

class ProcessRegistryValueTest extends TestCase
{
    public function testUint64ToId()
    {
        go(function () {
            $case = [
                0x0 => '$0',
                0x1 => '$1',
                0xf => '$f',
                0x1041041041041041 => '$11111111111',
            ];
            $registry = new ProcessRegistryValue(new ActorSystem());
            foreach ($case as $key => $value) {
                $this->assertSame($value, $registry->uint64ToId($key));
            }
        });
        \Swoole\Event::wait();
    }

    public function testShouldReturnNextId(): void
    {
        go(function () {
            $registry = new ProcessRegistryValue(new ActorSystem());
            $this->assertSame('$1', $registry->nextId());
            $this->assertSame('$2', $registry->nextId());
        });
        \Swoole\Event::wait();
    }

    public function testShouldReturnLocalDeadLetter(): void
    {
        go(function () {
            $registry = new ProcessRegistryValue(ActorSystem::create());
            $this->assertFalse($registry->getLocal('$1')->isProcess());
            $this->assertInstanceOf(
                DeadLetterProcess::class,
                $registry->getLocal('$1')->getProcess()
            );
        });
        \Swoole\Event::wait();
    }
}
