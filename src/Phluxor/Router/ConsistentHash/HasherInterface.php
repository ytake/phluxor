<?php

declare(strict_types=1);

namespace Phluxor\Router\ConsistentHash;

interface HasherInterface
{
    public function hash(): string;
}
