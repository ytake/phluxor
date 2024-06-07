<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use function sprintf;

readonly class PidKey
{
    /**
     * @param string $address
     * @param string $id
     */
    public function __construct(
        private string $address,
        private string $id,
    ) {
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return sprintf('%s-%s', $this->address, $this->id);
    }
}
