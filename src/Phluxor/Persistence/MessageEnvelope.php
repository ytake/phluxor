<?php

declare(strict_types=1);

namespace Phluxor\Persistence;

readonly class MessageEnvelope
{
    public function __construct(
        public string $typeName,
        public string $rawMessage
    ) {
    }
}
