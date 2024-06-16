<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Context;

use Phluxor\ActorSystem\Future;
use Phluxor\ActorSystem\Ref;
use DateInterval;

interface SenderPartInterface
{
    /**
     * returns the Ref of actor that sent currently processed message
     * @return Ref|null
     */
    public function sender(): Ref|null;

    /**
     * sends a message to the actor identified by the Ref
     * @param Ref|null $pid
     * @param mixed $message
     * @return void
     */
    public function send(Ref|null $pid, mixed $message): void;

    /**
     * sends a message to the actor identified by the Ref and expects a response
     * @param Ref|null $pid
     * @param mixed $message
     * @return void
     */
    public function request(Ref|null $pid, mixed $message): void;

    /**
     * @param Ref|null $pid
     * @param mixed $message
     * @param Ref|null $sender
     * @return void
     */
    public function requestWithCustomSender(Ref|null $pid, mixed $message, Ref|null $sender): void;

    /**
     * sends a message to the actor identified by the Ref and expects a response within a specified time
     * @param Ref|null $pid
     * @param mixed $message
     * @param int $duration
     * @return Future
     */
    public function requestFuture(Ref|null $pid, mixed $message, int $duration): Future;
}
