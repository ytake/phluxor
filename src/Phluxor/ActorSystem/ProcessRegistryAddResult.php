<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

readonly class ProcessRegistryAddResult
{
    public function __construct(
        private Pid $pid,
        private bool $added
    ) {
    }

    public function getPid(): Pid
    {
        return $this->pid;
    }

    public function isAdded(): bool
    {
        return $this->added;
    }

    public function __toString(): string
    {
        return $this->pid->protobufPid()->getId();
    }
}
