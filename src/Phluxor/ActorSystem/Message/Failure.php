<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Message;

use Phluxor\ActorSystem\Child\RestartStatistics;
use Phluxor\ActorSystem\Pid;

/**
 * message is sent to an actor parent when an exception is thrown by one of its methods
 */
readonly class Failure implements SystemMessageInterface
{
    /**
     * @param Pid $who
     * @param mixed $reason
     * @param RestartStatistics $restartStatistics
     * @param mixed $message
     */
    public function __construct(
        private Pid $who,
        private mixed $reason,
        private RestartStatistics $restartStatistics,
        private mixed $message,
    ) {
    }

    public function getWho(): Pid
    {
        return $this->who;
    }

    public function getReason(): mixed
    {
        return $this->reason;
    }

    public function getRestartStatistics(): RestartStatistics
    {
        return $this->restartStatistics;
    }

    public function getMessage(): mixed
    {
        return $this->message;
    }
}
