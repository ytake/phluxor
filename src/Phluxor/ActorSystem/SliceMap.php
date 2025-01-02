<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use SplFixedArray;
use GMP;

class SliceMap
{
    /** @var SplFixedArray<ConcurrentMap|null>  */
    private SplFixedArray $buckets;

    /** 32ビットマスク用 (0xFFFFFFFF)  */
    private GMP $mask32;

    /**
     * @param int $bucketSize バケット数 (初期1024)
     */
    public function __construct(int $bucketSize = 1024)
    {
        $this->buckets = new SplFixedArray($bucketSize);
        for ($i = 0; $i < $bucketSize; $i++) {
            $this->buckets[$i] = null;
        }
        $this->mask32 = gmp_init('FFFFFFFF', 16); // 0xFFFFFFFF
    }

    /**
     * 指定キーに対応するバケットを返す (ConcurrentMap を遅延生成)
     * lazy initialization
     */
    public function getBucket(string $key): ConcurrentMap
    {
        $hashGmp = $this->murmurHash3_32_GMP($key);
        $hashInt = gmp_intval($hashGmp);
        $index = $hashInt % $this->buckets->count();
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
     * GMPで 32ビットの MurmurHash3 を実装
     */
    private function murmurHash3_32_GMP(string $key): GMP
    {
        $length = strlen($key);

        // 定数をGMP化 (16進数で指定)
        $c1 = gmp_init('cc9e2d51', 16);
        $c2 = gmp_init('1b873593', 16);
        $five = gmp_init('5', 16);
        $mixConst = gmp_init('e6546b64', 16);

        // h1 (seed=0)
        $h1 = gmp_init('0', 16);

        // 4バイトごと処理
        $roundedEnd = $length & ~3; // 4の倍数へ切り捨て
        for ($i = 0; $i < $roundedEnd; $i += 4) {
            $k1 = gmp_init(
                (ord($key[$i]) & 0xff)
                | ((ord($key[$i + 1]) & 0xff) << 8)
                | ((ord($key[$i + 2]) & 0xff) << 16)
                | ((ord($key[$i + 3]) & 0xff) << 24),
                10
            );
            // k1 *= c1
            $k1 = $this->gmpAnd32(gmp_mul($k1, $c1));
            // rotateLeft(k1,15)
            $k1 = $this->gmpRotl32($k1, 15);
            // k1 *= c2
            $k1 = $this->gmpAnd32(gmp_mul($k1, $c2));

            // h1 ^= k1
            $h1 = gmp_xor($h1, $k1);
            // rotateLeft(h1,13)
            $h1 = $this->gmpRotl32($h1, 13);
            // h1 = (h1*5 + 0xe6546b64) & 0xffffffff
            $h1 = $this->gmpAnd32(gmp_mul($h1, $five));
            $h1 = gmp_add($h1, $mixConst);
            $h1 = $this->gmpAnd32($h1);
        }

        // 残り (1~3バイト)
        $k1 = gmp_init('0', 16);
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
                gmp_init(ord($key[$roundedEnd]) & 0xff, 10)
            );
            $k1 = $this->gmpAnd32(gmp_mul($k1, $c1));
            $k1 = $this->gmpRotl32($k1, 15);
            $k1 = $this->gmpAnd32(gmp_mul($k1, $c2));
            $h1 = gmp_xor($h1, $k1);
        }

        // lengthを XOR
        $h1 = gmp_xor($h1, gmp_init($length, 10));

        // fmix32
        $h1 = gmp_xor($h1, $this->gmpShiftRight($h1, 16));
        $h1 = $this->gmpAnd32(gmp_mul($h1, gmp_init('85ebca6b', 16)));
        $h1 = gmp_xor($h1, $this->gmpShiftRight($h1, 13));
        $h1 = $this->gmpAnd32(gmp_mul($h1, gmp_init('c2b2ae35', 16)));
        $h1 = gmp_xor($h1, $this->gmpShiftRight($h1, 16));

        return $this->gmpAnd32($h1);
    }

    /**
     * gmpでの AND (32ビット相当)
     * @param GMP $val
     * @return GMP
     */
    private function gmpAnd32(GMP $val): GMP
    {
        return gmp_and($val, $this->mask32);
    }

    /**
     * gmpでの rotate-left (32ビット相当)
     *
     * @param GMP $val
     * @param int $shift
     * @return GMP
     */
    private function gmpRotl32(GMP $val, int $shift): GMP
    {
        $shift &= 31;
        // 左シフト部 (val << shift) は val * (2^shift)
        $ls = gmp_mul($val, gmp_init(2 ** $shift, 10));
        $ls = $this->gmpAnd32($ls);

        // 右シフト部: val >> (32 - shift)
        $rsShift = 32 - $shift;
        $rs = $this->gmpShiftRight($val, $rsShift);

        // OR を取る
        return gmp_or($ls, $rs);
    }

    /**
     * gmpで論理右シフト(32ビット)
     * => val // 2^shift
     *
     * @param GMP $val
     * @param int $shift
     * @return GMP
     */
    private function gmpShiftRight(GMP $val, int $shift): GMP
    {
        if ($shift <= 0) {
            return $val;
        }
        $divisor = gmp_init(2 ** $shift, 10);
        $res = gmp_div_q($val, $divisor);
        return $this->gmpAnd32($res);
    }
}
