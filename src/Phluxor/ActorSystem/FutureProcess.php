<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Phluxor\ActorSystem\Exception\FutureTimeoutException;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\ProtoBuf\DeadLetterResponse;

readonly class FutureProcess implements ProcessInterface
{
    /**
     * @param Future $future
     */
    public function __construct(
        private Future $future
    ) {
    }

    /**
     * sendUserMessage sends a message asynchronously to the given PID
     * @param Ref|null $pid
     * @param mixed $message
     * @return void
     */
    public function sendUserMessage(?Ref $pid, mixed $message): void
    {
        $msg = MessageEnvelope::unwrapEnvelope($message);
        $res = $msg['message'];
        if ($res instanceof DeadLetterResponse) {
            $this->future->setResult(null);
            $this->future->setError(
                new FutureTimeoutException("future: dead letter")
            );
        } else {
            $this->future->setResult($res);
        }
        if ($pid != null) {
            $this->stop($pid);
        }
    }

    public function sendSystemMessage(Ref $pid, mixed $message): void
    {
        $this->future->setResult($message);
        $this->stop($pid);
    }

    public function stop(Ref $pid): void
    {
        $this->future->stop($pid);
    }

    public function getFuture(): Future
    {
        return $this->future;
    }
}
