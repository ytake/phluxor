<?php

declare(strict_types=1);

namespace Test;

use Closure;
use Phluxor\ActorSystem\Pid;
use Phluxor\ActorSystem\ProcessInterface;

readonly class MockProcess implements ProcessInterface
{
    /**
     * @param Closure(?Pid, mixed): void|null  $systemMessageFunc
     * @param Closure(?Pid, mixed): void|null $userMessageFunc
     */
    public function __construct(
        private Closure|null $systemMessageFunc = null,
        private Closure|null $userMessageFunc = null
    ) {
    }

    public function sendUserMessage(?Pid $pid, mixed $message): void
    {
        if ($this->userMessageFunc != null) {
            $f = $this->userMessageFunc;
            $f($pid, $message);
        }
    }

    public function sendSystemMessage(Pid $pid, mixed $message): void
    {
        if ($this->systemMessageFunc != null) {
            $f = $this->systemMessageFunc;
            $f($pid, $message);
        }
    }

    public function stop(Pid $pid): void
    {
        // TODO: Implement stop() method.
    }
}
