<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

interface SupervisorInterface
{
    /**
     * @return Pid[]
     */
    public function children(): array;

    public function escalateFailure(mixed $reason, mixed $message): void;

    public function restartChildren(Pid ...$pids): void;

    public function stopChildren(Pid ...$pids): void;

    public function resumeChildren(Pid ...$pids): void;
}
