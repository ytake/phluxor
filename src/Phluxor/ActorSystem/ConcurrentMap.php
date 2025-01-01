<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

class ConcurrentMap
{
    /** @var ConcurrentMapShared[] */
    private array $map = [];

    private const int SHARD_COUNT = 32;

    public function __construct()
    {
        for ($i = 0; $i < self::SHARD_COUNT; $i++) {
            $this->map[$i] = new ConcurrentMapShared();
        }
    }

    /**
     * @param string $key
     * @return ConcurrentMapShared
     */
    public function getShard(string $key): ConcurrentMapShared
    {
        $shardIndex = $this->fnv32($key) % self::SHARD_COUNT;
        return $this->map[$shardIndex];
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function setIfAbsent(string $key, mixed $value): bool
    {
        $shard = $this->getShard($key);
        $shard->lock();
        $set = false;
        if (!$shard->offsetExists($key)) {
            $shard->offsetSet($key, $value);
            $set = true;
        }
        $shard->unlock();
        return $set;
    }

    /**
     * @param string $key
     * @return ConcurrentMapResult
     */
    public function pop(string $key): ConcurrentMapResult
    {
        $shard = $this->getShard($key);
        $shard->lock();
        $exists = $shard->offsetExists($key);
        $value = null;
        if ($exists) {
            $value = $shard->offsetGet($key);
        }
        $shard->offsetUnset($key);
        $shard->unlock();
        return new ConcurrentMapResult($value, $exists);
    }

    /**
     * @param string $key
     * @return ConcurrentMapResult
     */
    public function get(string $key): ConcurrentMapResult
    {
        $shard = $this->getShard($key);
        $shard->lock();
        $exists = $shard->offsetExists($key);
        $value = null;
        if ($exists) {
            $value = $shard->offsetGet($key);
        }
        $shard->unlock();
        return new ConcurrentMapResult($value, $exists);
    }

    /**
     * @param string $key
     * @return int
     */
    public function fnv32(string $key): int
    {
        $hash = 0x811c9dc5;     // FNV offset basis (32bit)
        $prime32 = 0x01000193; // FNV prime (32bit)
        $len = strlen($key);
        for ($i = 0; $i < $len; $i++) {
            $hash ^= ord($key[$i]);
            $hash = ($hash * $prime32) & 0xffffffff;
        }
        return $hash;
    }
}
