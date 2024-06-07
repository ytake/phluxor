<?php

declare(strict_types=1);

namespace Phluxor\EventStream;

use Closure;
use Swoole\Lock;

use const SWOOLE_MUTEX;

class EventStream
{
    /** @var Subscription[] */
    private array $subscriptions = [];
    private int $counter = 0;

    /**
     * @param Lock $mutex
     */
    public function __construct(
        private readonly Lock $mutex = new Lock(SWOOLE_MUTEX),
    ) {
    }

    /**
     * @param Closure(mixed): void $handler
     * @return Subscription
     */
    public function subscribe(Closure $handler): Subscription
    {
        $this->mutex->lock();
        try {
            $sub = new Subscription(active: Subscription::ACTIVE);
            $sub->setHandler($handler);
            $sub->setId($this->counter++);
            $this->subscriptions[] = $sub;
        } finally {
            $this->mutex->unlock();
        }
        return $sub;
    }

    /**
     * @param Closure(mixed): void $handler
     * @param Closure(mixed): bool $predicate
     * @return Subscription
     */
    public function subscribeWithPredicate(
        Closure $handler,
        Closure $predicate
    ): Subscription {
        $sub = $this->subscribe($handler);
        $sub->setPredicate($predicate);
        return $sub;
    }

    /**
     * @param Subscription $sub
     * @return void
     */
    public function unsubscribe(Subscription $sub): void
    {
        if (!$sub->isActive()) {
            return;
        }

        $this->mutex->lock();
        try {
            if ($sub->deactivate()) {
                $index = $sub->getId();
                $lastIndex = $this->counter - 1;
                if ($index < $lastIndex) {
                    $this->subscriptions[$index] = $this->subscriptions[$lastIndex];
                    $this->subscriptions[$index]->setId($index);
                }
                unset($this->subscriptions[$lastIndex]);
                $this->counter--;

                if ($this->counter == 0) {
                    $this->subscriptions = [];
                }
            }
        } finally {
            $this->mutex->unlock();
        }
    }

    public function publish(mixed $event): void
    {
        $this->mutex->lock();
        $subs = [];
        foreach ($this->subscriptions as $sub) {
            if ($sub->isActive()) {
                $subs[] = $sub;
            }
        }
        $this->mutex->unlock();

        foreach ($subs as $sub) {
            if (!$sub->predicate($event)) {
                continue;
            }
            $sub->handle($event);
        }
    }

    /**
     * @return int
     */
    public function length(): int
    {
        $this->mutex->lock();
        $count = $this->counter;
        $this->mutex->unlock();
        return $count;
    }
}
