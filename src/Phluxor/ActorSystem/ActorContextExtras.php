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

    /** @var PidSet */
    private PidSet $children;

    /** @var PidSet */
    private PidSet $watchers;

    /** @var int|bool|null */
    private int|bool|null $receiveTimeoutTimer = null;

    public function __construct(
        private readonly ContextInterface $context,
        private readonly ContextExtensions $extensions = new ContextExtensions(),
    ) {
        $this->children = new PidSet();
        $this->watchers = new PidSet();
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
     * @param Pid $pid
     * @return void
     */
    public function addChild(Pid $pid): void
    {
        $this->children->add($pid);
    }

    /**
     * @param Pid $pid
     * @return void
     */
    public function removeChild(Pid $pid): void
    {
        $this->children->remove($pid);
    }

    /**
     * @return Pid[]
     */
    public function childrenValues(): array
    {
        return $this->children->values();
    }

    /**
     * @param Pid $pid
     * @return void
     */
    public function watch(Pid $pid): void
    {
        $this->watchers->add($pid);
    }

    /**
     * @param Pid $pid
     * @return void
     */
    public function unwatch(Pid $pid): void
    {
        $this->watchers->remove($pid);
    }

    public function watchers(): PidSet
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
