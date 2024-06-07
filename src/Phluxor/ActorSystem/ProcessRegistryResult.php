<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

readonly class ProcessRegistryResult
{
    public function __construct(
        private ProcessInterface $process,
        private bool $isProcess
    ) {
    }

    public function getProcess(): ProcessInterface
    {
        return $this->process;
    }

    public function isProcess(): bool
    {
        return $this->isProcess;
    }
}
