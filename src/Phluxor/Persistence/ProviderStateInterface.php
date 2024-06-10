<?php

declare(strict_types=1);

namespace Phluxor\Persistence;

interface ProviderStateInterface extends
    SnapshotStoreInterface,
    EventStoreInterface
{
    public function restart(): void;

    public function getSnapshotInterval(): int;
}
