<?php

declare(strict_types=1);

namespace Phluxor\Persistence;

use Closure;
use Google\Protobuf\Internal\Message;
use Swoole\Lock;

/**
 * use for testing only
 * do not use in production
 */
class InMemoryProvider implements ProviderStateInterface
{
    private Lock $lock;

    /** @var array<string, array{entry: array{eventIndex: int, snapshot: ?Message, events: Message[]}, loaded: bool}> */
    private array $store = [];

    public function __construct(
        private readonly int $snapshotInterval,
    ) {
        $this->lock = new Lock(Lock::MUTEX);
    }

    /**
     * @param string $actorName
     * @return array{entry: array{eventIndex: int, snapshot: ?Message, events: Message[]}, loaded: bool}
     */
    private function loader(string $actorName): array
    {
        $this->lock->lock();
        if (!isset($this->store[$actorName])) {
            $this->store[$actorName] = [
                'entry' => [
                    'snapshot' => null,
                    'eventIndex' => 0,
                    'events' => [],
                ],
                'loaded' => true,
            ];
        }
        $this->lock->unlock();
        return [
            'entry' => $this->store[$actorName]['entry'],
            'loaded' => true,
        ];
    }

    public function getEvents(string $actorName, int $eventIndexStart, int $eventIndexEnd, Closure $callback): void
    {
        $r = $this->loader($actorName);
        if ($eventIndexEnd == 0) {
            if (isset($r['entry'])) {
                if (isset($r['entry']['events'])) {
                    $eventIndexEnd = count($r['entry']['events']);
                }
            }
        }
        for ($i = $eventIndexStart; $i <= $eventIndexEnd; $i++) {
            if (isset($r['entry'])) {
                if (isset($r['entry']['events'])) {
                    if (isset($r['entry']['events'][$i])) {
                        $callback($r['entry']['events'][$i]);
                    }
                }
            }
        }
    }

    public function persistenceEvent(string $actorName, int $eventIndex, Message $event): void
    {
        $r = $this->loader($actorName);
        if (!count($r['entry']['events'])) {
            $this->store[$actorName]['entry']['events'] = [$event];
        }
        if (count($r['entry']['events'])) {
            $this->store[$actorName]['entry']['events'] = array_merge($r['entry']['events'], [$event]);
        }
    }

    public function restart(): void
    {
    }

    public function getSnapshotInterval(): int
    {
        return $this->snapshotInterval;
    }

    public function getSnapshot(string $actorName): SnapshotResult
    {
        $r = $this->loader($actorName);
        if (!isset($r['entry'])) {
            return new SnapshotResult(null, 0, false);
        }
        if (isset($r['entry']['snapshot'])) {
            if (!$r['loaded'] || $r['entry']['snapshot'] == null) {
                return new SnapshotResult(null, 0, false);
            } else {
                return new SnapshotResult($r['entry']['snapshot'], $r['entry']['eventIndex'], true);
            }
        }
        return new SnapshotResult(null, 0, false);
    }

    public function persistenceSnapshot(string $actorName, int $snapshotIndex, Message $snapshot): void
    {
        $this->loader($actorName);
        $this->store[$actorName]['entry']['snapshot'] = $snapshot;
        $this->store[$actorName]['entry']['eventIndex'] = $snapshotIndex;
    }
}
