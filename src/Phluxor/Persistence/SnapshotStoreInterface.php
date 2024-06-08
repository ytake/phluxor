<?php

declare(strict_types=1);

namespace Phluxor\Persistence;

use Google\Protobuf\Internal\Message;

interface SnapshotStoreInterface
{
    /**
     * @param string $actorName
     * @return SnapshotResult
     */
    public function getSnapshot(
        string $actorName
    ): SnapshotResult;

    /**
     * @param string $actorName
     * @param int $snapshotIndex
     * @param Message $snapshot
     * @return void
     */
    public function persistenceSnapshot(
        string $actorName,
        int $snapshotIndex,
        Message $snapshot
    ): void;

    /**
     * @param string $actorName
     * @param int $inclusiveToIndex
     * @return void
     */
    public function deleteSnapshots(
        string $actorName,
        int $inclusiveToIndex
    ): void;
}
