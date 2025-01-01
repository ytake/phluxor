<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

readonly class ConcurrentMapResult
{
    public function __construct(
        public mixed $value,
        public bool $exists
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
