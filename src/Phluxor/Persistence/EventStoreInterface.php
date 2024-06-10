<?php

declare(strict_types=1);

namespace Phluxor\Persistence;

use Closure;
use Google\Protobuf\Internal\Message;

interface EventStoreInterface
{
    /**
     * @param string $actorName
     * @param int $eventIndexStart
     * @param int $eventIndexEnd
     * @param Closure(mixed): void $callback
     * @return void
     */
    public function getEvents(
        string $actorName,
        int $eventIndexStart,
        int $eventIndexEnd,
        Closure $callback
    ): void;

    /**
     * @param string $actorName
     * @param int $eventIndex
     * @param Message $event
     * @return void
     */
    public function persistenceEvent(
        string $actorName,
        int $eventIndex,
        Message $event
    ): void;
}
