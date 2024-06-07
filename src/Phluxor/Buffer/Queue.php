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
        $this->content->overrideTail(($this->content->getTail() + 1) % $this->content->getMod());
        if ($this->content->getTail() === $this->content->getHead()) {
            $fillFactor = 2;
            $newLen = $this->content->getMod() * $fillFactor;
            $newBuff = array_fill(0, $newLen, null);
            for ($i = 0; $i < $this->content->getMod(); $i++) {
                $buffIndex = ($this->content->getTail() + $i) % $this->content->getMod();
                $newBuff[$i] = $this->content->getBuffer()[$buffIndex];
            }
            $mod = $this->content->getMod();
            $this->content = new RingBuffer(initialSize: $newLen, head: 0);
            $this->content->replaceBuffer($newBuff);
            $this->content->overrideTail($mod);
        }
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

    /**
     * single consumer
     * @return QueueResult
     */
    public function pop(): QueueResult
    {
        if ($this->isEmpty()) {
            return new QueueResult(null, false);
        }

        $this->lock->lock();
        $this->content->overrideHead(($this->content->getHead() + 1) % $this->content->getMod());
        $result = $this->content->getBuffer()[$this->content->getHead()];
        $this->content->add($this->content->getHead(), null);
        $this->len->sub(1);
        $this->lock->unlock();

        return new QueueResult($result, true);
    }

    public function popMany(int $count): QueueResult
    {
        if ($this->isEmpty()) {
            return new QueueResult(null, false);
        }

        $this->lock->lock();
        if ($count >= $this->len->get()) {
            $count = $this->len->get();
        }

        $this->len->sub($count);
        $buffer = [];

        for ($i = 0; $i < $count; $i++) {
            $pos = ($this->content->getHead() + 1 + $i) % $this->content->getMod();
            $buffer[] = $this->content->getBuffer()[$pos];
            $this->content->add($pos, null);
        }

        $this->content->overrideHead(($this->content->getHead() + $count) % $this->content->getMod());
        $this->lock->unlock();
        return new QueueResult($buffer, true);
    }
}
