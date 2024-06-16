<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

interface ProcessInterface
{
    /**
     * @param ?Ref $pid
     * @param mixed $message
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
