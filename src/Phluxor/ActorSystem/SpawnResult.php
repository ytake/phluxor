<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Phluxor\ActorSystem\Exception\SpawnErrorException;

readonly class SpawnResult
{
    public function __construct(
        private Pid|null $pid,
        private SpawnErrorException|null $isError
    ) {
    }

    public function getPid(): Pid|null
    {
        return $this->pid;
    }

    public function isError(): SpawnErrorException|null
    {
        return $this->isError;
    }
}
