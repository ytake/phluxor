<?php

declare(strict_types=1);

namespace Phluxor\Buffer;

use Phluxor\ActorSystem\QueueResult;
use Swoole\Atomic\Long;
use Swoole\Lock;

use function array_fill;

class Queue
{
    private RingBuffer $content;

    public function __construct(
        int $initialSize,
        private readonly Lock $lock = new Lock(),
        private readonly Long $len = new Long(0)
    ) {
        $this->content = new RingBuffer($initialSize);
    }

    public function push(mixed $item): void
    {
        $this->lock->lock();
        $newTail = ($this->content->getTail() + 1) % $this->content->getMod();
        if ($newTail === $this->content->getHead()) {
            $this->grow();
            $newTail = ($this->content->getTail() + 1) % $this->content->getMod();
        }
        $this->content->overrideTail($newTail);
        $this->content->add($this->content->getTail(), $item);

        $this->len->add(1);
        $this->lock->unlock();
    }

    public function length(): int
    {
        return $this->len->get();
    }

    public function isEmpty(): bool
    {
        return $this->length() === 0;
    }

    public function pop(): QueueResult
    {
        if ($this->isEmpty()) {
            return new QueueResult(null, false);
        }

        $this->lock->lock();
        $newHead = ($this->content->getHead() + 1) % $this->content->getMod();
        $result = $this->content->getBuffer()[$newHead];
        $this->content->add($newHead, null);

        $this->content->overrideHead($newHead);

        $this->len->sub(1);
        $this->maybeShrink();
        $this->lock->unlock();

        return new QueueResult($result, true);
    }

    public function popMany(int $count): QueueResult
    {
        if ($this->isEmpty()) {
            return new QueueResult(null, false);
        }
        $this->lock->lock();
        $avail = $this->len->get();
        if ($count >= $avail) {
            $count = $avail;
        }

        $buffer = [];
        for ($i = 0; $i < $count; $i++) {
            $pos = ($this->content->getHead() + 1 + $i) % $this->content->getMod();
            $buffer[] = $this->content->getBuffer()[$pos];
            // nullクリア
            $this->content->add($pos, null);
        }
        $this->content->overrideHead(
            ($this->content->getHead() + $count) % $this->content->getMod()
        );

        $this->len->sub($count);
        $this->maybeShrink();

        $this->lock->unlock();

        return new QueueResult($buffer, true);
    }

    /**
     * grow the ring buffer
     * @return void
     */
    private function grow(): void
    {
        $oldMod = $this->content->getMod();
        $newMod = $oldMod * 2;
        $newBuffer = array_fill(0, $newMod, null);

        $currentSize = $this->len->get();
        for ($i = 0; $i < $currentSize; $i++) {
            $oldPos = ($this->content->getHead() + 1 + $i) % $oldMod;
            $newBuffer[$i + 1] = $this->content->getBuffer()[$oldPos];
        }
        $this->content->replaceBuffer($newBuffer, $newMod);
        $this->content->overrideHead(0);
        $this->content->overrideTail($currentSize);
    }

    /**
     * shrink the ring buffer if the usage ratio is less than 25%
     * @return void
     */
    private function maybeShrink(): void
    {
        $oldMod = $this->content->getMod();
        if ($oldMod <= 4) {
            return;
        }

        $currentSize = $this->len->get();
        $usageRatio = $currentSize / $oldMod;
        if ($usageRatio < 0.25) {
            $newMod = (int)($oldMod / 2);
            // 最小限 currentSize は入る必要があるのでそれ以下にはしない
            if ($newMod < $currentSize + 1) {
                return;
            }

            $newBuffer = array_fill(0, $newMod, null);

            // headの次から順に currentSize 分だけコピー
            for ($i = 0; $i < $currentSize; $i++) {
                $oldPos = ($this->content->getHead() + 1 + $i) % $oldMod;
                $newBuffer[$i + 1] = $this->content->getBuffer()[$oldPos];
            }
            $this->content->replaceBuffer($newBuffer, $newMod);
            $this->content->overrideHead(0);
            $this->content->overrideTail($currentSize);
        }
    }
}
