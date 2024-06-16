<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Closure;

use function array_pop;

class RefSet
{
    /** @var Ref[] */
    private array $pids = [];

    /** @var int[] array{string, int} */
    private array $lookup = [];

    public function __construct(Ref ...$pids)
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
     * @param Ref $pid
     * @return string
     */
    private function key(Ref $pid): string
    {
        $pidKey = new RefKey($pid->protobufPid()->getAddress(), $pid->protobufPid()->getId());
        return (string)$pidKey;
    }

    /**
     * @param Ref $pid
     * @return int
     */
    public function indexOf(Ref $pid): int
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
     * @param Ref $v
     * @return bool
     */
    public function contains(Ref $v): bool
    {
        return isset($this->lookup[$this->key($v)]);
    }

    /**
     * @param Ref $pid
     * @return void
     */
    public function add(Ref $pid): void
    {
        if (!$this->contains($pid)) {
            $this->pids[] = $pid;
            $this->lookup[$this->key($pid)] = count($this->pids) - 1;
        }
    }

    /**
     * @param Ref $pid
     * @return bool
     */
    public function remove(Ref $pid): bool
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
     * @return Ref[]
     */
    public function values(): array
    {
        return $this->pids;
    }

    /**
     * @param Closure(int, Ref): void $f
     * @return void
     */
    public function forEach(Closure $f): void
    {
        foreach ($this->pids as $index => $pid) {
            $f($index, $pid);
        }
    }

    public function get(int $index): ?Ref
    {
        return $this->pids[$index] ?? null;
    }

    public function clone(): RefSet
    {
        return new RefSet(...$this->pids);
    }
}
