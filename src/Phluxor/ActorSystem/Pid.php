<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Phluxor\ActorSystem;

class Pid
{
    private ProcessInterface|null $process = null;

    public function __construct(
        private readonly ActorSystem\ProtoBuf\PID $pid
    ) {
    }

    /**
     * @param ActorSystem $actorSystem
     * @return ProcessInterface|null
     */
    public function ref(ActorSystem $actorSystem): ProcessInterface|null
    {
        if ($this->process !== null) {
            if ($this->process instanceof ActorProcess && $this->process->dead()->get() === 1) {
                $this->process = null;
            } else {
                return $this->process;
            }
        }
        $result = $actorSystem->getProcessRegistry()->get($this);
        if ($result->isProcess()) {
            $this->process = $result->getProcess();
        }
        return $result->getProcess();
    }

    /**
     * @param ActorSystem $actorSystem
     * @param mixed $message
     * @return void
     */
    public function sendUserMessage(ActorSystem $actorSystem, mixed $message): void
    {
        $this->ref($actorSystem)?->sendUserMessage($this, $message);
    }

    /**
     * @param ActorSystem $actorSystem
     * @param mixed $message
     * @return void
     */
    public function sendSystemMessage(ActorSystem $actorSystem, mixed $message): void
    {
        $this->ref($actorSystem)?->sendSystemMessage($this, $message);
    }

    /**
     * @return ProtoBuf\PID
     */
    public function protobufPid(): ActorSystem\ProtoBuf\PID
    {
        return $this->pid;
    }

    /**
     * @param Pid|null $other
     * @return bool
     */
    public function equal(Pid|null $other): bool
    {
        if ($other == null) {
            return false;
        }
        return $this->pid->getId() == $other->pid->getId()
            && $this->pid->getAddress() == $other->pid->getAddress()
            && $this->pid->getRequestId() == $other->pid->getRequestId();
    }

    public function __toString(): string
    {
        return $this->pid->getId();
    }
}
