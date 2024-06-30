<?php

declare(strict_types=1);

namespace Phluxor\Router\ConsistentHash;

use Ketama\Continuum;
use Phluxor\ActorSystem\Ref;

class HashmapContainer
{
    private array $routeeMap = [];

    /**
     * @param ?Continuum $hashring
     */
    public function __construct(
        private ?Continuum $hashring = null
    ) {
    }

    public function addRoutee(string $name, Ref $ref): void
    {
        $this->routeeMap[$name] = $ref;
    }

    public function setHashring(Continuum $hashring): void
    {
        $this->hashring = $hashring;
    }

    /**
     * @return Continuum
     * @throws ConsistentHashException
     */
    public function Hashring(): Continuum
    {
        if ($this->hashring === null) {
            throw new ConsistentHashException('Hashring is not set');
        }
        return $this->hashring;
    }

    /**
     * @return array{string, Ref}
     */
    public function getRouteeMap(): array
    {
        return $this->routeeMap;
    }
}
