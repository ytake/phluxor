<?php

declare(strict_types=1);

namespace Test\Router\ConsistentHash;

use Phluxor\Router\ConsistentHash\HashRing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class HashRingTest extends TestCase
{
    public function testShouldReturnCorrectNode(): void
    {
        $hashring = new HashRing(new Psr16Cache(new ArrayAdapter()));
        $hash = $hashring->createContinuum(['node1', 'node2', 'node3']);
        $cases = [
            'com.github.35' => 'node1',
            'com.github.36' => 'node2',
            'com.github.100' => 'node3',
        ];
        foreach ($cases as $k => $v) {
            $this->assertEquals($v, $hash->getServer($k));
        }
    }
}
