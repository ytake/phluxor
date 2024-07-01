<?php

declare(strict_types=1);

namespace Test\Persistence;

use Phluxor\Persistence\InMemoryProvider;
use Phluxor\Persistence\ProviderInterface;
use Phluxor\Persistence\ProviderStateInterface;

readonly class InMemoryStateProvider implements ProviderInterface
{
    /**
     * @param InMemoryProvider $provider
     */
    public function __construct(
        private InMemoryProvider $provider,
    ) {
    }

    public function getState(): ProviderStateInterface
    {
        return $this->provider;
    }
}
