<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use Phluxor\ActorSystem\ConcurrentMapShared;
use PHPUnit\Framework\TestCase;
use Phluxor\ActorSystem\SliceMap;

use function go;

class SliceMapTest extends TestCase
{
    public function testSliceMapShared(): void
    {
        go(function () {
            $map = new SliceMap();
            $cm = $map->getBucket('a');
            $this->assertInstanceOf(ConcurrentMapShared::class, $cm->getShard('a'));
        });
        \Swoole\Event::wait();
    }

    public function testSliceMapSetIfAbsent(): void
    {
        go(function () {
            $map = new SliceMap();
            $cm = $map->getBucket('a');
            $this->assertTrue($cm->setIfAbsent('a', 'b'));
            $this->assertFalse($cm->setIfAbsent('a', 'b'));
        });
        \Swoole\Event::wait();
    }
}
