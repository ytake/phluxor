<?php

declare(strict_types=1);

namespace Test\Persistence;

use Phluxor\Persistence\InMemoryProvider;
use Phluxor\Persistence\ProviderInterface;
use Phluxor\Persistence\ProviderStateInterface;
use Test\Persistence\ProtoBuf\TestMessage;
use Test\Persistence\ProtoBuf\TestSnapshot;

class DataState implements ProviderInterface
{
    private ProviderStateInterface $state;

    public function __construct(
        int $snapshotInterval,
    ) {
        $this->state = new InMemoryProvider($snapshotInterval);
    }

    public function initialize(int $lastSnapshot, string ...$state): ProviderStateInterface
    {
        for ($i = 0; $i < count($state); $i++) {
            $this->state->persistenceEvent(
                'test.actor',
                $i,
                new TestMessage(['message' => $state[$i]])
            );
        }
        if ($lastSnapshot < count($state)) {
            $snapshot = $state[$lastSnapshot];
            $this->state->persistenceSnapshot(
                'test.actor',
                $lastSnapshot,
                new TestSnapshot(['message' => $snapshot])
            );
        }
        return $this->state;
    }

    public function getState(): ProviderStateInterface
    {
        return $this->state;
    }
}
