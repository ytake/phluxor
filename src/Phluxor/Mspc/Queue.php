<?php

declare(strict_types=1);

namespace Phluxor\Mspc;

use Phluxor\ActorSystem\QueueInterface;
use Phluxor\ActorSystem\QueueResult;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

class Queue implements QueueInterface
{
    /**
     * @param Channel $channel 暫定的にバッファサイズを1024
     * @param Node $tail
     */
    public function __construct(
        private readonly Channel $channel = new Channel(1024),
        private Node $tail = new Node()
    ) {
    }

    public function push(mixed $val): void
    {
        while (!$this->channel->push(new Node($val), 0.001)) {
            // バックプレッシャー管理のために待機
            Coroutine::sleep(0.001);
        }
    }

    public function pop(): QueueResult
    {
        if ($this->channel->errCode === 0) {
            if ($this->tail->getNext() === null) {
                $result = $this->channel->pop(0.001);
                if (!$result instanceof Node) {
                    return new QueueResult(null, false);
                }
                $this->tail->replaceNext($result);
            }

            if ($this->tail->getNext() !== null) {
                $result = $this->tail->getNext()->value();
                $this->tail = $this->tail->getNext();
                $this->tail->replaceValue(null);
                return new QueueResult($result, true);
            }
        }
        return new QueueResult(null, false);
    }

    public function isEmpty(): bool
    {
        return $this->tail->getNext() === null && $this->channel->isEmpty();
    }
}
