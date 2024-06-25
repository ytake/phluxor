<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Message;

use Phluxor\ActorSystem\ProtoBuf\Stop;
use Phluxor\ActorSystem\ProtoBuf\Terminated;
use Phluxor\ActorSystem\ProtoBuf\Unwatch;
use Phluxor\ActorSystem\ProtoBuf\Watch;

final class DetectSystemMessage implements DetectMessageInterface
{
    private array $expects = [
        Stop::class,
        Watch::class,
        Unwatch::class,
        Terminated::class,
    ];

    public function __construct(
        private readonly mixed $message
    ) {
    }

    public function isMatch(): bool
    {
        if ($this->message instanceof SystemMessageInterface) {
            return true;
        }
        foreach ($this->expects as $message) {
            if ($this->message instanceof $message) {
                return true;
            }
        }
        return false;
    }
}
