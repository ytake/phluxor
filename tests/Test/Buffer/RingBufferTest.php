<?php

declare(strict_types=1);

namespace Test\Buffer;

use Phluxor\Buffer\RingBuffer;
use PHPUnit\Framework\TestCase;

class RingBufferTest extends TestCase
{
    /**
     * Tests if the buffer is replaced correctly with a new buffer and mod value.
     */
    public function testReplaceBufferUpdatesBufferAndMod(): void
    {
        $initialSize = 4;
        $ringBuffer = new RingBuffer($initialSize);

        $newBuffer = [1, 2, 3, 4, 5];
        $newMod = 5;
        $ringBuffer->replaceBuffer($newBuffer, $newMod);

        $this->assertSame($newBuffer, $ringBuffer->getBuffer(), "Buffer should be updated to the new buffer.");
        $this->assertSame($newMod, $ringBuffer->getMod(), "Mod value should be updated to the new mod.");
    }

    /**
     * Tests if an empty buffer can be replaced correctly.
     */
    public function testReplaceBufferWithEmptyBuffer(): void
    {
        $ringBuffer = new RingBuffer(3);

        $newBuffer = [];
        $newMod = 0;
        $ringBuffer->replaceBuffer($newBuffer, $newMod);

        $this->assertSame(
            $newBuffer,
            $ringBuffer->getBuffer(),
            "Buffer should allow replacement with an empty buffer."
        );
        $this->assertSame($newMod, $ringBuffer->getMod(), "Mod value should be updated correctly for an empty buffer.");
    }

    /**
     * Tests if replacing with a buffer of a different size does not affect head or tail values.
     */
    public function testReplaceBufferDoesNotChangeHeadAndTail(): void
    {
        $ringBuffer = new RingBuffer(4, head: 1, tail: 2);

        $newBuffer = [10, 20, 30];
        $newMod = 3;
        $ringBuffer->replaceBuffer($newBuffer, $newMod);

        $this->assertSame($newBuffer, $ringBuffer->getBuffer(), "Buffer should be replaced correctly.");
        $this->assertSame($newMod, $ringBuffer->getMod(), "Mod value should be replaced correctly.");
        $this->assertSame(1, $ringBuffer->getHead(), "Head value should remain unchanged.");
        $this->assertSame(2, $ringBuffer->getTail(), "Tail value should remain unchanged.");
    }

    /**
     * Tests if replacing the buffer with a null-like buffer throws no errors.
     */
    public function testReplaceBufferWithNullValues(): void
    {
        $ringBuffer = new RingBuffer(5);

        $newBuffer = [null, null, null];
        $newMod = 3;
        $ringBuffer->replaceBuffer($newBuffer, $newMod);

        $this->assertSame(
            $newBuffer,
            $ringBuffer->getBuffer(),
            "Buffer should be replaced correctly with null-like values."
        );
        $this->assertSame($newMod, $ringBuffer->getMod(), "Mod value should be updated correctly.");
    }
}