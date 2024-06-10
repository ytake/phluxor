<?php

declare(strict_types=1);

namespace Phluxor\Persistence;

readonly class SnapshotResult
{
    /**
     * @param mixed $snapshot
     * @param int $eventIndex
     * @param bool $ok
     */
    public function __construct(
        private mixed $snapshot,
        private int $eventIndex,
        private bool $ok
    ) {
    }

    public function getSnapshot(): mixed
    {
        return $this->snapshot;
    }

    public function getEventIndex(): int
    {
        return $this->eventIndex;
    }

    public function isOk(): bool
    {
        return $this->ok;
    }
}
