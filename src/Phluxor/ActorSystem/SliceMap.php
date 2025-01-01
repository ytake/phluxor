<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use SplFixedArray;
use GMP;

class SliceMap
{
    /** @var SplFixedArray<ConcurrentMap|null> */
    private SplFixedArray $buckets;

    public function __construct(int $bucketSize = 1024)
    {
        $this->buckets = new SplFixedArray($bucketSize);
        for ($i = 0; $i < $bucketSize; $i++) {
            $this->buckets[$i] = null;
        }
    }

    public function getBucket(string $key): ConcurrentMap
    {
        // GMP版 MurmurHash3(32bit)
        $hash32 = $this->murmurHash3_32_GMP($key);

        // other implementation
        // if (gmp_cmp($hash32, gmp_init('0x80000000')) >= 0) {
        //     $index = gmp_intval(gmp_sub($hash32, gmp_init('4294967296')));
        // } else {
        //     $index = gmp_intval($hash32);
        // }
        $index = gmp_intval($hash32) % $this->buckets->count();
        if ($index < 0) {
            $index += $this->buckets->count();
        }

        $bucket = $this->buckets[$index];
        if ($bucket === null) {
            $bucket = new ConcurrentMap();
            $this->buckets[$index] = $bucket;
        }

        return $bucket;
    }

    /**
     * GMPで 32ビットのMurmurHash3を実装
     */
    private function murmurHash3_32_GMP(string $key): GMP
    {
        $length = strlen($key);

        // 定数をGMP化
        $c1 = gmp_init('0xcc9e2d51', 16);
        $c2 = gmp_init('0x1b873593', 16);
        $five = gmp_init(5, 10);
        $mixConst = gmp_init('0xe6546b64', 16);

        // h1 (seed=0)
        $h1 = gmp_init(0, 10);

        // 4バイトごと処理
        $roundedEnd = $length & ~3; // 4の倍数に切り捨て
        for ($i = 0; $i < $roundedEnd; $i += 4) {
            $k1 = gmp_init(
                (ord($key[$i]) & 0xff)
                | ((ord($key[$i + 1]) & 0xff) << 8)
                | ((ord($key[$i + 2]) & 0xff) << 16)
                | ((ord($key[$i + 3]) & 0xff) << 24),
                10
            );
            // k1 *= c1
            $k1 = self::gmpAnd32(gmp_mul($k1, $c1));
            // k1 = rotateLeft(k1, 15)
            $k1 = self::gmpRotl32($k1, 15);
            // k1 *= c2
            $k1 = self::gmpAnd32(gmp_mul($k1, $c2));

            // h1 ^= k1
            $h1 = gmp_xor($h1, $k1);
            // h1 = rotateLeft(h1, 13)
            $h1 = self::gmpRotl32($h1, 13);
            // h1 = h1*5 + 0xe6546b64
            $h1 = gmp_add(self::gmpAnd32(gmp_mul($h1, $five)), $mixConst);
            $h1 = self::gmpAnd32($h1);
        }

        // 残り (1~3バイト)
        $k1 = gmp_init(0, 10);
        $tailSize = $length & 3;
        if ($tailSize >= 3) {
            $k1 = gmp_xor(
                $k1,
                gmp_init((ord($key[$roundedEnd + 2]) & 0xff) << 16, 10)
            );
        }
        if ($tailSize >= 2) {
            $k1 = gmp_xor(
                $k1,
                gmp_init((ord($key[$roundedEnd + 1]) & 0xff) << 8, 10)
            );
        }
        if ($tailSize >= 1) {
            $k1 = gmp_xor(
                $k1,
                gmp_init((ord($key[$roundedEnd]) & 0xff), 10)
            );

            $k1 = self::gmpAnd32(gmp_mul($k1, $c1));
            $k1 = self::gmpRotl32($k1, 15);
            $k1 = self::gmpAnd32(gmp_mul($k1, $c2));

            $h1 = gmp_xor($h1, $k1);
        }

        // lengthを XOR
        $h1 = gmp_xor($h1, gmp_init($length, 10));

        // fmix32
        // h1 ^= h1 >> 16
        $h1 = gmp_xor($h1, self::gmpShiftRight($h1, 16));
        $h1 = self::gmpAnd32(gmp_mul($h1, gmp_init('0x85ebca6b', 16)));
        $h1 = gmp_xor($h1, self::gmpShiftRight($h1, 13));
        $h1 = self::gmpAnd32(gmp_mul($h1, gmp_init('0xc2b2ae35', 16)));
        $h1 = gmp_xor($h1, self::gmpShiftRight($h1, 16));
        return self::gmpAnd32($h1);
    }

    /**
     * gmp 32bit  (val & 0xffffffff)
     * @param GMP $val
     * @return GMP
     */
    private static function gmpAnd32(GMP $val): GMP
    {
        static $mask32 = null;
        if (!$mask32) {
            // 0xffffffff
            $mask32 = gmp_init('4294967295', 10);
        }
        return gmp_and($val, $mask32);
    }

    /**
     * gmp 32bit rotate-left
     * @param GMP $val
     * @param int $shift
     * @return GMP
     */
    private static function gmpRotl32(GMP $val, int $shift): GMP
    {
        $shift = $shift & 31;
        $val = self::gmpAnd32($val);
        $ls = gmp_mul($val, gmp_init(2 ** $shift, 10));
        $ls = self::gmpAnd32($ls);
        // right-shift
        $rsShift = 32 - $shift;
        // $val >> (32-$shift)
        $rs = self::gmpShiftRight($val, $rsShift);
        // OR
        return gmp_or($ls, $rs);
    }

    /**
     * gmp 32bit shift-right
     * subtract 2^$shift
     *
     * gmpで論理右シフト(符号を維持しない右シフト)
     * PHPの算術シフトと違い、GMPなので自力で 2^$shift
     * @param GMP $val
     * @param int $shift
     * @return GMP
     */
    private static function gmpShiftRight(GMP $val, int $shift): GMP
    {
        // $val // 2^$shift
        return gmp_div_q($val, gmp_init(2 ** $shift, 10));
    }
}
