<?php

declare(strict_types=1);

namespace Phluxor\Metrics;

use Psr\Log\LoggerInterface;
use Swoole\Lock;

class PhluxorMetrics
{
    public const string INTERNAL_ACTOR_METRICS = 'internal.actor.metrics';

    private Lock $mutex;
    private ActorMetrics $actorMetrics;

    /** @var array{string, ActorMetrics} */
    private array $knownMetrics = [];

    public function __construct(
        private LoggerInterface $logger
    ) {
        $this->mutex = new Lock(Lock::MUTEX);
        $this->actorMetrics = new ActorMetrics();

        $this->register(self::INTERNAL_ACTOR_METRICS, $this->actorMetrics);
    }

    /**
     * Returns the ActorMetrics object.
     *
     * @return ActorMetrics The ActorMetrics object.
     */
    public function instruments(): ActorMetrics
    {
        return $this->actorMetrics;
    }

    /**
     * Registers a new ActorMetrics object with the specified key name.
     *
     * @param string $keyName The key name for the ActorMetrics object.
     * @param ActorMetrics $actorMetrics The ActorMetrics object to register.
     * @return void
     */
    public function register(string $keyName, ActorMetrics $actorMetrics): void
    {
        $this->mutex->lock();
        if (isset($this->knownMetrics[$keyName])) {
            $this->mutex->unlock();
            $this->logger->error(
                'could not register actor metrics, key already exists',
                [
                    'key' => $keyName,
                ]
            );
            return;
        }
        $this->knownMetrics[$keyName] = $actorMetrics;
        $this->mutex->unlock();
    }

    public function find(string $keyName): ?ActorMetrics
    {
        $metrics =  $this->knownMetrics[$keyName] ?? null;
        if ($metrics === null) {
            $this->logger->error(
                'could not find actor metrics',
                [
                    'key' => $keyName,
                ]
            );
        }
        return $metrics;
    }
}
