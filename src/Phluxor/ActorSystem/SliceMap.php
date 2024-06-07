<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use SplFixedArray;

class SliceMap
{
    /**
     * @param SplFixedArray<ConcurrentMap> $fixedArray
     */
    public function __construct(
        private SplFixedArray $fixedArray = new SplFixedArray(1024)
    ) {
        for ($i = 0; $i < $this->fixedArray->count(); $i++) {
            $this->fixedArray[$i] = new ConcurrentMap();
        }
    }

    public function getBucket(string $key): ConcurrentMap
    {
        $hash = $this->seedSum32(0, unpack('C*', $key));
        $index = $hash % 1024;
        return $this->fixedArray[$index];
    }

    private function SeedSum32(int $seed, array $data): int
    {
        $h1 = $seed;
        $clen = count($data);
        $c1_32 = 0xcc9e2d51;
        $c2_32 = 0x1b873593;

        for ($i = 1; $i + 3 <= $clen; $i += 4) {
            $k1 = ($data[$i] | $data[$i + 1] << 8 | $data[$i + 2] << 16 | $data[$i + 3] << 24);
            $k1 = $this->multiplyAndRotate($k1, $c1_32, 15);
            $k1 *= $c2_32;

            $h1 ^= intval($k1);
            $h1 = $this->multiplyAndRotate($h1, 5, 13);
            $h1 += 0xe6546b64;
        }

        $k1 = 0;
        for ($j = $i; $j < $clen; $j++) {
            $k1 ^= $data[$j] << ($j - $i) * 8;
        }
        if ($k1 !== 0) {
            $k1 = $this->multiplyAndRotate($k1, $c1_32, 15);
            $k1 *= $c2_32;
            $h1 ^= (int) $k1;
        }

        $h1 ^= $clen;
        $h1 = $this->finalizeHash32($h1);

        return $h1;
    }

    function multiplyAndRotate(int $value, int $multiplier, int $rot): int
    {
        $value *= $multiplier;
        $value = intval($value);
        return ($value << $rot) | ($value >> (32 - $rot));
    }

    function finalizeHash32(int $h): int
    {
        $h ^= $h >> 16;
        $h *= 0x85ebca6b;
        $h = intval($h);
        $h ^= $h >> 13;
        $h *= 0xc2b2ae35;
        $h = intval($h);
        $h ^= $h >> 16;
        return $h;
    }
}
