<?php

declare(strict_types=1);

namespace Phluxor\EventStream;

use Closure;
use Swoole\Atomic;

class Subscription
{
    const int DEACTIVE = 0;
    const int ACTIVE = 1;

    /** @var Closure(mixed): void|null  */
    private Closure|null $handler = null;

    /** @var Closure(mixed): bool|null  */
    private Closure|null $predicate = null;
    private readonly Atomic $active;

    /**
     * @param int $active
     * @param int $id
     */
    public function __construct(
        int $active = self::DEACTIVE,
        private int $id = 0,
    ) {
        $this->active = new Atomic($active);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param Closure(mixed): bool $predicate
     * @return void
     */
    public function setPredicate(Closure $predicate): void
    {
        $this->predicate = $predicate;
    }

    /**
     * @param mixed $event
     * @return bool
     */
    public function predicate(mixed $event): bool
    {
        return $this->predicate ? ($this->predicate)($event) : true;
    }

    /**
     * @return Closure(mixed): bool|null
     */
    public function getPredicate(): Closure|null
    {
        return $this->predicate;
    }

    /**
     * @param Closure(mixed): void $handler
     * @return void
     */
    public function setHandler(Closure $handler): void
    {
        $this->handler = $handler;
    }

    public function handle(mixed $event): void
    {
        if ($this->handler) {
            ($this->handler)($event);
        }
    }

    public function activate(): bool
    {
        return $this->active->cmpset(self::DEACTIVE, self::ACTIVE);
    }

    public function deactivate(): bool
    {
        return $this->active->cmpset(self::ACTIVE, self::DEACTIVE);
    }

    public function isActive(): bool
    {
        return $this->active->get() === self::ACTIVE;
    }
}
