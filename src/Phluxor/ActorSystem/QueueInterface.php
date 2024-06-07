<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

interface QueueInterface
{
    public function push(mixed $val): void;

    public function pop(): QueueResult;
}
