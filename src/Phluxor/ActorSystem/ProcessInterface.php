<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

interface ProcessInterface
{
    /**
     * @param ?Pid $pid
     * @param mixed $message
     * @return void
     */
    public function sendUserMessage(?Pid $pid, mixed $message): void;

    /**
     * @param Pid $pid
     * @param mixed $message
     * @return void
     */
    public function sendSystemMessage(Pid $pid, mixed $message): void;

    /**
     * @param Pid $pid
     * @return void
     */
    public function stop(Pid $pid): void;
}
