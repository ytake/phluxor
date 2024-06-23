<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

interface ProcessInterface
{
    /**
     * Sends a user message.
     *
     * @param Ref|null $pid The reference to an actor.
     * @param mixed $message The message to send.
     * @return void
     */
    public function sendUserMessage(?Ref $pid, mixed $message): void;

    /**
     * @param Ref $pid
     * @param mixed $message
     * @return void
     */
    public function sendSystemMessage(Ref $pid, mixed $message): void;

    /**
     * @param Ref $pid
     * @return void
     */
    public function stop(Ref $pid): void;
}
