<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Mailbox;

interface MessageInvokerInterface
{
    /**
     * @param mixed $message
     * @return void
     */
    public function invokeSystemMessage(mixed $message): void;

    /**
     * @param mixed $message
     * @return void
     */
    public function invokeUserMessage(mixed $message): void;

    /**
     * @param mixed $reason
     * @param mixed $message
     * @return void
     */
    public function escalateFailure(mixed $reason, mixed $message): void;
}
