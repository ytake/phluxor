<?php

declare(strict_types=1);

namespace Phluxor\Buffer;

use function array_fill;

final class RingBuffer
{
    /** @var array<int, mixed> $buffer */
    private array $buffer;
    private int $mod;

    /**
     * @param int $initialSize
     * @param int $head
     * @param int $tail
     */
    public function __construct(
        int $initialSize,
        private int $head = 0,
        private int $tail = 0
    ) {
        $this->buffer = array_fill(0, $initialSize, null);
        $this->mod = $initialSize;
    }

    /**
     * @return array<int, mixed>
     */
    public function getBuffer(): array
    {
        return $this->buffer;
    }

    /**
     * @param array<int, mixed> $newBuffer
     * @param int $newMod
     * @return void
     */
    public function replaceBuffer(array $newBuffer, int $newMod): void
    {
        $this->buffer = $newBuffer;
        $this->mod = $newMod;
    }

    public function getMod(): int
    {
        return $this->mod;
    }

    public function getHead(): int
    {
        return $this->head;
    }

    public function getTail(): int
    {
        return $this->tail;
    }

    public function add(int $offer, mixed $item): void
    {
        $this->buffer[$offer] = $item;
    }

    public function overrideHead(int $head): void
    {
        $this->head = $head;
    }

    public function overrideTail(int $tail): void
    {
        $this->tail = $tail;
    }
}
