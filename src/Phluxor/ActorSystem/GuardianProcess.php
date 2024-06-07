<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Phluxor\ActorSystem\Message\Failure;
use Phluxor\ActorSystem\Exception\GuardianErrorException;
use Phluxor\ActorSystem\Message\Restart;
use Phluxor\ActorSystem\Message\ResumeMailbox;
use Phluxor\ActorSystem\ProtoBuf\Stop;

class GuardianProcess implements ProcessInterface, SupervisorInterface
{
    /**
     * @param GuardiansValue $guardiansValue
     * @param Pid|null $pid
     * @param SupervisorStrategyInterface $strategy
     */
    public function __construct(
        private readonly GuardiansValue $guardiansValue,
        private Pid|null $pid,
        private readonly SupervisorStrategyInterface $strategy
    ) {
    }

    public function sendUserMessage(?Pid $pid, mixed $message): void
    {
        throw new GuardianErrorException(
            'guardian actor cannot receive any user messages'
        );
    }

    public function sendSystemMessage(Pid $pid, mixed $message): void
    {
        if ($message instanceof Failure) {
            $this->strategy->handleFailure(
                $this->guardiansValue->getActorSystem(),
                $this,
                $message->getWho(),
                $message->getRestartStatistics(),
                $message->getReason(),
                $message->getMessage()
            );
        }
    }

    public function stop(Pid $pid): void
    {
        // none
    }

    public function children(): array
    {
        throw new GuardianErrorException(
            'guardian does not hold its children PIDs'
        );
    }

    public function escalateFailure(mixed $reason, mixed $message): void
    {
        throw new GuardianErrorException(
            'guardian cannot escalate failure'
        );
    }

    /**
     * @param Pid ...$pids
     * @return void
     */
    public function restartChildren(Pid ...$pids): void
    {
        foreach ($pids as $pid) {
            $pid->sendUserMessage($this->guardiansValue->getActorSystem(), new Restart());
        }
    }

    /**
     * @param Pid ...$pids
     * @return void
     */
    public function stopChildren(Pid ...$pids): void
    {
        foreach ($pids as $pid) {
            $pid->sendUserMessage($this->guardiansValue->getActorSystem(), new Stop());
        }
    }

    /**
     * @param Pid ...$pids
     * @return void
     */
    public function resumeChildren(Pid ...$pids): void
    {
        foreach ($pids as $pid) {
            $pid->sendUserMessage($this->guardiansValue->getActorSystem(), new ResumeMailbox());
        }
    }

    public function setPid(Pid $pid): void
    {
        $this->pid = $pid;
    }

    public function getPid(): Pid
    {
        if ($this->pid === null) {
            throw new GuardianErrorException(
                'guardian pid is not set'
            );
        }
        return $this->pid;
    }
}
