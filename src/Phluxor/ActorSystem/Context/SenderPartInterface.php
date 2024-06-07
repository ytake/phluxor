<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Context;

use Phluxor\ActorSystem\Future;
use Phluxor\ActorSystem\Pid;
use DateInterval;

interface SenderPartInterface
{
    /**
     * returns the Pid of actor that sent currently processed message
     * @return Pid|null
     */
    public function sender(): Pid|null;

    /**
     * sends a message to the actor identified by the Pid
     * @param Pid|null $pid
     * @param mixed $message
     * @return void
     */
    public function send(Pid|null $pid, mixed $message): void;

    /**
     * sends a message to the actor identified by the Pid and expects a response
     * @param Pid|null $pid
     * @param mixed $message
     * @return void
     */
    public function request(Pid|null $pid, mixed $message): void;

    /**
     * @param Pid|null $pid
     * @param mixed $message
     * @param Pid|null $sender
     * @return void
     */
    public function requestWithCustomSender(Pid|null $pid, mixed $message, Pid|null $sender): void;

    /**
     * sends a message to the actor identified by the Pid and expects a response within a specified time
     * @param Pid|null $pid
     * @param mixed $message
     * @param int $duration
     * @return Future
     */
    public function requestFuture(Pid|null $pid, mixed $message, int $duration): Future;
}
