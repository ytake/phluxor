<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Context;

use Phluxor\ActorSystem\Future;
use Phluxor\ActorSystem\Ref;

interface StopperPartInterface
{
    /**
     * will stop actor immediately regardless of existing user messages in mailbox.
     * @param Ref|null $pid
     * @return void
     */
    public function stop(Ref|null $pid): void;

    /**
     * will stop actor immediately regardless of existing user messages in mailbox,
     * and return its future.
     * @param Ref|null $pid
     * @return Future|null
     */
    public function stopFuture(Ref|null $pid): Future|null;

    /**
     * will tell actor to stop after processing current user messages in mailbox.
     * @param Ref|null $pid
     * @return void
     */
    public function poison(Ref|null $pid): void;

    /**
     * will tell actor to stop after processing current user messages in mailbox,
     * and return its future.
     * @param Ref|null $pid
     * @return Future|null
     */
    public function poisonFuture(Ref|null $pid): Future|null;
}
