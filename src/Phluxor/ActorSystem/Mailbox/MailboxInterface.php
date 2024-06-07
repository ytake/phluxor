<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Mailbox;

use Phluxor\ActorSystem\Dispatcher\DispatcherInterface;

interface MailboxInterface
{
    /**
     * @param mixed $message
     * @return void
     */
    public function postUserMessage(mixed $message): void;

    /**
     * @param mixed $message
     * @return void
     */
    public function postSystemMessage(mixed $message): void;

    /**
     * @return void
     */
    public function start(): void;

    /**
     * @return int
     */
    public function userMessageCount(): int;

    /**
     * @param MessageInvokerInterface $invoker
     * @param DispatcherInterface $dispatcher
     * @return void
     */
    public function registerHandlers(
        MessageInvokerInterface $invoker,
        DispatcherInterface $dispatcher
    ): void;
}
