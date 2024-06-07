<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Closure;

use function array_pop;

class PidSet
{
    /** @var Pid[] */
    private array $pids = [];

    /** @var int[] array{string, int} */
    private array $lookup = [];

    public function __construct(Pid ...$pids)
    {
        foreach ($pids as $pid) {
            $this->add($pid);
        }
    }

    public function ensureInit(): void
    {
        $this->lookup = [];
    }

    /**
     * @param Pid $pid
     * @return string
     */
    private function key(Pid $pid): string
    {
        $pidKey = new PidKey($pid->protobufPid()->getAddress(), $pid->protobufPid()->getId());
        return (string)$pidKey;
    }

    /**
     * @param Pid $pid
     * @return int
     */
    public function indexOf(Pid $pid): int
    {
        $k = $this->key($pid);
        foreach ($this->lookup as $v => $idx) {
            if ($v === $k) {
                return $idx;
            }
        }
        return -1;
    }

    /**
     * @param Pid $v
     * @return bool
     */
    public function contains(Pid $v): bool
    {
        return isset($this->lookup[$this->key($v)]);
    }

    /**
     * @param Pid $pid
     * @return void
     */
    public function add(Pid $pid): void
    {
        if (!$this->contains($pid)) {
            $this->pids[] = $pid;
            $this->lookup[$this->key($pid)] = count($this->pids) - 1;
        }
    }

    /**
     * @param Pid $pid
     * @return bool
     */
    public function remove(Pid $pid): bool
    {
        $index = $this->indexOf($pid);
        if ($index === -1) {
            return false;
        }

        unset($this->lookup[$this->key($pid)]);
        if ($index < count($this->pids) - 1) {
            $lastPID = $this->pids[count($this->pids) - 1];
            $this->pids[$index] = $lastPID;
            $this->lookup[$this->key($lastPID)] = $index;
        }

        array_pop($this->pids);
        return true;
    }

    public function clear(): void
    {
        $this->pids = [];
        $this->lookup = [];
    }

    public function len(): int
    {
        return count($this->pids);
    }

    public function empty(): bool
    {
        return $this->len() === 0;
    }

    /**
     * @return Pid[]
     */
    public function values(): array
    {
        return $this->pids;
    }

    /**
     * @param Closure(int, Pid): void $f
     * @return void
     */
    public function forEach(Closure $f): void
    {
        foreach ($this->pids as $index => $pid) {
            $f($index, $pid);
        }
    }

    public function get(int $index): ?Pid
    {
        return $this->pids[$index] ?? null;
    }

    public function clone(): PidSet
    {
        return new PidSet(...$this->pids);
    }
}
