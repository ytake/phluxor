<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Mailbox;

use Phluxor\ActorSystem\QueueInterface;
use Phluxor\ActorSystem\QueueResult;
use Phluxor\Buffer\Queue;

class BoundedMailboxQueue implements QueueInterface
{
    private Queue $queue;
    private int $size;
    private bool $drop;

    public function __construct(int $size, bool $drop)
    {
        $this->queue = new Queue($size);
        $this->size = $size;
        $this->drop = $drop;
    }

    /**
     * @param mixed $val
     * @return void
     */
    public function push(mixed $val): void
    {
        if ($this->drop) {
            if ($this->queue->length() > 0 && $this->size == $this->queue->length()) {
                $this->queue->pop();
            }
        }
        $this->queue->push($val);
    }

    public function pop(): QueueResult
    {
        if ($this->queue->length() === 0) {
            return new QueueResult(null, false);
        }
        return $this->queue->pop();
    }
}
