<?php

declare(strict_types=1);

namespace Phluxor\Swoole\OpenTelemetry;

use OpenTelemetry\Context\ExecutionContextAwareInterface;
use Swoole\Coroutine;

final readonly class SwooleContextHandler
{
    public function __construct(
        private ExecutionContextAwareInterface $storage
    ) {
    }

    public function switchToActiveCoroutine(): void
    {
        $cid = Coroutine::getCid();
        if ($cid !== -1 && !$this->isForked($cid)) {
            for (
                $pcid = $cid; ($pcid = Coroutine::getPcid($pcid)) !== -1 && Coroutine::exists(
                $pcid
            ) && !$this->isForked($pcid);
            ) {
            }

            $this->storage->switch($pcid);
            $this->forkCoroutine($cid);
        }

        $this->storage->switch($cid);
    }

    /**
     * for swoole & openswoole
     * @see https://github.com/opentelemetry-php/context-swoole/pull/1
     * @return void
     */
    public function splitOffChildCoroutines(): void
    {
        $pcid = Coroutine::getCid();
        // @phpstan-ignore-next-line
        $clist = method_exists(Coroutine::class, 'list') ? Coroutine::list() : Coroutine::listCoroutines();
        foreach ($clist as $cid) {
            if ($pcid === Coroutine::getPcid($cid) && !$this->isForked($cid)) {
                $this->forkCoroutine($cid);
            }
        }
    }

    private function isForked(int $cid): bool
    {
        return isset(Coroutine::getContext($cid)[__CLASS__]);
    }

    private function forkCoroutine(int $cid): void
    {
        $this->storage->fork($cid);
        Coroutine::getContext($cid)[__CLASS__] = new SwooleContextDestructor($this->storage, $cid);
    }
}
