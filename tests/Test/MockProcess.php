<?php

declare(strict_types=1);

namespace Test;

use Closure;
use Phluxor\ActorSystem\ProcessInterface;
use Phluxor\ActorSystem\Ref;

readonly class MockProcess implements ProcessInterface
{
    /**
     * @param Closure(?Ref, mixed): void|null  $systemMessageFunc
     * @param Closure(?Ref, mixed): void|null $userMessageFunc
     */
    public function __construct(
        private Closure|null $systemMessageFunc = null,
        private Closure|null $userMessageFunc = null
    ) {
    }

    public function sendUserMessage(?Ref $pid, mixed $message): void
    {
        if ($this->userMessageFunc != null) {
            $f = $this->userMessageFunc;
            $f($pid, $message);
        }
    }

    public function sendSystemMessage(Ref $pid, mixed $message): void
    {
        if ($this->systemMessageFunc != null) {
            $f = $this->systemMessageFunc;
            $f($pid, $message);
        }
    }

    public function stop(Ref $pid): void
    {
        // TODO: Implement stop() method.
    }
}
