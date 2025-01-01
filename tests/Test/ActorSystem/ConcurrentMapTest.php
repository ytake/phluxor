<?php

namespace Test\ActorSystem;

use Phluxor\ActorSystem\ConcurrentMap;
use Phluxor\ActorSystem\ConcurrentMapShared;
use PHPUnit\Framework\TestCase;

use function go;
use function Swoole\Coroutine\run;

class ConcurrentMapTest extends TestCase
{
    public function testConcurrentMap(): void
    {
        run(function () {
            go(function () {
                $map = new ConcurrentMap();
                $s = $map->getShard('a');
                $s->offsetSet('a', 'b');
                $this->assertSame('b', $s->offsetGet('a'));
            });
        });
    }

    /**
     * @covers \Phluxor\ActorSystem\ConcurrentMap::getShard
     * Test that getShard returns the same shard for identical keys.
     */
    public function testGetShardConsistency(): void
    {
        $map = new ConcurrentMap();
        $shard1 = $map->getShard('testKey');
        $shard2 = $map->getShard('testKey');
        $this->assertSame($shard1, $shard2);
    }

    /**
     * @covers \Phluxor\ActorSystem\ConcurrentMap::getShard
     * Test shard retrieval with different keys.
     */
    public function testGetShardWithDifferentKeys(): void
    {
        $map = new ConcurrentMap();
        $shard1 = $map->getShard('key1');
        $shard2 = $map->getShard('key2');
        $this->assertNotSame($shard1, $shard2);
    }

    /**
     * @covers \Phluxor\ActorSystem\ConcurrentMap::getShard
     * Test shard retrieval with edge cases such as empty and special characters.
     */
    public function testGetShardWithEdgeCases(): void
    {
        $map = new ConcurrentMap();
        $emptyShard = $map->getShard('');
        $specialCharShard = $map->getShard('!@#$%^&*()');
        $this->assertInstanceOf(ConcurrentMapShared::class, $emptyShard);
        $this->assertInstanceOf(ConcurrentMapShared::class, $specialCharShard);
    }

    /**
     * @covers \Phluxor\ActorSystem\ConcurrentMap::getShard
     * Test that getShard always returns an instance of ConcurrentMapShared.
     */
    public function testGetShardReturnsValidInstance(): void
    {
        $map = new ConcurrentMap();
        $shard = $map->getShard('someKey');
        $this->assertInstanceOf(ConcurrentMapShared::class, $shard);
    }
}
