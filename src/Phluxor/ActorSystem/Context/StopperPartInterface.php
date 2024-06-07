<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Context;

use Phluxor\ActorSystem\Future;
use Phluxor\ActorSystem\Pid;

interface StopperPartInterface
{
    /**
     * will stop actor immediately regardless of existing user messages in mailbox.
     * @param Pid|null $pid
     * @return void
     */
    public function stop(Pid|null $pid): void;

    /**
     * will stop actor immediately regardless of existing user messages in mailbox,
     * and return its future.
     * @param Pid|null $pid
     * @return Future|null
     */
    public function stopFuture(Pid|null $pid): Future|null;

    /**
     * will tell actor to stop after processing current user messages in mailbox.
     * @param Pid|null $pid
     * @return void
     */
    public function poison(Pid|null $pid): void;

    /**
     * will tell actor to stop after processing current user messages in mailbox,
     * and return its future.
     * @param Pid|null $pid
     * @return Future|null
     */
    public function poisonFuture(Pid|null $pid): Future|null;
}
