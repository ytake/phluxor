<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Closure;
use Phluxor\ActorSystem\Child\RestartStatistics;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\Value\ContextExtensions;
use Swoole\Timer;

class ActorContextExtras
{
    /** @var RestartStatistics|null */
    private RestartStatistics|null $rs = null;

    /** @var Closure|null  */
    private Closure|null $timer = null;

    /** @var RefSet */
    private RefSet $children;

    /** @var RefSet */
    private RefSet $watchers;

    /** @var int|bool|null */
    private int|bool|null $receiveTimeoutTimer = null;

    public function __construct(
        private readonly ContextInterface $context,
        private readonly ContextExtensions $extensions = new ContextExtensions(),
    ) {
        $this->children = new RefSet();
        $this->watchers = new RefSet();
    }

    public function context(): ContextInterface
    {
        return $this->context;
    }

    public function extensions(): ContextExtensions
    {
        return $this->extensions;
    }

    /**
     * @return RestartStatistics
     */
    public function restartStats(): RestartStatistics
    {
        if ($this->rs == null) {
            $this->rs = new RestartStatistics();
        }
        return $this->rs;
    }

    /**
     * @param Ref $pid
     * @return void
     */
    public function addChild(Ref $pid): void
    {
        $this->children->add($pid);
    }

    /**
     * @param Ref $pid
     * @return void
     */
    public function removeChild(Ref $pid): void
    {
        $this->children->remove($pid);
    }

    /**
     * @return Ref[]
     */
    public function childrenValues(): array
    {
        return $this->children->values();
    }

    /**
     * @param Ref $pid
     * @return void
     */
    public function watch(Ref $pid): void
    {
        $this->watchers->add($pid);
    }

    /**
     * @param Ref $pid
     * @return void
     */
    public function unwatch(Ref $pid): void
    {
        $this->watchers->remove($pid);
    }

    public function watchers(): RefSet
    {
        return $this->watchers;
    }

    /**
     * @param int $seconds
     * @param Closure(): void $timer
     * @return void
     */
    public function initReceiveTimeoutTimer(int $seconds, Closure $timer): void
    {
        $this->timer = $timer;
        $this->receiveTimeoutTimer = Timer::after($seconds * 1000, $timer);
    }

    public function resetReceiveTimeoutTimer(int $seconds): void
    {
        if ($this->receiveTimeoutTimer == null) {
            return;
        }
        if ($this->timer == null) {
            return;
        }
        Timer::clear($this->receiveTimeoutTimer);
        $this->receiveTimeoutTimer = Timer::after($seconds * 1000, $this->timer);
    }

    public function killReceiveTimeoutTimer(): void
    {
        if ($this->receiveTimeoutTimer !== null) {
            Timer::clear($this->receiveTimeoutTimer);
            $this->receiveTimeoutTimer = null;
        }
    }

    public function receiveTimeoutTimer(): int|null
    {
        return $this->receiveTimeoutTimer;
    }

    public function stopReceiveTimeoutTimer(): void
    {
        if ($this->receiveTimeoutTimer == null) {
            return;
        }
        Timer::clear($this->receiveTimeoutTimer);
    }
}
