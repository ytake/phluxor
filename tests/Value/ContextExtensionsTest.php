<?php

declare(strict_types=1);

namespace Test\Value;

use Phluxor\Value\ContextExtensions;
use Phluxor\Value\ContextExtensionId;
use PHPUnit\Framework\TestCase;

class ContextExtensionsTest extends TestCase
{
    public function testGenerateContextExtensions(): void
    {
        go(function () {
            $extensions = new ContextExtensions();
            $extensions->set(new ContextExtensionId(0));
            $this->assertEquals(1, $extensions->get(new ContextExtensionId(0))->value());
        });
        \Swoole\Event::wait();
    }

    public function testGenerateSizeOveContextExtensions(): void
    {
        go(function () {
            $extensions = new ContextExtensions();
            $extensions->set(new ContextExtensionId(456));
            $this->assertEquals(457, $extensions->get(new ContextExtensionId(456))->value());
        });
        \Swoole\Event::wait();
    }
}
