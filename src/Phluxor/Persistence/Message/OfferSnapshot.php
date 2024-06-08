<?php

declare(strict_types=1);

namespace Phluxor\Persistence\Message;

final readonly class OfferSnapshot
{
    public function __construct(
        public mixed $snapshot,
    ) {
    }
}
