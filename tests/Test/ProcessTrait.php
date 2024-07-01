<?php

declare(strict_types=1);

namespace Test;

use Closure;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Ref;

trait ProcessTrait
{
    /**
     * @param ActorSystem $system
     * @param string $id
     * @param Closure(?Ref, mixed): void|null $systemMessageFunc
     * @param Closure(?Ref, mixed): void|null $userMessageFunc
     * @return array{ref: ActorSystem\Ref, process: ActorSystem\ProcessInterface}
     */
    private function spawnMockProcess(
        ActorSystem $system,
        string $id,
        Closure|null $systemMessageFunc = null,
        Closure|null $userMessageFunc = null
    ): array {
        $process = new MockProcess($systemMessageFunc, $userMessageFunc);
        $r = $system->getProcessRegistry()->add($process, $id);
        return [
            'ref' => $r->getRef(),
            'process' => $process
        ];
    }

    /**
     * @param ActorSystem $system
     * @param ActorSystem\Ref $pid
     * @return void
     */
    private function removeMockProcess(ActorSystem $system, ActorSystem\Ref $pid): void
    {
        $system->getProcessRegistry()->remove($pid);
    }
}
