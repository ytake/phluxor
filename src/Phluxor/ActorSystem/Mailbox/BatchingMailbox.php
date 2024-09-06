<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Mailbox;

use Closure;
use Phluxor\ActorSystem\Dispatcher\DispatcherInterface;
use Phluxor\ActorSystem\Message\MessageBatch;
use Phluxor\ActorSystem\Message\ResumeMailbox;
use Phluxor\ActorSystem\Message\SuspendMailbox;
use Phluxor\ActorSystem\QueueResult;
use Swoole\Atomic;
use Throwable;

class BatchingMailbox implements MailboxInterface
{
    private const int IDLE = 0;
    private const int RUNNING = 1;

    private Atomic $userMessages;
    private Atomic $systemMessages;
    private Atomic $schedulerStatus;
    private Atomic $suspended;
    private DispatcherInterface|null $dispatcher;
    private MessageInvokerInterface|null $invoker;

    /**
     * @param UnboundedMailboxQueue $userMailbox
     * @param UnboundedMailboxQueue $systemMailbox
     * @param int $batchSize
     * @param MailboxMiddlewareInterface[] $middlewares
     */
    public function __construct(
        private readonly UnboundedMailboxQueue $userMailbox,
        private readonly UnboundedMailboxQueue $systemMailbox,
        private readonly int $batchSize,
        private readonly array $middlewares
    ) {
        $this->userMessages = new Atomic(0);
        $this->systemMessages = new Atomic(0);
        $this->suspended = new Atomic(0);
        $this->schedulerStatus = new Atomic(self::IDLE);
    }

    /**
     * @param MessageInvokerInterface $invoker
     * @param DispatcherInterface $dispatcher
     * @return void
     */
    public function registerHandlers(
        MessageInvokerInterface $invoker,
        DispatcherInterface $dispatcher
    ): void {
        $this->invoker = $invoker;
        $this->dispatcher = $dispatcher;
    }

    public function start(): void
    {
        foreach ($this->middlewares as $middleware) {
            $middleware->mailboxStared();
        }
    }

    public function postUserMessage(mixed $message): void
    {
        foreach ($this->middlewares as $middleware) {
            $middleware->messagePosted($message);
        }
        $this->userMailbox->push($message);
        $this->userMessages->add();
        $this->schedule();
    }

    public function postSystemMessage(mixed $message): void
    {
        foreach ($this->middlewares as $middleware) {
            $middleware->messagePosted($message);
        }
        $this->systemMailbox->push($message);
        $this->systemMessages->add();
        $this->schedule();
    }

    /**
     * @param mixed $msg
     * @return void
     */
    protected function handleSystemMessage(mixed $msg): void
    {
        $msg = $this->getValue($msg);
        switch (true) {
            case $msg instanceof SuspendMailbox:
                $this->suspended->set(1);
                break;
            case $msg instanceof ResumeMailbox:
                $this->suspended->set(0);
                break;
            default:
                $this->invoker?->invokeSystemMessage($msg);
        };
        foreach ($this->middlewares as $middleware) {
            $middleware->messageReceived($msg);
        }
    }

    /**
     * @param mixed $msg
     * @return void
     */
    protected function handleUserMessage(mixed $msg): void
    {
        $msg = new MessageBatch($msg);
        $this->invoker?->invokeUserMessage($msg);
        foreach ($this->middlewares as $middleware) {
            $middleware->messageReceived($msg);
        }
    }

    public function run(): void
    {
        try {
            $batch = [];
            $msg = $this->systemMailbox->pop();

            if (!$msg->valueIsNull()) {
                $this->systemMessages->sub();
                $this->handleSystemMessage($msg);
            }

            if ($this->suspended->get() === 1) {
                return;
            }

            $msg = $this->userMailbox->pop();
            if (!$msg->valueIsNull()) {
                while (count($batch) < $this->batchSize) {
                    $batch[] = $this->getValue($msg);
                }
                $this->userMessages->sub();
                if (count($batch) > 0) {
                    $this->handleUserMessage($batch);
                }
            } else {
                return;
            }
        } catch (Throwable $e) {
            $this->suspended->set(1);
            $this->invoker?->escalateFailure($e, $msg ?? null);
        }
        $this->schedulerStatus->set(self::IDLE);

        if (!$this->userMailbox->isEmpty() ||
            (!$this->systemMailbox->isEmpty() && $this->suspended->get() === 0)) {
            $this->schedule();
        }
    }

    /**
     * @return Closure(): void
     */
    private function processMessage(): Closure
    {
        return function () {
            process:
            $this->run();
            $this->schedulerStatus->set(self::IDLE);
            $sys = $this->systemMessages->get();
            $user = $this->userMessages->get();
            if ($sys > 0 || ($this->suspended->get() === 0 && $user > 0)) {
                if ($this->schedulerStatus->cmpset(self::IDLE, self::RUNNING)) {
                    goto process;
                }
            }
            if ($user === 0 && $this->suspended->get() == 0) {
                foreach ($this->middlewares as $middleware) {
                    $middleware->mailboxEmpty();
                }
            }
        };
    }

    private function schedule(): void
    {
        if ($this->schedulerStatus->cmpset(self::IDLE, self::RUNNING)) {
            $this->dispatcher?->schedule($this->processMessage());
        }
    }

    public function userMessageCount(): int
    {
        return $this->userMessages->get();
    }

    public function systemMessageCount(): int
    {
        return $this->systemMessages->get();
    }

    /**
     * @param mixed $msg
     * @return mixed
     */
    public function getValue(mixed $msg): mixed
    {
        if ($msg instanceof QueueResult) {
            $msg = $msg->value();
        }
        return $msg;
    }
}
