<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

interface SupervisorInterface
{
    /**
     * @return Ref[]
     */
    public function children(): array;

    public function escalateFailure(mixed $reason, mixed $message): void;

    public function restartChildren(Ref ...$pids): void;

    public function stopChildren(Ref ...$pids): void;

    public function resumeChildren(Ref ...$pids): void;
}
