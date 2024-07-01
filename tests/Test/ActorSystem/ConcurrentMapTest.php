<?php

namespace Test\ActorSystem;

use Phluxor\ActorSystem\ConcurrentMap;
use PHPUnit\Framework\TestCase;

use function go;

class ConcurrentMapTest extends TestCase
{
    public function testConcurrentMap()
    {
        go(function () {
            $map = new ConcurrentMap();
            $s = $map->getShard('a');
            $s->offsetSet('a', 'b');
            $this->assertSame('b', $s->offsetGet('a'));
        });
        \Swoole\Event::wait();
    }
}
