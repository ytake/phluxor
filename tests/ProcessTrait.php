<?php

declare(strict_types=1);

namespace Test;

use Closure;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Pid;

trait ProcessTrait
{
    /**
     * @param ActorSystem $system
     * @param string $id
     * @param Closure(?Pid, mixed): void|null $systemMessageFunc
     * @param Closure(?Pid, mixed): void|null $userMessageFunc
     * @return array{ref: ActorSystem\Pid, process: ActorSystem\ProcessInterface}
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
            'ref' => $r->getPid(),
            'process' => $process
        ];
    }

    /**
     * @param ActorSystem $system
     * @param ActorSystem\Pid $pid
     * @return void
     */
    private function removeMockProcess(ActorSystem $system, ActorSystem\Pid $pid): void
    {
        $system->getProcessRegistry()->remove($pid);
    }
}
