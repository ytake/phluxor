<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Mailbox;

use Closure;
use Phluxor\ActorSystem\Dispatcher\DispatcherInterface;
use Phluxor\ActorSystem\Message\MessageBatchInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Message\ResumeMailbox;
use Phluxor\ActorSystem\Message\SuspendMailbox;
use Phluxor\ActorSystem\QueueInterface;
use Phluxor\Mspc\Queue as MpscQueue;
use Swoole\Atomic;
use Throwable;

class DefaultMailbox implements MailboxInterface
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
     * @param QueueInterface $userMailbox
     * @param MpscQueue $systemMailbox
     * @param MailboxMiddlewareInterface[] $middlewares
     */
    public function __construct(
        private readonly QueueInterface $userMailbox,
        private readonly MpscQueue $systemMailbox,
        private readonly array $middlewares
    ) {
        $this->userMessages = new Atomic(0);
        $this->systemMessages = new Atomic(0);
        $this->suspended = new Atomic(0);
        $this->schedulerStatus = new Atomic(self::IDLE);
    }

    /**
     * @param mixed $message
     * @return void
     */
    public function postUserMessage(mixed $message): void
    {
        if ($message instanceof MessageBatchInterface) {
            foreach ($message->getMessages() as $msg) {
                $this->postUserMessage($msg);
            }
        }

        if ($message instanceof MessageEnvelope) {
            $env = $message->getMessage();
            if ($env instanceof MessageBatchInterface) {
                foreach ($env->getMessages() as $msg) {
                    $this->postUserMessage($msg);
                }
            }
        }

        foreach ($this->middlewares as $middleware) {
            $middleware->messagePosted($message);
        }
        $this->userMailbox->push($message);
        $this->userMessages->add();
        $this->schedule();
    }

    /**
     * @param mixed $message
     * @return void
     */
    public function postSystemMessage(mixed $message): void
    {
        foreach ($this->middlewares as $middleware) {
            $middleware->messagePosted($message);
        }
        $this->systemMailbox->push($message);
        $this->systemMessages->add();
        $this->schedule();
    }

    public function start(): void
    {
        foreach ($this->middlewares as $middleware) {
            $middleware->mailboxStared();
        }
    }

    public function userMessageCount(): int
    {
        return $this->userMessages->get();
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

    public function run(): void
    {
        try {
            $i = 0;
            $t = $this->dispatcher?->throughput();
            while (true) {
                if ($i > $t) {
                    $i = 0;
                }
                $i++;

                $msg = $this->systemMailbox->pop();

                if (!$msg->valueIsNull()) {
                    $this->systemMessages->sub();
                    $this->handleSystemMessage($msg);
                    continue;
                }

                if ($this->suspended->get() === 1) {
                    return;
                }

                $msg = $this->userMailbox->pop();
                if (!$msg->valueIsNull()) {
                    $this->userMessages->sub();
                    $this->handleUserMessage($msg);
                } else {
                    return;
                }
            }
        } catch (Throwable $e) {
            // escalate failure
            // all exceptions are escalated to the supervisor
            // TODO specify the message that caused the failure
            $this->invoker?->escalateFailure($e, $msg ?? null);
        }
    }

    /**
     * @param mixed $msg
     * @return void
     */
    protected function handleSystemMessage(mixed $msg): void
    {
        match ($msg) {
            ($msg instanceof SuspendMailbox) => $this->suspended->set(1),
            ($msg instanceof ResumeMailbox) => $this->suspended->set(0),
            default => $this->invoker?->invokeSystemMessage($msg),
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
        $this->invoker?->invokeUserMessage($msg);
        foreach ($this->middlewares as $middleware) {
            $middleware->messageReceived($msg);
        }
    }
}
