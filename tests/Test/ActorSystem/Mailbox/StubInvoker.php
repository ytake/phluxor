<?php

declare(strict_types=1);

namespace Test\ActorSystem\Mailbox;

use Closure;
use Phluxor\ActorSystem\Mailbox\MessageInvokerInterface;
use Swoole\Coroutine\WaitGroup;

class StubInvoker implements MessageInvokerInterface
{
    private Closure|null $handler;

    public function __construct(
        private int $count,
        private readonly int $max,
        private readonly WaitGroup $waitGroup
    ) {
    }

    public function invokeSystemMessage(mixed $message): void
    {
        $this->count++;
        if ($this->count == $this->max) {
            $this->waitGroup->done();
        }
        if ($this->count > $this->max) {
            echo "unexpected data\n";
        }
    }

    public function invokeUserMessage(mixed $message): void
    {
        $this->count++;
        if ($this->handler !== null) {
            $handler = $this->handler;
            $handler($message);
        }
        if ($this->count == $this->max) {
            $this->waitGroup->done();
        }
        if ($this->count > $this->max) {
            echo "unexpected data\n";
        }
    }

    public function escalateFailure(mixed $reason, mixed $message): void
    {
    }

    public function withUserMessageReceiveHandler(Closure $handler): MessageInvokerInterface
    {
        $this->handler = $handler;
        return $this;
    }
}
