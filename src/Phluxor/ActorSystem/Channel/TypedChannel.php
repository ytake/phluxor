<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Channel;

use Closure;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Ref;
use Swoole\Coroutine\Channel;

class TypedChannel
{
    private Channel $channel;
    private Ref $ref;
    private ActorSystem $actorSystem;

    /**
     * @param ActorSystem $actorSystem
     * @param Closure(mixed): bool $specification
     * @param int $bufferSize
     * <code>
     *     $channel = new TypedChannel(
     *         $actorSystem,
     *         fn(mixed $message): bool => is_string($message)
     *    });
     * </code>
     */
    public function __construct(
        ActorSystem $actorSystem,
        private readonly Closure $specification,
        private readonly int $bufferSize = 1
    ) {
        $this->channel = new Channel($this->bufferSize);
        $this->actorSystem = $actorSystem;
        $this->ref = $this->actorSystem->root()->spawn($this->createProps());
    }

    /**
     * Send a message to the channel
     * @return mixed
     */
    public function result(): mixed
    {
        return $this->channel->pop();
    }

    /**
     * actor reference
     * @return Ref
     */
    public function getRef(): Ref
    {
        return $this->ref;
    }

    /**
     * Close the channel
     * call this method when you want to close the channel
     * @return void
     */
    public function close(): void
    {
        $this->actorSystem->root()->stop($this->ref);
        $this->channel->close();
    }

    /**
     * Check if the message is defined or not
     * @param mixed $msg
     * @return bool
     */
    private function isDefinedMessage(mixed $msg): bool
    {
        foreach (
            [
                new ActorSystem\Message\DetectAutoReceiveMessage($msg),
                new ActorSystem\Message\DetectSystemMessage($msg),
            ] as $expect
        ) {
            if ($expect->isMatch()) {
                return true;
            }
        }
        return false;
    }

    private function createProps(): ActorSystem\Props
    {
        return ActorSystem\Props::fromFunction(
            new ActorSystem\Message\ReceiveFunction(
                function (ActorSystem\Context\ContextInterface $context) {
                    $msg = $context->message();
                    $specification = $this->specification;
                    switch (true) {
                        // is defined message or not
                        case $this->isDefinedMessage($msg):
                            return;
                        case $specification($msg):
                            $this->channel->push($msg);
                            return;
                    }
                }
            )
        );
    }
}
