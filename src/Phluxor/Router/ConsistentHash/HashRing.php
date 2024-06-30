<?php

declare(strict_types=1);

namespace Phluxor\Router\ConsistentHash;

use Ketama\Bucket;
use Ketama\Continuum;
use Ketama\Serverinfo;
use Psr\SimpleCache\CacheInterface;

class HashRing
{
    public function __construct(
        private CacheInterface $cache
    ) {
    }

    public function createContinuum(array $filename): Continuum
    {
        if (null !== $continuum = $this->loadFromCache($filename)) {
            return $continuum;
        }

        $servers = $this->readDefinitions($filename);
        $mtime = time();

        $memory = array_reduce($servers, function ($carry, Serverinfo $server): int {
            return $carry + $server->getMemory();
        }, 0);
        $buckets = [];
        $cont = 0;

        foreach ($servers as $i => $server) {
            $pct = $server->getMemory() / $memory;
            $ks = floor($pct * 40 * count($servers));

            for ($k = 0; $k < $ks; $k++) {
                $ss = sprintf('%s-%d', $server->getAddr(), $k);
                $digest = hash('md5', $ss, true);

                for ($h = 0; $h < 4; $h++) {
                    $unpacked = unpack('V', substr($digest, $h*4, 4));
                    assert($unpacked !== false);
                    [, $point] = $unpacked;
                    $buckets[$cont] = new Bucket($point, $server->getAddr());
                    $cont++;
                }
            }
        }

        usort($buckets, function ($a, $b): int {
            $a = $a->getPoint();
            $b = $b->getPoint();
            if ($a < $b) {
                return -1;
            }
            if ($a > $b) {
                return 1;
            }
            return 0;
        });

        $continuum = Continuum::create($buckets, $mtime);
        $this->storeCache($filename, $continuum);

        return $continuum;
    }

    /** @return Serverinfo[] */
    private function readDefinitions(array $nodes): array
    {
        $servers = [];
        foreach ($nodes as $server) {
            $serverinfo = new Serverinfo($server, 1);
            $servers[] = $serverinfo;
        }
        return $servers;
    }

    private function storeCache(array $array, Continuum $continuum): void
    {
        $key = 'continuum.' . md5(implode($array));
        $this->cache->set($key, $continuum->serialize());
    }

    private function loadFromCache(array $array): ?Continuum
    {
        $key = 'continuum.' . md5(implode($array));
        $data = $this->cache->get($key);
        if (null === $data) {
            return null;
        }

        assert(is_string($data));

        $continuum = Continuum::unserialize($data);



        return $continuum;
    }
}
