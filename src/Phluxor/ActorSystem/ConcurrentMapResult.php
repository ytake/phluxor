<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

readonly class ConcurrentMapResult
{
    public function __construct(
        private mixed $value,
        private bool $exists
    ) {
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function exists(): bool
    {
        return $this->exists;
    }
}
