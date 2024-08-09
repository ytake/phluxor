<?php

declare(strict_types=1);

namespace Test\Value;

use Phluxor\Value\ContextExtensions;
use Phluxor\Value\ContextExtensionId;
use Phluxor\Value\ExtensionInterface;
use PHPUnit\Framework\TestCase;

use function Phluxor\Swoole\Coroutine\run;

class ContextExtensionsTest extends TestCase
{
    public function testGenerateContextExtensions(): void
    {
        run(function () {
            go(function () {
                $id = new class() implements ExtensionInterface {
                    public function extensionID(): ContextExtensionId
                    {
                        return new ContextExtensionId(0);
                    }
                };
                $extensions = new ContextExtensions();
                $extensions->set($id);
                $this->assertSame(1, $extensions->get($id->extensionID())->extensionID()->value());
            });
        });
    }

    public function testGenerateSizeOveContextExtensions(): void
    {
        run(function () {
            go(function () {
                $id = new class() implements ExtensionInterface {
                    public function extensionID(): ContextExtensionId
                    {
                        return new ContextExtensionId(456);
                    }
                };
                $extensions = new ContextExtensions();
                $extensions->set($id);
                $this->assertSame(457, $extensions->get($id->extensionID())->extensionID()->value());
            });
        });
    }
}
