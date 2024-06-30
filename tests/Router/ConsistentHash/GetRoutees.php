<?php

declare(strict_types=1);

namespace Test\Router\ConsistentHash;

use Phluxor\ActorSystem\Ref;

readonly class GetRoutees
{
    public function __construct(
        public Ref $ref
    ) {
    }
}