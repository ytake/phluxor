<?php

declare(strict_types=1);

namespace Phluxor\Buffer;

use Phluxor\ActorSystem\QueueResult;
use Swoole\Atomic\Long;
use Swoole\Lock;

use function array_fill;

class Queue
{
    private RingBuffer $content;

    public function __construct(
        int $initialSize,
        private readonly Lock $lock = new Lock(),
        private readonly Long $len = new Long(0)
    ) {
        $this->content = new RingBuffer($initialSize);
    }

    public function push(mixed $item): void
    {
        $this->lock->lock();
        // 次のTailを確定
        $newTail = ($this->content->getTail() + 1) % $this->content->getMod();

        // 満杯判定 (tail と head が同じスロットを指したら満杯、としている)
        if ($newTail === $this->content->getHead()) {
            // 倍増処理
            $this->grow();
            // grow()後はRingBuffer全体が変わっているため再計算
            $newTail = ($this->content->getTail() + 1) % $this->content->getMod();
        }

        // 書き込み
        $this->content->overrideTail($newTail);
        $this->content->add($this->content->getTail(), $item);

        $this->len->add(1);
        $this->lock->unlock();
    }

    public function length(): int
    {
        return $this->len->get();
    }

    public function isEmpty(): bool
    {
        return $this->length() === 0;
    }

    public function pop(): QueueResult
    {
        if ($this->isEmpty()) {
            return new QueueResult(null, false);
        }

        $this->lock->lock();

        // 次のHeadを確定
        $newHead = ($this->content->getHead() + 1) % $this->content->getMod();
        $result = $this->content->getBuffer()[$newHead];

        // 取り出し後、スロットをnullクリア
        $this->content->add($newHead, null);

        $this->content->overrideHead($newHead);

        $this->len->sub(1);

        // 一定のタイミングでメモリ縮小を試みる
        // (毎回呼ぶ例。実際は一定回数に1度呼ぶなど工夫してもよい)
        $this->maybeShrink();

        $this->lock->unlock();

        return new QueueResult($result, true);
    }

    public function popMany(int $count): QueueResult
    {
        if ($this->isEmpty()) {
            return new QueueResult(null, false);
        }

        $this->lock->lock();

        // 実際に取り出す要素数
        $avail = $this->len->get();
        if ($count >= $avail) {
            $count = $avail;
        }

        $buffer = [];
        for ($i = 0; $i < $count; $i++) {
            $pos = ($this->content->getHead() + 1 + $i) % $this->content->getMod();
            $buffer[] = $this->content->getBuffer()[$pos];
            // nullクリア
            $this->content->add($pos, null);
        }

        // head更新
        $this->content->overrideHead(
            ($this->content->getHead() + $count) % $this->content->getMod()
        );

        $this->len->sub($count);

        // 縮小チェック
        $this->maybeShrink();

        $this->lock->unlock();

        return new QueueResult($buffer, true);
    }

    /**
     * リングバッファを2倍に拡張する
     */
    private function grow(): void
    {
        $oldMod = $this->content->getMod();
        $newMod = $oldMod * 2;
        $newBuffer = array_fill(0, $newMod, null);

        // headから順にコピーしなおす(実装方法は一例)
        // 現在の要素数
        $currentSize = $this->len->get();

        // headの次から順に currentSize 分だけ埋める
        for ($i = 0; $i < $currentSize; $i++) {
            $oldPos = ($this->content->getHead() + 1 + $i) % $oldMod;
            $newBuffer[$i + 1] = $this->content->getBuffer()[$oldPos];
        }

        // 新しいRingBufferに置き換え
        $this->content->replaceBuffer($newBuffer, $newMod);

        // headは0, tailはcurrentSizeぶん進める
        $this->content->overrideHead(0);
        $this->content->overrideTail($currentSize);
    }

    /**
     * リングバッファを縮小する (25%以下の使用率なら半分にする)
     */
    private function maybeShrink(): void
    {
        $oldMod = $this->content->getMod();
        // あまりに小さい場合は縮小しない
        if ($oldMod <= 4) {
            return;
        }

        $currentSize = $this->len->get();
        $usageRatio = $currentSize / $oldMod;

        // 使用率が25%以下なら縮小を試みる
        if ($usageRatio < 0.25) {
            $newMod = (int)($oldMod / 2);
            // 最小限 currentSize は入る必要があるのでそれ以下にはしない
            if ($newMod < $currentSize + 1) {
                return;
            }

            $newBuffer = array_fill(0, $newMod, null);

            // headの次から順に currentSize 分だけコピー
            for ($i = 0; $i < $currentSize; $i++) {
                $oldPos = ($this->content->getHead() + 1 + $i) % $oldMod;
                $newBuffer[$i + 1] = $this->content->getBuffer()[$oldPos];
            }

            $this->content->replaceBuffer($newBuffer, $newMod);

            // headは0, tailはcurrentSizeぶん進める
            $this->content->overrideHead(0);
            $this->content->overrideTail($currentSize);
        }
    }
}
